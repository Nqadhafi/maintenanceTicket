<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingSla extends Model
{
    protected $table = 'settings_sla';

    protected $fillable = ['divisi','urgensi','target_duration_minutes','jam_kerja_json'];

    protected $casts = [
        'target_duration_minutes' => 'integer',
        'jam_kerja_json' => 'array',
    ];

    // Helper ambil SLA menit berdasarkan divisi & urgensi
    public static function minutesFor(string $divisi, string $urgensi): ?int
    {
        $row = static::query()->where([
            ['divisi','=',$divisi],
            ['urgensi','=',$urgensi],
        ])->first();

        return $row->target_duration_minutes;
    }
}
