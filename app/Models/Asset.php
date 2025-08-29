<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Asset extends Model
{
    protected $fillable = [
        'kode_aset','nama','asset_category_id','location_id','vendor_id',
        'spesifikasi','status','tanggal_beli','lampiran_cover'
    ];

    protected $casts = [
        'spesifikasi' => 'array',
        'tanggal_beli' => 'date',
    ];

    public const STATUS_AKTIF = 'AKTIF';
    public const STATUS_RUSAK = 'RUSAK';
    public const STATUS_SCRAP = 'SCRAP';

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}
