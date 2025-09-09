<?php
// app/Http/Controllers/Web/PmExecutionWebController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{PmExecution, PmSchedule, WorkOrder, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PmExecutionWebController extends Controller
{
    public function create($scheduleId)
    {
        $schedule = PmSchedule::with(['plan','asset'])->findOrFail($scheduleId);
        return view('pm.executions.create', compact('schedule'));
    }

public function store(Request $req, $scheduleId)
{
    $schedule = \App\Models\PmSchedule::with(['plan','asset'])->findOrFail($scheduleId);

    $data = $req->validate([
        'performed_at'         => 'required|date',
        'checklist_result'     => 'required|array|min:1',
        'checklist_result.*'   => 'required|string|max:200',
        'notes'                => 'nullable|string',
        'make_wo'              => 'sometimes|boolean',
        'wo_ringkasan'         => 'nullable|string|max:200',
    ]);

    $makeWO = $req->boolean('make_wo'); // aman untuk PHP 7.4+; jika 7.3 kebawah, pakai: (bool)($data['make_wo'] ?? false)
    $woId   = null;

    DB::transaction(function () use ($schedule, $data, $makeWO, &$woId) {
        // 1) Buat WO preventif jika diminta
        if ($makeWO) {
            $wo = \App\Models\WorkOrder::create([
                'kode_wo'   => 'WO-'.now()->format('ymdHis'),
                'type'      => \App\Models\WorkOrder::TYPE_PREV,
                'ticket_id' => null,
                'asset_id'  => $schedule->asset_id,
                'assignee_id' => $schedule->plan->default_assignee_id,
                'status'    => \App\Models\WorkOrder::ST_OPEN,
                'ringkasan_pekerjaan' => $data['wo_ringkasan'] ?: ('PM '.$schedule->plan->nama_plan),
            ]);
            $woId = $wo->id;
        }

        // 2) Simpan eksekusi PM (work_order_id bisa null bila tidak buat WO)
        \App\Models\PmExecution::create([
            'pm_schedule_id'   => $schedule->id,
            'work_order_id'    => $woId, // pastikan kolom ini nullable di DB
            'performed_at'     => $data['performed_at'],
            'checklist_result' => $data['checklist_result'],
            'notes'            => $data['notes'] ?? null,
        ]);

        // 3) Auto-advance next_due_at untuk interval kalender
        $type = $schedule->plan->interval_type;
        if (in_array($type, ['DAY','WEEK','MONTH'], true) && $schedule->next_due_at) {
            $next = \Carbon\Carbon::parse($schedule->next_due_at);
            $iv   = (int) $schedule->plan->interval_value;

            if ($type === 'DAY') {
                $next = $next->addDays($iv);
            } elseif ($type === 'WEEK') {
                $next = $next->addWeeks($iv);
            } elseif ($type === 'MONTH') {
                $next = $next->addMonths($iv);
            }

            $schedule->update(['next_due_at' => $next]);
        }
    });

    return redirect()->route('pm.schedules.index')->with('ok','Eksekusi PM dicatat.');
}


}
