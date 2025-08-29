<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Ticket extends Model
{
    protected $fillable = [
        'kode_tiket',
        'user_id',
        'kategori',
        'urgensi',
        'asset_id',
        'is_asset_unlisted',
        'asset_nama_manual',
        'asset_lokasi_manual',
        'asset_vendor_manual',
        'divisi_pj',
        'assignee_id',
        'judul',
        'deskripsi',
        'status',
        'sla_due_at',
        'closed_at'
    ];

    protected $casts = [
        'is_asset_unlisted' => 'boolean',
        'sla_due_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Kategori & Urgensi
    public const KAT_IT = 'IT';
    public const KAT_PROD = 'PRODUKSI';
    public const KAT_GA = 'GA';
    public const KAT_LAIN = 'LAINNYA';

    public const URG_RENDAH = 'RENDAH';
    public const URG_SEDANG = 'SEDANG';
    public const URG_TINGGI = 'TINGGI';
    public const URG_DARURAT = 'DARURAT';

    // Status
    public const ST_OPEN = 'OPEN';
    public const ST_ASSIGNED = 'ASSIGNED';
    public const ST_INPROGRESS = 'IN_PROGRESS';
    public const ST_PENDING = 'PENDING';
    public const ST_RESOLVED = 'RESOLVED';
    public const ST_CLOSED = 'CLOSED';

    public function getStatusLabelAttribute(): string
    {
        $map = [
            'OPEN'         => 'Baru',
            'ASSIGNED'     => 'Ditugaskan',
            'IN_PROGRESS'  => 'Sedang dikerjakan',
            'PENDING'      => 'Menunggu',
            'RESOLVED'     => 'Selesai',
            'CLOSED'       => 'Ditutup',
        ];
        return $map[$this->status] ?? $this->status;
    }

    public function getUrgensiLabelAttribute(): string
    {
        $map = [
            'RENDAH'  => 'Rendah',
            'SEDANG'  => 'Sedang',
            'TINGGI'  => 'Tinggi',
            'DARURAT' => 'Darurat',
        ];
        return $map[$this->urgensi] ?? $this->urgensi;
    }


    // Relasi
    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }
    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    // WO Corrective (opsional)
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'ticket_id');
    }


    // Scopes umum
    public function scopeMine(Builder $q, User $user): Builder
    {
        return $q->where('user_id', $user->id);
    }

    public function scopeForAssigneeDivision(Builder $q, ?string $divisi): Builder
    {
        return $divisi ? $q->where('divisi_pj', $divisi) : $q;
    }

    public function scopeStatus(Builder $q, ?string $status): Builder
    {
        return $status ? $q->where('status', $status) : $q;
    }

    public function scopeUrgensi(Builder $q, ?string $urgensi): Builder
    {
        return $urgensi ? $q->where('urgensi', $urgensi) : $q;
    }

    public function scopeKategori(Builder $q, ?string $kategori): Builder
    {
        return $kategori ? $q->where('kategori', $kategori) : $q;
    }

    // Helper SLA
    public function isOverSla(): bool
    {
        return $this->sla_due_at instanceof Carbon
            && now()->greaterThan($this->sla_due_at)
            && !in_array($this->status, [self::ST_RESOLVED, self::ST_CLOSED], true);
    }
}
