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
        if ($from) $q->where('created_at', '>=', Carbon::parse($from)->startOfDay());
        if ($to)   $q->where('created_at', '<=', Carbon::parse($to)->endOfDay());

        // RBAC scope
        if ($user->role === User::ROLE_SUPERADMIN) {
            // full
        } elseif ($user->role === User::ROLE_PJ) {
            $q->where('divisi_pj', $user->divisi);
        } else { // USER
            $q->where('user_id', $user->id);
        }

        // ===== Ringkasan (berdasarkan filter saat ini) =====
        $base = clone $q;

        $summary = [
            'total'   => (clone $base)->count(),
            'status'  => collect($this->status)->mapWithKeys(
                fn($s) => [$s => (clone $base)->where('status', $s)->count()]
            )->all(),
            'overdue' => (clone $base)
                ->whereNotIn('status', ['RESOLVED','CLOSED'])
                ->whereNotNull('sla_due_at')
                ->where('sla_due_at', '<', now())
                ->count(),
            'due_today' => (clone $base)
                ->whereNotIn('status', ['RESOLVED','CLOSED'])
                ->whereNotNull('sla_due_at')
                ->whereBetween('sla_due_at', [now()->startOfDay(), now()->endOfDay()])
                ->count(),
        ];

        $tickets = $q->orderByDesc('created_at')->paginate(25)->withQueryString();

        // Dropdown assignee (opsional)
        $pjList = User::where('role','PJ')->where('aktif',true)
            ->orderBy('name')->get(['id','name','divisi']);

        // ===== Chart data (mengikuti filter & RBAC yang sama) =====
$chartBase = clone $base;

// 1) Tren 14 hari terakhir (kalau user tidak set range)
$rangeFrom = $from ? Carbon::parse($from)->startOfDay() : now()->subDays(13)->startOfDay();
$rangeTo   = $to   ? Carbon::parse($to)->endOfDay()   : now()->endOfDay();

$trendRows = (clone $chartBase)
    ->whereBetween('created_at', [$rangeFrom, $rangeTo])
    ->selectRaw('DATE(created_at) d, COUNT(*) c')
    ->groupBy('d')
    ->orderBy('d')
    ->pluck('c','d')
    ->all();

// normalize ke rentang tanggal supaya tidak ada “lubang”
$labels = [];
$series = [];
for ($d = $rangeFrom->copy(); $d <= $rangeTo; $d->addDay()) {
    $key = $d->toDateString();
    $labels[] = $d->format('d M');
    $series[] = $trendRows[$key] ?? 0;
}

// 2) Distribusi urgensi (untuk donut/legend kecil)
$urgensiCounts = (clone $chartBase)
    ->selectRaw('urgensi, COUNT(*) c')
    ->groupBy('urgensi')
    ->pluck('c','urgensi')
    ->all();

$chart = [
    'trend' => ['labels' => $labels, 'series' => $series],
    'status' => array_map(fn($s) => $summary['status'][$s] ?? 0, $this->status),
    'statusLabels' => array_map(fn($s) => ucfirst(strtolower(str_replace('_',' ',$s))), $this->status),
    'urgensi' => $urgensiCounts,
];

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
            'summary'  => $summary,
            'chart' => $chart,
'range' => ['from' => $rangeFrom, 'to' => $rangeTo],
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

        $fileName = 'laporan_tiket_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($q) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 agar nyaman dibuka Excel
            fwrite($out, "\xEF\xBB\xBF");
            // Header
            fputcsv($out, [
                'Kode', 'Dibuat', 'Kategori', 'Divisi PJ', 'Prioritas', 'Status',
                'Judul', 'Pelapor', 'PJ', 'Deadline', 'Waktu Ditutup'
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
