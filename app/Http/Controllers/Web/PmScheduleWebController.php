<?php
// app/Http/Controllers/Web/PmScheduleWebController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{PmSchedule, PmPlan, Asset};
use Illuminate\Http\Request;

class PmScheduleWebController extends Controller
{
    public function index(Request $req)
    {
        $q = PmSchedule::query()
            ->with(['plan:id,nama_plan,interval_type,interval_value','asset:id,kode_aset,nama'])
            ->when($req->query('plan_id'), fn($qq,$v)=>$qq->where('pm_plan_id',$v))
            ->when($req->query('asset_q'), function ($qq, $v) {
                $like = '%'.$v.'%';
                $qq->whereHas('asset', fn($a)=>$a->where('kode_aset','like',$like)->orWhere('nama','like',$like));
            })
            ->when($req->boolean('aktif', null) !== null, fn($qq,$v)=>$qq->where('aktif',(bool)$v))
            ->when($req->query('due_in_days'), function ($qq, $v) {
                $qq->whereBetween('next_due_at', [now(), now()->addDays((int)$v)]);
            })
            ->orderBy('next_due_at');

        return view('pm.schedules.index', [
            'rows'  => $q->paginate(20)->withQueryString(),
            'plans' => PmPlan::orderBy('nama_plan')->get(['id','nama_plan']),
            'filters' => [
                'plan_id' => $req->query('plan_id'),
                'asset_q' => $req->query('asset_q'),
                'aktif'   => $req->query('aktif'),
                'due_in_days' => $req->query('due_in_days'),
            ],
        ]);
    }

    public function create()
    {
        return view('pm.schedules.create', [
            'plans'  => PmPlan::orderBy('nama_plan')->get(['id','nama_plan']),
            'assets' => Asset::orderBy('kode_aset')->limit(300)->get(['id','kode_aset','nama']),
        ]);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'pm_plan_id'     => 'required|exists:pm_plans,id',
            'asset_id'       => 'required|exists:assets,id',
            'next_due_at'    => 'required|date',
            'meter_threshold'=> 'nullable|integer|min:1',
            'aktif'          => 'sometimes|boolean',
        ]);
        $data['aktif'] = (bool)($data['aktif'] ?? true);

        // unik per plan+asset (opsional, tapi bagus)
        $exists = PmSchedule::where('pm_plan_id',$data['pm_plan_id'])->where('asset_id',$data['asset_id'])->exists();
        if ($exists) return back()->withInput()->withErrors(['asset_id'=>'Asset ini sudah punya jadwal untuk rencana tersebut.']);

        PmSchedule::create($data);
        return redirect()->route('pm.schedules.index')->with('ok','Jadwal PM dibuat.');
    }

    public function edit($id)
    {
        return view('pm.schedules.edit', [
            'schedule' => PmSchedule::with(['plan','asset'])->findOrFail($id),
            'plans'    => PmPlan::orderBy('nama_plan')->get(['id','nama_plan']),
            'assets'   => Asset::orderBy('kode_aset')->limit(300)->get(['id','kode_aset','nama']),
        ]);
    }

    public function update(Request $req, $id)
    {
        $sch = PmSchedule::findOrFail($id);
        $data = $req->validate([
            'pm_plan_id'     => 'required|exists:pm_plans,id',
            'asset_id'       => 'required|exists:assets,id',
            'next_due_at'    => 'required|date',
            'meter_threshold'=> 'nullable|integer|min:1',
            'aktif'          => 'sometimes|boolean',
        ]);
        $sch->update($data + ['aktif'=>(bool)($data['aktif'] ?? $sch->aktif)]);
        return redirect()->route('pm.schedules.index')->with('ok','Jadwal PM diperbarui.');
    }

    public function destroy($id)
    {
        PmSchedule::findOrFail($id)->delete();
        return back()->with('ok','Jadwal PM dihapus.');
    }
}
