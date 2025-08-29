<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name','email','password','no_wa','role','divisi','aktif'
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'aktif' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    // ===== Konstanta Role & Divisi =====
    public const ROLE_USER = 'USER';
    public const ROLE_PJ = 'PJ';
    public const ROLE_SUPERADMIN = 'SUPERADMIN';

    public const DIV_IT = 'IT';
    public const DIV_PRODUKSI = 'PRODUKSI';
    public const DIV_GA = 'GA';

    // ===== Relasi =====
    public function tiketDibuat(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    public function tiketDitugaskan(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assignee_id');
    }

    public function workOrdersDitugaskan(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'assignee_id');
    }

    // ===== Helper Akses =====
    public function isSuperadmin(): bool { return $this->role === self::ROLE_SUPERADMIN; }
    public function isPJ(): bool { return $this->role === self::ROLE_PJ; }
    public function isUser(): bool { return $this->role === self::ROLE_USER; }
}
