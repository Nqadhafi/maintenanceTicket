<?php

namespace App\Services;

use App\Models\SettingSla;
use Carbon\Carbon;

class SlaService
{
    // Versi simpel: tambah menit kalender. (Nanti bisa diupgrade ke jam kerja)
    public function dueAt(string $divisi, string $urgensi): ?Carbon
    {
        $minutes = SettingSla::minutesFor($divisi, $urgensi);
        return $minutes ? now()->copy()->addMinutes($minutes) : null;
    }
}
