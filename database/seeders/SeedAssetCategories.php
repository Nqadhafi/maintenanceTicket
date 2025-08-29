<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssetCategory;

class SeedAssetCategories extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['nama' => 'IT',       'deskripsi' => 'Perangkat IT & jaringan'],
            ['nama' => 'PRODUKSI', 'deskripsi' => 'Mesin produksi & periferal'],
            ['nama' => 'GA',       'deskripsi' => 'Fasilitas umum: AC, listrik, furniture'],
        ];

        foreach ($rows as $r) {
            AssetCategory::updateOrCreate(['nama' => $r['nama']], ['deskripsi' => $r['deskripsi']]);
        }
    }
}
