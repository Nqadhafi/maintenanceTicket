<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{Ticket, User, Asset};
use App\Services\{FonnteService, SlaService};
use App\Support\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketWebController extends Controller
{
    public function index(Request $req)
    {
        /** @var User $user */
        $user = $req->user();

        $q = Ticket::query()
            ->with([
                'pelapor:id,name',
                'assignee:id,name,divisi',
                // Pastikan FK aset ikut di-select agar nested eager load (category/location/vendor) bisa bekerja
                'asset' => function ($aq) {
                    $aq->select('id','kode_aset','nama','asset_category_id','location_id','vendor_id');
                },
                'asset.category:id,nama',
                'asset.location:id,nama',
                'asset.vendor:id,nama',
            ])
            ->when($req->query('status'), fn ($qq, $v) => $qq->where('status', $v))
            ->when($req->query('kategori'), fn ($qq, $v) => $qq->where('kategori', $v))
            ->when($req->query('urgensi'), fn ($qq, $v) => $qq->where('urgensi', $v))
            ->when($req->query('q'), function ($qq, $v) {
                $like = '%' . $v . '%';
                $qq->where(function ($x) use ($like) {
                    $x->where('judul', 'like', $like)->orWhere('deskripsi', 'like', $like);
                });
            });

        if ($user->role === User::ROLE_SUPERADMIN) {
            // full
        } elseif ($user->role === User::ROLE_PJ) {
            $q->where('divisi_pj', $user->divisi);
        } else { // USER
            $q->where('user_id', $user->id);
        }

        $tickets = $q->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('tickets.index', [
            'tickets' => $tickets,
            'filters' => [
                'q'        => $req->query('q'),
                'status'   => $req->query('status'),
                'kategori' => $req->query('kategori'),
                'urgensi'  => $req->query('urgensi'),
            ],
        ]);
    }

    public function create()
    {
        $assets   = Asset::orderBy('kode_aset')->limit(200)->get(['id','kode_aset','nama']);
        $pjs      = User::where('role','PJ')->where('aktif',true)->orderBy('name')->get(['id','name','divisi']);
        $kategori = ['IT','PRODUKSI','GA','LAINNYA'];
        $urgensi  = ['RENDAH','SEDANG','TINGGI','DARURAT'];

        return view('tickets.create', compact('assets','pjs','kategori','urgensi'));
    }

    public function store(Request $req, SlaService $sla, FonnteService $wa)
    {
        /** @var User $user */ $user = $req->user();

        $data = $req->validate([
            'kategori' => 'required|in:IT,PRODUKSI,GA,LAINNYA',
            'urgensi'  => 'required|in:RENDAH,SEDANG,TINGGI,DARURAT',
            'asset_id' => 'nullable|exists:assets,id',
            'is_asset_unlisted' => 'sometimes|boolean',
            'asset_nama_manual' => 'nullable|string|max:120',
            'asset_lokasi_manual' => 'nullable|string|max:120',
            'asset_vendor_manual' => 'nullable|string|max:120',
            'judul' => 'required|string|max:150',
            'deskripsi' => 'required|string',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $isUnlisted = (bool)($data['is_asset_unlisted'] ?? false);

        // Validasi kombinasi aset & kategori
        if ($data['kategori'] !== 'LAINNYA' && !$isUnlisted && empty($data['asset_id'])) {
            return back()->withInput()->withErrors(['asset_id'=>'Pilih aset atau centang "Aset belum terdaftar".']);
        }
        if ($data['kategori'] === 'LAINNYA') {
            if (empty($data['assignee_id'])) {
                return back()->withInput()->withErrors(['assignee_id'=>'Kategori LAINNYA wajib memilih Penanggung Jawab.']);
            }
            if (empty($data['asset_nama_manual']) || empty($data['asset_lokasi_manual'])) {
                return back()->withInput()->withErrors(['asset_nama_manual'=>'Nama & lokasi aset wajib diisi.']);
            }
        }
        if ($isUnlisted && (empty($data['asset_nama_manual']) || empty($data['asset_lokasi_manual']))) {
            return back()->withInput()->withErrors(['asset_nama_manual'=>'Nama & lokasi aset wajib diisi bila aset belum terdaftar.']);
        }

        // Tentukan PJ/divisi
        $assignee = null; $divisiPj = null;
        if (!empty($data['assignee_id'])) {
            $assignee = User::find($data['assignee_id']);
            if (!$assignee || $assignee->role !== User::ROLE_PJ || empty($assignee->divisi)) {
                return back()->withInput()->withErrors(['assignee_id'=>'Penanggung Jawab tidak valid.']);
            }
            $divisiPj = $assignee->divisi;
        } else {
            if ($data['kategori'] === 'LAINNYA') {
                return back()->withInput()->withErrors(['assignee_id'=>'Kategori LAINNYA wajib memilih Penanggung Jawab.']);
            }
            $divisiPj = $data['kategori'];
        }

        // SLA
        $slaDueAt = $sla->dueAt($divisiPj, $data['urgensi']);

        $ticket = Ticket::create([
            'kode_tiket'          => Code::ticket(),
            'user_id'             => $user->id,
            'kategori'            => $data['kategori'],
            'urgensi'             => $data['urgensi'],
            'asset_id'            => $isUnlisted || $data['kategori']==='LAINNYA' ? null : ($data['asset_id'] ?? null),
            'is_asset_unlisted'   => $isUnlisted,
            'asset_nama_manual'   => $isUnlisted || $data['kategori']==='LAINNYA' ? ($data['asset_nama_manual'] ?? null) : null,
            'asset_lokasi_manual' => $isUnlisted || $data['kategori']==='LAINNYA' ? ($data['asset_lokasi_manual'] ?? null) : null,
            'asset_vendor_manual' => $isUnlisted || $data['kategori']==='LAINNYA' ? ($data['asset_vendor_manual'] ?? null) : null,
            'divisi_pj'           => $divisiPj,
            'assignee_id'         => $assignee ? $assignee->id : null,
            'judul'               => $data['judul'],
            'deskripsi'           => $data['deskripsi'],
            'status'              => $assignee ? Ticket::ST_ASSIGNED : Ticket::ST_OPEN,
            'sla_due_at'          => $slaDueAt,
        ]);

        // WA best-effort
        try {
            $url = route('tickets.show', $ticket->id);
            $deadline = $ticket->sla_due_at ? $ticket->sla_due_at->format('d/m/Y H:i') : '-';
            $msg = "[Tiket #{$ticket->kode_tiket}] {$ticket->kategori}/{$ticket->divisi_pj} • {$ticket->urgensi}\n"
                 . "Judul: {$ticket->judul}\nSLA: {$deadline}\n{$url}";
            if (!empty($user->no_wa)) $wa->send($user->no_wa, $msg);
            if ($assignee && !empty($assignee->no_wa)) $wa->send($assignee->no_wa, $msg);
        } catch (\Throwable $e) { report($e); }

        return redirect()->route('tickets.show', $ticket->id)->with('ok','Tiket berhasil dibuat.');
    }

    public function show($id)
    {
        $ticket = Ticket::with([
            'pelapor:id,name',
            'assignee:id,name,divisi',
            // Asset + relasi agar Kategori/Lokasi/Vendor tampil di blade
            'asset' => function ($aq) {
                $aq->select('id','kode_aset','nama','asset_category_id','location_id','vendor_id');
            },
            'asset.category:id,nama',
            'asset.location:id,nama',
            'asset.vendor:id,nama',
            'comments.user:id,name,role,divisi',
            'attachments',
        ])->findOrFail($id);

        $pjs = User::where('role','PJ')->where('aktif',true)
            ->where('divisi', $ticket->divisi_pj)->orderBy('name')->get(['id','name','divisi']);

        return view('tickets.show', compact('ticket','pjs'));
    }

    public function updateStatus(Request $req, $id, FonnteService $wa)
    {
        /** @var User $user */ $user = $req->user();
        $ticket = Ticket::findOrFail($id);

        $req->validate(['status'=>'required|in:OPEN,ASSIGNED,IN_PROGRESS,PENDING,RESOLVED,CLOSED']);

        // akses sederhana
        if ($user->role === User::ROLE_PJ && $ticket->divisi_pj !== $user->divisi) abort(403);
        if ($user->role === User::ROLE_USER && $ticket->user_id !== $user->id) abort(403);

        $ticket->status = $req->input('status');
        if ($ticket->status === Ticket::ST_CLOSED) $ticket->closed_at = now();
        $ticket->save();

        try {
            $url = route('tickets.show', $ticket->id);
            $deadline = $ticket->sla_due_at ? $ticket->sla_due_at->format('d/m/Y H:i') : '-';
            $msg = "[Tiket #{$ticket->kode_tiket}] Status: {$ticket->status}\n"
                 . "Urgensi: {$ticket->urgensi}\nSLA: {$deadline}\n{$url}";
            if ($ticket->pelapor && !empty($ticket->pelapor->no_wa)) $wa->send($ticket->pelapor->no_wa, $msg);
            if ($ticket->assignee && !empty($ticket->assignee->no_wa)) $wa->send($ticket->assignee->no_wa, $msg);
        } catch (\Throwable $e) { report($e); }

        return back()->with('ok','Status tiket diperbarui.');
    }

    public function assign(Request $req, $id)
    {
        /** @var User $user */ $user = $req->user();
        $ticket = Ticket::findOrFail($id);

        if ($user->role === User::ROLE_PJ && $ticket->divisi_pj !== $user->divisi) abort(403);

        $data = $req->validate(['assignee_id'=>'required|exists:users,id']);
        $pj = User::findOrFail($data['assignee_id']);
        if ($pj->role !== User::ROLE_PJ || $pj->divisi !== $ticket->divisi_pj) {
            return back()->withErrors(['assignee_id'=>'PJ harus dari divisi '.$ticket->divisi_pj]);
        }
        $ticket->assignee_id = $pj->id;
        if ($ticket->status === Ticket::ST_OPEN) $ticket->status = Ticket::ST_ASSIGNED;
        $ticket->save();

        return back()->with('ok','Penanggung jawab diperbarui.');
    }

    public function comment(Request $req, $id)
    {
        /** @var User $user */ $user = $req->user();
        $ticket = Ticket::findOrFail($id);

        $data = $req->validate([
            'body' => 'required|string',
            'is_internal' => 'sometimes|boolean'
        ]);
        $isInternal = (bool)($data['is_internal'] ?? false);

        if ($isInternal) {
            if (!($user->role === User::ROLE_SUPERADMIN || ($user->role === User::ROLE_PJ && $user->divisi === $ticket->divisi_pj))) abort(403);
        } else {
            if (!($user->id === $ticket->user_id || ($user->role === User::ROLE_PJ && $user->divisi === $ticket->divisi_pj) || $user->role === User::ROLE_SUPERADMIN)) abort(403);
        }

        $ticket->comments()->create([
            'user_id' => $user->id,
            'body' => $data['body'],
            'is_internal' => $isInternal,
        ]);

        return back()->with('ok','Komentar ditambahkan.');
    }

    public function attach(Request $req, $id)
    {
        /** @var User $user */ $user = $req->user();
        $ticket = Ticket::findOrFail($id);

        if (!($user->id === $ticket->user_id || ($user->role === User::ROLE_PJ && $user->divisi === $ticket->divisi_pj) || $user->role === User::ROLE_SUPERADMIN)) abort(403);

        $req->validate(['file'=>'required|file|max:5120|mimes:jpg,jpeg,png,mp4,pdf,doc,docx,xls,xlsx']);

        $file = $req->file('file');
        $path = $file->store('tickets/'.$ticket->id, 'public');

        $ticket->attachments()->create([
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return back()->with('ok','Lampiran diunggah.');
    }

    public function detach(Request $req, $id, $attId)
    {
        /** @var User $user */ $user = $req->user();
        $ticket = Ticket::findOrFail($id);

        // RBAC: owner, PJ divisi, atau superadmin
        if (!($user->id === $ticket->user_id
            || ($user->role === User::ROLE_PJ && $user->divisi === $ticket->divisi_pj)
            || $user->role === User::ROLE_SUPERADMIN)) {
            abort(403);
        }

        $att = $ticket->attachments()->where('id', $attId)->firstOrFail();

        try {
            if ($att->path && Storage::disk('public')->exists($att->path)) {
                Storage::disk('public')->delete($att->path);
            }
        } catch (\Throwable $e) {
            report($e); // best-effort
        }

        $att->delete();

        return back()->with('ok','Lampiran dihapus.');
    }

public function edit($id)
{
    $ticket   = Ticket::with(['asset'])->findOrFail($id);
    $assets   = Asset::orderBy('kode_aset')->limit(200)->get(['id','kode_aset','nama']);
    $pjs      = User::where('role','PJ')->where('aktif',true)->orderBy('name')->get(['id','name','divisi']);
    $kategori = ['IT','PRODUKSI','GA','LAINNYA'];
    $urgensi  = ['RENDAH','SEDANG','TINGGI','DARURAT'];

    // Akses: USER hanya miliknya; PJ hanya divisinya; SUPERADMIN bebas
    $u = auth()->user();
    if ($u->role === User::ROLE_USER && $ticket->user_id !== $u->id) abort(403);
    if ($u->role === User::ROLE_PJ && $ticket->divisi_pj !== $u->divisi) abort(403);

    return view('tickets.edit', compact('ticket','assets','pjs','kategori','urgensi'));
}

   public function update(Request $req, $id, SlaService $sla)
{
    /** @var User $u */ $u = $req->user();
    $ticket = Ticket::findOrFail($id);

    if ($u->role === User::ROLE_USER && $ticket->user_id !== $u->id) abort(403);
    if ($u->role === User::ROLE_PJ && $ticket->divisi_pj !== $u->divisi) abort(403);

    $data = $req->validate([
        'kategori' => 'required|in:IT,PRODUKSI,GA,LAINNYA',
        'urgensi'  => 'required|in:RENDAH,SEDANG,TINGGI,DARURAT',
        'asset_id' => 'nullable|exists:assets,id',
        'is_asset_unlisted' => 'sometimes|boolean',
        'asset_nama_manual' => 'nullable|string|max:120',
        'asset_lokasi_manual' => 'nullable|string|max:120',
        'asset_vendor_manual' => 'nullable|string|max:120',
        'judul' => 'required|string|max:150',
        'deskripsi' => 'required|string',
        'assignee_id' => 'nullable|exists:users,id',
    ]);

    $isUnlisted = (bool)($data['is_asset_unlisted'] ?? false);

    // Validasi kombinasi aset & kategori (sama seperti store)
    if ($data['kategori'] !== 'LAINNYA' && !$isUnlisted && empty($data['asset_id'])) {
        return back()->withErrors(['asset_id'=>'Pilih aset atau centang "Aset belum terdaftar".'])->withInput();
    }
    if ($data['kategori'] === 'LAINNYA') {
        if (empty($data['assignee_id'])) {
            return back()->withErrors(['assignee_id'=>'Kategori LAINNYA wajib memilih Penanggung Jawab.'])->withInput();
        }
        if (empty($data['asset_nama_manual']) || empty($data['asset_lokasi_manual'])) {
            return back()->withErrors(['asset_nama_manual'=>'Nama & lokasi aset wajib diisi.'])->withInput();
        }
    }
    if ($isUnlisted && (empty($data['asset_nama_manual']) || empty($data['asset_lokasi_manual']))) {
        return back()->withErrors(['asset_nama_manual'=>'Nama & lokasi aset wajib diisi bila aset belum terdaftar.'])->withInput();
    }

    // Tentukan PJ/divisi (boleh kosong kecuali LAINNYA)
    $assignee = null; $divisiPj = $ticket->divisi_pj;
    if (!empty($data['assignee_id'])) {
        $assignee = User::find($data['assignee_id']);
        if (!$assignee || $assignee->role !== User::ROLE_PJ || empty($assignee->divisi)) {
            return back()->withErrors(['assignee_id'=>'Penanggung Jawab tidak valid.'])->withInput();
        }
        $divisiPj = $assignee->divisi;
    } else {
        if ($data['kategori'] === 'LAINNYA') {
            return back()->withErrors(['assignee_id'=>'Kategori LAINNYA wajib memilih Penanggung Jawab.'])->withInput();
        }
        // default divisi = kategori
        $divisiPj = $data['kategori'];
    }

    // Recalc SLA jika kategori/divisi/urgensi berubah (MVP: sederhana)
    $slaDueAt = $sla->dueAt($divisiPj, $data['urgensi']);

    $ticket->update([
        'kategori'            => $data['kategori'],
        'urgensi'             => $data['urgensi'],
        'asset_id'            => $isUnlisted || $data['kategori']==='LAINNYA' ? null : ($data['asset_id'] ?? null),
        'is_asset_unlisted'   => $isUnlisted,
        'asset_nama_manual'   => $isUnlisted || $data['kategori']==='LAINNYA' ? ($data['asset_nama_manual'] ?? null) : null,
        'asset_lokasi_manual' => $isUnlisted || $data['kategori']==='LAINNYA' ? ($data['asset_lokasi_manual'] ?? null) : null,
        'asset_vendor_manual' => $isUnlisted || $data['kategori']==='LAINNYA' ? ($data['asset_vendor_manual'] ?? null) : null,
        'divisi_pj'           => $divisiPj,
        'assignee_id'         => $assignee ? $assignee->id : $ticket->assignee_id, // biarkan yang lama jika tidak diubah
        'judul'               => $data['judul'],
        'deskripsi'           => $data['deskripsi'],
        'sla_due_at'          => $slaDueAt,
    ]);

    return redirect()->route('tickets.show', $ticket->id)->with('ok','Tiket diperbarui.');
}
}
