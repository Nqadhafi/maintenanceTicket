<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{Ticket, User};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardWebController extends Controller
{
    private array $status = ['OPEN','ASSIGNED','IN_PROGRESS','PENDING','RESOLVED','CLOSED'];
    private array $kategori = ['IT','PRODUKSI','GA','LAINNYA'];
    private array $urgensi = ['RENDAH','SEDANG','TINGGI','DARURAT'];

public function index(Request $req)
{
    /** @var User $user */
    $user = $req->user();

    // Status aktif (belum selesai)
    $active = ['OPEN','ASSIGNED','IN_PROGRESS','PENDING'];

    // Base scope RBAC untuk "recent"
    $base = Ticket::query();
    if ($user->role === User::ROLE_SUPERADMIN) {
        // full
    } elseif ($user->role === User::ROLE_PJ) {
        $base->where('divisi_pj', $user->divisi);
    } else {
        $base->where('user_id', $user->id);
    }

    // Default recent (akan diubah per role di bawah)
    $recent = (clone $base)
        ->with(['pelapor:id,name','assignee:id,name'])
        ->orderByDesc('created_at')
        ->limit(8)
        ->get([
            'id','kode_tiket','judul','status','urgensi','kategori','divisi_pj',
            'created_at','sla_due_at','assignee_id','user_id'
        ]);

    // Kartu-kartu per role
    $cards = [];
    $quick = []; // quick actions

    if ($user->role === User::ROLE_USER) {
        // Tiket milik saya
        $mine = Ticket::where('user_id', $user->id);

        $myOpen     = (clone $mine)->whereIn('status',$active)->count();
        $myOverdue  = (clone $mine)->whereIn('status',$active)->whereNotNull('sla_due_at')->where('sla_due_at','<', now())->count();
        $myToday    = (clone $mine)->whereDate('created_at', now()->toDateString())->count();
        $myDone7d   = (clone $mine)
                        ->whereIn('status',['RESOLVED','CLOSED'])
                        ->where('updated_at','>=', now()->subDays(7))
                        ->count();

 $cards = [
        ['title'=>'Tiket Aktif Saya','value'=>$myOpen,'hint'=>'Tiket yang masih berjalan','tone'=>'info'],
        ['title'=>'Perlu Perhatian','value'=>$myOverdue,'hint'=>'Lewat deadline','tone'=>'danger'],
        ['title'=>'Dibuat Hari Ini','value'=>$myToday,'hint'=>'Tiket yang baru kamu buat','tone'=>'warn'],
        ['title'=>'Selesai (7 hari)','value'=>$myDone7d,'hint'=>'Tiketmu yang beres minggu ini','tone'=>'ok'],
    ];

        $quick = [
            ['label'=>'Buat Tiket','route'=>route('tickets.create'),'style'=>'btn-brand'],
            ['label'=>'Lihat Semua Tiket','route'=>route('tickets.index'),'style'=>'btn-outline'],
        ];
    }
    elseif ($user->role === User::ROLE_PJ) {
        // Fokus ke tugas saya & antrian divisi
        $mine = Ticket::where('assignee_id', $user->id)->whereIn('status',$active);
        $divQ = Ticket::where('divisi_pj', $user->divisi)->whereIn('status',$active);

        $assignedToMe   = (clone $mine)->count();
        $dueToday       = (clone $mine)->whereDate('sla_due_at', now()->toDateString())->count();
        $overdueMine    = (clone $mine)->whereNotNull('sla_due_at')->where('sla_due_at','<', now())->count();
        $unassignedDiv  = (clone $divQ)->whereNull('assignee_id')->count();

    $cards = [
        ['title'=>'Tugas Saya','value'=>$assignedToMe,'hint'=>'Tiket aktif yang ditugaskan ke kamu','tone'=>'info'],
        ['title'=>'Jatuh Tempo Hari Ini','value'=>$dueToday,'hint'=>'Segera ditangani','tone'=>'warn'],
        ['title'=>'Lewat Deadline','value'=>$overdueMine,'hint'=>'Butuh prioritas','tone'=>'danger'],
        ['title'=>'Belum Ada PJ (Divisi)','value'=>$unassignedDiv,'hint'=>'Perlu diambil','tone'=>'warn'],
    ];

        // recent: prioritas divisi (yang belum selesai), terbaru 8
        $recent = Ticket::with(['pelapor:id,name','assignee:id,name'])
            ->where('divisi_pj',$user->divisi)
            ->orderByDesc('created_at')
            ->limit(8)->get([
                'id','kode_tiket','judul','status','urgensi','kategori','divisi_pj',
                'created_at','sla_due_at','assignee_id','user_id'
            ]);

        $quick = [
            ['label'=>'Ambil Tiket (Belum Ada PJ)','route'=>route('reports.tickets',['divisi_pj'=>$user->divisi,'assignee_id'=>'']),'style'=>'btn-outline'],
            ['label'=>'Lihat Semua Tiket Divisi','route'=>route('reports.tickets',['divisi_pj'=>$user->divisi]),'style'=>'btn-outline'],
        ];
    }
    else { // SUPERADMIN
        $openAll     = Ticket::whereIn('status',$active)->count();
        $overdueAll  = Ticket::whereIn('status',$active)->whereNotNull('sla_due_at')->where('sla_due_at','<', now())->count();
        $todayNew    = Ticket::whereDate('created_at', now()->toDateString())->count();
        $unassigned  = Ticket::whereIn('status',$active)->whereNull('assignee_id')->count();

    $cards = [
        ['title'=>'Tiket Aktif (All)','value'=>$openAll,'hint'=>'Semua divisi','tone'=>'info'],
        ['title'=>'Lewat Deadline','value'=>$overdueAll,'hint'=>'Perlu diprioritaskan','tone'=>'danger'],
        ['title'=>'Masuk Hari Ini','value'=>$todayNew,'hint'=>'Tiket baru','tone'=>'warn'],
        ['title'=>'Belum Ada PJ','value'=>$unassigned,'hint'=>'Perlu penugasan','tone'=>'warn'],
    ];

        $quick = [
            ['label'=>'Kelola Users','route'=>route('admin.users.index'),'style'=>'btn-outline'],
            ['label'=>'Atur Deadline','route'=>route('settings.sla.index'),'style'=>'btn-outline'],
            ['label'=>'Laporan','route'=>route('reports.tickets'),'style'=>'btn-outline'],
        ];
    }

    return view('dashboard.index', compact('cards','recent','quick'));
}

}
