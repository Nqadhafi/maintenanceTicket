<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{Ticket, User};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportWebController extends Controller
{
    private array $kategori = ['IT','PRODUKSI','GA','LAINNYA'];
    private array $urgensi  = ['RENDAH','SEDANG','TINGGI','DARURAT'];
    private array $status   = ['OPEN','ASSIGNED','IN_PROGRESS','PENDING','RESOLVED','CLOSED'];
    private array $divisi   = ['IT','PRODUKSI','GA'];

    public function tickets(Request $req)
    {
        /** @var User $user */
        $user = $req->user();

        $q = Ticket::query()
            ->with(['pelapor:id,name', 'assignee:id,name,divisi'])
            ->when($req->query('q'), function ($qq, $v) {
                $like = '%'.$v.'%';
                $qq->where(function($x) use($like){
                    $x->where('kode_tiket','like',$like)
                      ->orWhere('judul','like',$like)
                      ->orWhere('deskripsi','like',$like);
                });
            })
            ->when($req->query('kategori'), fn($qq,$v)=>$qq->where('kategori',$v))
            ->when($req->query('urgensi'), fn($qq,$v)=>$qq->where('urgensi',$v))
            ->when($req->query('status'), fn($qq,$v)=>$qq->where('status',$v))
            ->when($req->query('divisi_pj'), fn($qq,$v)=>$qq->where('divisi_pj',$v))
            ->when($req->query('assignee_id'), fn($qq,$v)=>$qq->where('assignee_id',$v));

        // Tanggal (created_at)
        $from = $req->query('date_from');
        $to   = $req->query('date_to');
        if ($from) {
            $q->where('created_at', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            $q->where('created_at', '<=', Carbon::parse($to)->endOfDay());
        }

        // RBAC scope
        if ($user->role === User::ROLE_SUPERADMIN) {
            // full
        } elseif ($user->role === User::ROLE_PJ) {
            $q->where('divisi_pj', $user->divisi);
        } else { // USER
            $q->where('user_id', $user->id);
        }

        $tickets = $q->orderByDesc('created_at')->paginate(25)->withQueryString();

        // Dropdown assignee (opsional)
        $pjList = User::where('role','PJ')->where('aktif',true)
            ->orderBy('name')->get(['id','name','divisi']);

        return view('reports.tickets', [
            'tickets' => $tickets,
            'filters' => [
                'q' => $req->query('q'),
                'kategori' => $req->query('kategori'),
                'urgensi' => $req->query('urgensi'),
                'status' => $req->query('status'),
                'divisi_pj' => $req->query('divisi_pj'),
                'assignee_id' => $req->query('assignee_id'),
                'date_from' => $from,
                'date_to' => $to,
            ],
            'kategori' => $this->kategori,
            'urgensi'  => $this->urgensi,
            'status'   => $this->status,
            'divisi'   => $this->divisi,
            'pjList'   => $pjList,
        ]);
    }

    public function exportTickets(Request $req): StreamedResponse
    {
        /** @var User $user */
        $user = $req->user();

        $q = Ticket::query()
            ->with(['pelapor:id,name', 'assignee:id,name,divisi'])
            ->when($req->query('q'), function ($qq, $v) {
                $like = '%'.$v.'%';
                $qq->where(function($x) use($like){
                    $x->where('kode_tiket','like',$like)
                      ->orWhere('judul','like',$like)
                      ->orWhere('deskripsi','like',$like);
                });
            })
            ->when($req->query('kategori'), fn($qq,$v)=>$qq->where('kategori',$v))
            ->when($req->query('urgensi'), fn($qq,$v)=>$qq->where('urgensi',$v))
            ->when($req->query('status'), fn($qq,$v)=>$qq->where('status',$v))
            ->when($req->query('divisi_pj'), fn($qq,$v)=>$qq->where('divisi_pj',$v))
            ->when($req->query('assignee_id'), fn($qq,$v)=>$qq->where('assignee_id',$v));

        $from = $req->query('date_from');
        $to   = $req->query('date_to');
        if ($from) $q->where('created_at','>=', Carbon::parse($from)->startOfDay());
        if ($to)   $q->where('created_at','<=', Carbon::parse($to)->endOfDay());

        // RBAC scope
        if ($user->role === User::ROLE_SUPERADMIN) {
            // full
        } elseif ($user->role === User::ROLE_PJ) {
            $q->where('divisi_pj', $user->divisi);
        } else {
            $q->where('user_id', $user->id);
        }

        $fileName = 'tickets_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($q) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 agar nyaman dibuka Excel
            fwrite($out, "\xEF\xBB\xBF");
            // Header
            fputcsv($out, [
                'Kode', 'Dibuat', 'Kategori', 'Divisi PJ', 'Urgensi', 'Status',
                'Judul', 'Pelapor', 'PJ', 'SLA Due', 'Closed At'
            ]);

            $q->orderBy('created_at')->chunk(500, function($rows) use ($out) {
                foreach ($rows as $t) {
                    fputcsv($out, [
                        $t->kode_tiket,
                        optional($t->created_at)->format('d/m/Y H:i'),
                        $t->kategori,
                        $t->divisi_pj,
                        $t->urgensi,
                        $t->status,
                        $t->judul,
                        optional($t->pelapor)->name,
                        optional($t->assignee)->name,
                        optional($t->sla_due_at)->format('d/m/Y H:i'),
                        optional($t->closed_at)->format('d/m/Y H:i'),
                    ]);
                }
            });

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
