<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class SeedLocations extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['nama' => 'Front Office', 'detail' => 'Lantai 1'],
            ['nama' => 'Produksi A',   'detail' => 'Area mesin besar'],
            ['nama' => 'Produksi B',   'detail' => 'Finishing & QC'],
            ['nama' => 'Gudang',       'detail' => 'Bahan & sparepart'],
            ['nama' => 'Ruang IT',     'detail' => 'Server, jaringan'],
        ];
        foreach ($rows as $r) {
            Location::updateOrCreate(['nama' => $r['nama']], ['detail' => $r['detail']]);
        }
    }
}
