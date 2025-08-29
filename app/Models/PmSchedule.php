<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Builder;

class PmSchedule extends Model
{
    protected $fillable = ['pm_plan_id','asset_id','next_due_at','meter_threshold','aktif'];

    protected $casts = [
        'next_due_at' => 'datetime',
        'meter_threshold' => 'integer',
        'aktif' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PmPlan::class, 'pm_plan_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(PmExecution::class, 'pm_schedule_id');
    }

    public function scopeDueSoon(Builder $q, int $days = 3): Builder
    {
        return $q->where('aktif', true)->whereBetween('next_due_at', [now(), now()->addDays($days)]);
    }
}
