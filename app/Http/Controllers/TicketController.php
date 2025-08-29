<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketStoreRequest;
use App\Models\{Ticket, User, Asset, SettingSla};
use App\Services\{FonnteService, SlaService};
use App\Support\Code;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    // GET /tickets?status=&kategori=&urgensi=&q=&scope=mine|divisi
    public function index(Request $req): JsonResponse
    {
        /** @var User|null $user */
        $user = $req->user();
        if (!$user instanceof User) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $q = Ticket::query()
            ->with(['pelapor:id,name,divisi,role', 'assignee:id,name,divisi,role', 'asset:id,kode_aset,nama'])
            ->when($req->query('status'), function ($qq, $v) { $qq->where('status', $v); })
            ->when($req->query('kategori'), function ($qq, $v) { $qq->where('kategori', $v); })
            ->when($req->query('urgensi'), function ($qq, $v) { $qq->where('urgensi', $v); })
            ->when($req->query('q'), function ($qq, $v) {
                $like = '%' . $v . '%';
                $qq->where(function ($x) use ($like) {
                    $x->where('judul', 'like', $like)->orWhere('deskripsi', 'like', $like);
                });
            });

        $scope = $req->query('scope');
        if ($user->role === User::ROLE_SUPERADMIN) {
            if ($req->query('divisi')) {
                $q->where('divisi_pj', $req->query('divisi'));
            }
        } elseif ($user->role === User::ROLE_PJ) {
            $q->where('divisi_pj', $user->divisi);
            if ($scope === 'mine') {
                $q->where('assignee_id', $user->id);
            }
        } else {
            $q->where('user_id', $user->id);
        }

        $tickets = $q->orderBy('created_at', 'desc')->paginate(20);

        return response()->json(['data' => $tickets]);
    }

    // POST /tickets
    public function store(TicketStoreRequest $req, SlaService $sla, FonnteService $wa): JsonResponse
    {
        /** @var User|null $user */
        $user = $req->user();
        if (!$user instanceof User) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $data = $req->validated();

        $divisiPj = null;

        // ---- Ambil PJ jika ada
        /** @var User|null $assignee */
        $assignee = null;
        $assigneeId = isset($data['assignee_id']) ? (int)$data['assignee_id'] : null;

        if ($assigneeId) {
            $assignee = User::find($assigneeId);
            if (!$assignee instanceof User) {
                return response()->json(['error' => 'Penanggung Jawab tidak ditemukan.'], 422);
            }
            if ($assignee->role !== User::ROLE_PJ || empty($assignee->divisi)) {
                return response()->json(['error' => 'Penanggung Jawab tidak valid.'], 422);
            }
            $divisiPj = $assignee->divisi; // IT / PRODUKSI / GA
        } else {
            if ($data['kategori'] === 'LAINNYA') {
                return response()->json(['error' => 'Kategori LAINNYA wajib memilih Penanggung Jawab.'], 422);
            }
            $divisiPj = $data['kategori'];
        }

        // ---- Validasi asset jika diisi
        $assetId = isset($data['asset_id']) ? (int)$data['asset_id'] : null;
        if ($assetId) {
            $assetExists = Asset::query()->whereKey($assetId)->exists();
            if (!$assetExists) {
                return response()->json(['error' => 'Aset tidak ditemukan.'], 422);
            }
        }

        // ---- Hitung SLA
        $slaDueAt = $sla->dueAt($divisiPj, $data['urgensi']);

        // ---- Simpan tiket
        /** @var Ticket $ticket */
        $ticket = null;
        DB::transaction(function () use (&$ticket, $user, $data, $divisiPj, $assignee, $slaDueAt) {
            $ticket = Ticket::create([
                'kode_tiket'          => Code::ticket(),
                'user_id'             => $user->id,
                'kategori'            => $data['kategori'],
                'urgensi'             => $data['urgensi'],
                'asset_id'            => isset($data['asset_id']) ? (int)$data['asset_id'] : null,
                'is_asset_unlisted'   => isset($data['is_asset_unlisted']) ? (bool)$data['is_asset_unlisted'] : false,
                'asset_nama_manual'   => isset($data['asset_nama_manual']) ? $data['asset_nama_manual'] : null,
                'asset_lokasi_manual' => isset($data['asset_lokasi_manual']) ? $data['asset_lokasi_manual'] : null,
                'asset_vendor_manual' => isset($data['asset_vendor_manual']) ? $data['asset_vendor_manual'] : null,
                'divisi_pj'           => $divisiPj,
                'assignee_id'         => $assignee ? $assignee->id : null,
                'judul'               => $data['judul'],
                'deskripsi'           => $data['deskripsi'],
                'status'              => $assignee ? Ticket::ST_ASSIGNED : Ticket::ST_OPEN,
                'sla_due_at'          => $slaDueAt,
            ]);
        });

        // ---- Notifikasi WA (best effort)
        try {
            $url = url('/tickets/' . $ticket->id);
            $deadline = $ticket->sla_due_at ? $ticket->sla_due_at->format('d/m/Y H:i') : '-';

            $msg = "[Tiket #{$ticket->kode_tiket}] {$ticket->kategori}/{$ticket->divisi_pj} • {$ticket->urgensi}\n"
                 . "Judul: {$ticket->judul}\n"
                 . "SLA: {$deadline}\n{$url}";

            if (!empty($user->no_wa)) {
                $wa->send($user->no_wa, $msg);
            }
            if ($assignee && !empty($assignee->no_wa)) {
                $wa->send($assignee->no_wa, $msg);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Tiket berhasil dibuat.',
            'data'    => $ticket->load('pelapor','assignee','asset'),
        ], 201);
    }

    // GET /tickets/{id}
    public function show($id): JsonResponse
    {
        /** @var Ticket|null $ticket */
        $ticket = Ticket::with([
            'pelapor:id,name,divisi,role',
            'assignee:id,name,divisi,role',
            'asset:id,kode_aset,nama,asset_category_id',
            'comments.user:id,name,role,divisi',
            'attachments',
        ])->find($id);

        if (!$ticket instanceof Ticket) {
            return response()->json(['error' => 'Tiket tidak ditemukan.'], 404);
        }

        return response()->json(['data' => $ticket]);
    }

    // PUT /tickets/{id}
    public function update(Request $req, $id, FonnteService $wa): JsonResponse
    {
        /** @var User|null $user */
        $user = $req->user();
        if (!$user instanceof User) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        /** @var Ticket|null $ticket */
        $ticket = Ticket::query()->find($id);
        if (!$ticket instanceof Ticket) {
            return response()->json(['error' => 'Tiket tidak ditemukan.'], 404);
        }

        if ($user->role === User::ROLE_PJ && $ticket->divisi_pj !== $user->divisi) {
            return response()->json(['error' => 'Tidak berwenang.'], 403);
        }
        if ($user->role === User::ROLE_USER && (int)$ticket->user_id !== (int)$user->id) {
            return response()->json(['error' => 'Tidak berwenang.'], 403);
        }

        $data = $req->validate([
            'status'      => 'nullable|in:OPEN,ASSIGNED,IN_PROGRESS,PENDING,RESOLVED,CLOSED',
            'assignee_id' => 'nullable|exists:users,id',
            'judul'       => 'nullable|string|max:150',
            'deskripsi'   => 'nullable|string',
            'urgensi'     => 'nullable|in:RENDAH,SEDANG,TINGGI,DARURAT',
        ]);

        $changed = false;
        $assigneeChanged = false;

        /** @var User|null $assignee */
        $assignee = null;

        DB::transaction(function () use (&$ticket, $data, &$changed, &$assigneeChanged, &$assignee) {
            if (isset($data['assignee_id'])) {
                $assignee = User::find((int)$data['assignee_id']);
                if (!$assignee instanceof User || $assignee->role !== User::ROLE_PJ || empty($assignee->divisi)) {
                    abort(422, 'Penanggung Jawab tidak valid.');
                }
                if ($ticket->divisi_pj !== $assignee->divisi) {
                    abort(422, 'Divisi Penanggung Jawab tidak sesuai dengan divisi tiket.');
                }
                $ticket->assignee_id = $assignee->id;
                if ($ticket->status === Ticket::ST_OPEN) {
                    $ticket->status = Ticket::ST_ASSIGNED;
                }
                $changed = true;
                $assigneeChanged = true;
            }

            if (isset($data['status'])) {
                $ticket->status = $data['status'];
                if ($data['status'] === Ticket::ST_CLOSED) {
                    $ticket->closed_at = now();
                }
                $changed = true;
            }

            if (isset($data['judul'])) {
                $ticket->judul = $data['judul']; $changed = true;
            }
            if (isset($data['deskripsi'])) {
                $ticket->deskripsi = $data['deskripsi']; $changed = true;
            }
            if (isset($data['urgensi'])) {
                $ticket->urgensi = $data['urgensi']; $changed = true;
                $mins = SettingSla::minutesFor($ticket->divisi_pj, $ticket->urgensi);
                if ($mins) {
                    $ticket->sla_due_at = now()->addMinutes($mins);
                }
            }

            if ($changed) $ticket->save();
        });

        if ($changed) {
            try {
                $url = url('/tickets/' . $ticket->id);
                $deadline = $ticket->sla_due_at ? $ticket->sla_due_at->format('d/m/Y H:i') : '-';
                $msg = "[Tiket #{$ticket->kode_tiket}] Status: {$ticket->status}\n"
                     . "Urgensi: {$ticket->urgensi}\n"
                     . "SLA: {$deadline}\n{$url}";

                $pelapor = $ticket->pelapor;
                if ($pelapor && !empty($pelapor->no_wa)) {
                    $wa->send($pelapor->no_wa, $msg);
                }
                $pj = $ticket->assignee;
                if ($pj && !empty($pj->no_wa) && ($assigneeChanged || $ticket->status !== Ticket::ST_OPEN)) {
                    $wa->send($pj->no_wa, $msg);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // gunakan refresh() (bukan fresh()) supaya pasti object
        $ticket->refresh();

        return response()->json([
            'message' => 'Tiket diperbarui.',
            'data'    => $ticket->load('pelapor','assignee','asset'),
        ]);
    }
}
