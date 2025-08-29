<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Builder;

class WorkOrder extends Model
{
    protected $fillable = [
        'kode_wo','type','ticket_id','asset_id','assignee_id','status',
        'started_at','finished_at','duration_minutes','cost_total','ringkasan_pekerjaan'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_minutes' => 'integer',
        'cost_total' => 'decimal:2',
    ];

    public const TYPE_CORR = 'CORRECTIVE';
    public const TYPE_PREV = 'PREVENTIVE';

    public const ST_OPEN = 'OPEN';
    public const ST_INPROGRESS = 'IN_PROGRESS';
    public const ST_DONE = 'DONE';

    public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class, 'ticket_id'); }
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class, 'asset_id'); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assignee_id'); }
    public function items(): HasMany { return $this->hasMany(WorkOrderItem::class); }

    public function scopeJenis(Builder $q, ?string $type): Builder
    {
        return $type ? $q->where('type', $type) : $q;
    }
}
