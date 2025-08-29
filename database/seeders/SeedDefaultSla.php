<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SettingSla;

class SeedDefaultSla extends Seeder
{
    public function run(): void
    {
        $rows = [
            // IT
            ['divisi'=>'IT','urgensi'=>'RENDAH','target_duration_minutes'=>2880],
            ['divisi'=>'IT','urgensi'=>'SEDANG','target_duration_minutes'=>1440],
            ['divisi'=>'IT','urgensi'=>'TINGGI','target_duration_minutes'=>480],
            ['divisi'=>'IT','urgensi'=>'DARURAT','target_duration_minutes'=>240],
            // PRODUKSI
            ['divisi'=>'PRODUKSI','urgensi'=>'RENDAH','target_duration_minutes'=>2880],
            ['divisi'=>'PRODUKSI','urgensi'=>'SEDANG','target_duration_minutes'=>1440],
            ['divisi'=>'PRODUKSI','urgensi'=>'TINGGI','target_duration_minutes'=>240],
            ['divisi'=>'PRODUKSI','urgensi'=>'DARURAT','target_duration_minutes'=>120],
            // GA
            ['divisi'=>'GA','urgensi'=>'RENDAH','target_duration_minutes'=>4320],
            ['divisi'=>'GA','urgensi'=>'SEDANG','target_duration_minutes'=>2880],
            ['divisi'=>'GA','urgensi'=>'TINGGI','target_duration_minutes'=>1440],
            ['divisi'=>'GA','urgensi'=>'DARURAT','target_duration_minutes'=>480],
        ];

        foreach ($rows as $r) {
            SettingSla::updateOrCreate(
                ['divisi' => $r['divisi'], 'urgensi' => $r['urgensi']],
                ['target_duration_minutes' => $r['target_duration_minutes']]
            );
        }
    }
}
