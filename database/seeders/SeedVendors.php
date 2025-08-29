<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendor;

class SeedVendors extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['nama' => 'PT Sumber Jaya', 'kontak' => 'Pak Budi', 'no_wa' => '08xxxxxxxxxx', 'alamat' => 'Solo'],
            ['nama' => 'CV Maju Teknik', 'kontak' => 'Mbak Rina', 'no_wa' => '08xxxxxxxxxx', 'alamat' => 'Kartasura'],
        ];
        foreach ($rows as $r) {
            Vendor::updateOrCreate(['nama' => $r['nama']], [
                'kontak' => $r['kontak'], 'no_wa' => $r['no_wa'], 'alamat' => $r['alamat']
            ]);
        }
    }
}
