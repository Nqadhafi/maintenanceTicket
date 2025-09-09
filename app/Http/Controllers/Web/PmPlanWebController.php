<?php
// app/Http/Controllers/Web/PmPlanWebController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{PmPlan, AssetCategory, User};
use Illuminate\Http\Request;

class PmPlanWebController extends Controller
{
    public function index(Request $req)
    {
        $q = PmPlan::query()
            ->with(['category:id,nama','defaultAssignee:id,name,divisi'])
            ->when($req->query('q'), function ($qq, $v) {
                $like = '%'.$v.'%';
                $qq->where('nama_plan','like',$like);
            })
            ->when($req->query('category_id'), fn($qq,$v)=>$qq->where('asset_category_id',$v))
            ->when($req->boolean('aktif', null) !== null, fn($qq,$v)=>$qq->where('aktif',(bool)$v))
            ->orderBy('nama_plan');

        return view('pm.plans.index', [
            'rows' => $q->paginate(20)->withQueryString(),
            'filters' => [
                'q' => $req->query('q'),
                'category_id' => $req->query('category_id'),
                'aktif' => $req->query('aktif'),
            ],
            'categories' => AssetCategory::orderBy('nama')->get(['id','nama']),
        ]);
    }

    public function create()
    {
        return view('pm.plans.create', [
            'categories' => AssetCategory::orderBy('nama')->get(['id','nama']),
            'pjs'        => User::where('role','PJ')->where('aktif',true)->orderBy('name')->get(['id','name','divisi']),
        ]);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'nama_plan'         => 'required|string|max:150',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'interval_type'     => 'required|in:DAY,WEEK,MONTH,METER',
            'interval_value'    => 'required|integer|min:1',
            'checklist'         => 'required|array|min:1',
            'checklist.*'       => 'required|string|max:180',
            'default_assignee_id' => 'nullable|exists:users,id',
            'aktif'             => 'sometimes|boolean',
        ]);
        $data['aktif'] = (bool)($data['aktif'] ?? true);
        PmPlan::create($data);
        return redirect()->route('pm.plans.index')->with('ok','Rencana PM dibuat.');
    }

    public function edit($id)
    {
        return view('pm.plans.edit', [
            'plan'       => PmPlan::findOrFail($id),
            'categories' => AssetCategory::orderBy('nama')->get(['id','nama']),
            'pjs'        => User::where('role','PJ')->where('aktif',true)->orderBy('name')->get(['id','name','divisi']),
        ]);
    }

    public function update(Request $req, $id)
    {
        $plan = PmPlan::findOrFail($id);
        $data = $req->validate([
            'nama_plan'         => 'required|string|max:150',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'interval_type'     => 'required|in:DAY,WEEK,MONTH,METER',
            'interval_value'    => 'required|integer|min:1',
            'checklist'         => 'required|array|min:1',
            'checklist.*'       => 'required|string|max:180',
            'default_assignee_id' => 'nullable|exists:users,id',
            'aktif'             => 'sometimes|boolean',
        ]);
        $plan->update($data + ['aktif'=>(bool)($data['aktif'] ?? $plan->aktif)]);
        return redirect()->route('pm.plans.index')->with('ok','Rencana PM diperbarui.');
    }

    public function destroy($id)
    {
        PmPlan::findOrFail($id)->delete();
        return back()->with('ok','Rencana PM dihapus.');
    }
}
