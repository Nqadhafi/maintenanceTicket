<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class PmPlan extends Model
{
    protected $fillable = [
        'nama_plan','asset_category_id','interval_type','interval_value',
        'checklist','default_assignee_id','aktif'
    ];

    protected $casts = [
        'checklist' => 'array',
        'aktif' => 'boolean',
    ];

    public const INT_DAY = 'DAY';
    public const INT_WEEK = 'WEEK';
    public const INT_MONTH = 'MONTH';
    public const INT_METER = 'METER';

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function defaultAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'default_assignee_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(PmSchedule::class);
    }
}
