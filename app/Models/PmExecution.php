<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmExecution extends Model
{
    protected $fillable = [
        'pm_schedule_id','work_order_id','performed_at','checklist_result','notes'
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'checklist_result' => 'array',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PmSchedule::class, 'pm_schedule_id');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }
}
