<?php
// app/Http/Controllers/Web/WorkOrderWebController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{WorkOrder, WorkOrderItem, Ticket, Asset, User};
use Illuminate\Http\Request;

class WorkOrderWebController extends Controller
{
    public function index(Request $req)
    {
        $q = WorkOrder::query()
            ->with(['asset:id,kode_aset,nama','assignee:id,name,divisi','ticket:id,kode_tiket,judul'])
            ->when($req->query('type'), fn($qq,$v)=>$qq->where('type',$v))
            ->when($req->query('status'), fn($qq,$v)=>$qq->where('status',$v))
            ->when($req->query('q'), function ($qq, $v) {
                $like = '%'.$v.'%';
                $qq->where(function($w) use($like){
                    $w->where('kode_wo','like',$like)
                      ->orWhere('ringkasan_pekerjaan','like',$like)
                      ->orWhereHas('ticket', fn($t)=>$t->where('kode_tiket','like',$like)->orWhere('judul','like',$like))
                      ->orWhereHas('asset', fn($a)=>$a->where('kode_aset','like',$like)->orWhere('nama','like',$like));
                });
            })
            ->orderByDesc('created_at');

        return view('wo.index', [
            'rows' => $q->paginate(20)->withQueryString(),
            'filters' => [
                'q' => $req->query('q'),
                'type' => $req->query('type'),
                'status' => $req->query('status'),
            ],
        ]);
    }

    public function create()
    {
        return view('wo.create', [
            'assets' => Asset::orderBy('kode_aset')->limit(300)->get(['id','kode_aset','nama']),
            'pjs'    => User::where('role','PJ')->where('aktif',true)->orderBy('name')->get(['id','name','divisi']),
            'tickets'=> Ticket::orderByDesc('created_at')->limit(300)->get(['id','kode_tiket','judul']),
        ]);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'type'       => 'required|in:CORRECTIVE,PREVENTIVE',
            'ticket_id'  => 'nullable|exists:tickets,id',
            'asset_id'   => 'required|exists:assets,id',
            'assignee_id'=> 'nullable|exists:users,id',
            'ringkasan_pekerjaan' => 'required|string|max:200',
        ]);

        $wo = WorkOrder::create([
            'kode_wo'   => 'WO-'.now()->format('ymdHis'),
            'type'      => $data['type'],
            'ticket_id' => $data['ticket_id'] ?? null,
            'asset_id'  => $data['asset_id'],
            'assignee_id' => $data['assignee_id'] ?? null,
            'status'    => WorkOrder::ST_OPEN,
            'ringkasan_pekerjaan' => $data['ringkasan_pekerjaan'],
        ]);

        return redirect()->route('wo.show', $wo->id)->with('ok','WO dibuat.');
    }

    public function show($id)
    {
        $wo = WorkOrder::with(['asset','assignee','ticket','items'])->findOrFail($id);
        return view('wo.show', compact('wo'));
    }

    public function edit($id)
    {
        $wo = WorkOrder::findOrFail($id);
        return view('wo.edit', [
            'wo'     => $wo,
            'assets' => Asset::orderBy('kode_aset')->limit(300)->get(['id','kode_aset','nama']),
            'pjs'    => User::where('role','PJ')->where('aktif',true)->orderBy('name')->get(['id','name','divisi']),
            'tickets'=> Ticket::orderByDesc('created_at')->limit(300)->get(['id','kode_tiket','judul']),
        ]);
    }

    public function update(Request $req, $id)
    {
        $wo = WorkOrder::findOrFail($id);
        $data = $req->validate([
            'type'       => 'required|in:CORRECTIVE,PREVENTIVE',
            'ticket_id'  => 'nullable|exists:tickets,id',
            'asset_id'   => 'required|exists:assets,id',
            'assignee_id'=> 'nullable|exists:users,id',
            'status'     => 'required|in:OPEN,IN_PROGRESS,DONE',
            'ringkasan_pekerjaan' => 'required|string|max:200',
            'started_at' => 'nullable|date',
            'finished_at'=> 'nullable|date|after_or_equal:started_at',
            'duration_minutes' => 'nullable|integer|min:0',
        ]);
        $wo->update($data);
        return redirect()->route('wo.show', $wo->id)->with('ok','WO diperbarui.');
    }

    public function destroy($id)
    {
        WorkOrder::findOrFail($id)->delete();
        return redirect()->route('wo.index')->with('ok','WO dihapus.');
    }

    // ---- Items ----
    public function addItem(Request $req, $id)
    {
        $wo = WorkOrder::findOrFail($id);
        $data = $req->validate([
            'item_name' => 'required|string|max:150',
            'qty'       => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
        ]);
        $data['total_cost'] = (float)$data['qty'] * (float)$data['unit_cost'];
        $it = $wo->items()->create($data);

        $sum = $wo->items()->sum('total_cost');
        $wo->update(['cost_total'=>$sum]);

        return back()->with('ok','Item ditambahkan.');
    }

    public function removeItem($id, $itemId)
    {
        $wo = WorkOrder::findOrFail($id);
        $item = $wo->items()->where('id',$itemId)->firstOrFail();
        $item->delete();

        $sum = $wo->items()->sum('total_cost');
        $wo->update(['cost_total'=>$sum]);

        return back()->with('ok','Item dihapus.');
    }

    // Status helper
    public function start($id)
    {
        $wo = WorkOrder::findOrFail($id);
        $wo->update(['status'=>WorkOrder::ST_INPROGRESS,'started_at'=>now()]);
        return back()->with('ok','WO dimulai.');
    }
    public function done($id)
    {
        $wo = WorkOrder::findOrFail($id);
        $dur = $wo->started_at ? now()->diffInMinutes($wo->started_at) : null;
        $wo->update(['status'=>WorkOrder::ST_DONE,'finished_at'=>now(),'duration_minutes'=>$dur]);
        return back()->with('ok','WO selesai.');
    }
}
