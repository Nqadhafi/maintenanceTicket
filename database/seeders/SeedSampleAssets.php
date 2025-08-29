<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Asset, AssetCategory, Location, Vendor};

class SeedSampleAssets extends Seeder
{
    public function run(): void
    {
        // Helper ambil id
        $catId = function (string $nama): ?int {
            $row = AssetCategory::where('nama', $nama)->first();
            return $row ? $row->id : null;
        };
        $locId = function (string $nama): ?int {
            $row = Location::where('nama', $nama)->first();
            return $row ? $row->id : null;
        };
        $venId = function (?string $nama): ?int {
            if (!$nama) return null;
            $row = Vendor::where('nama', $nama)->first();
            return $row ? $row->id : null;
        };

        // IT
        Asset::updateOrCreate(
            ['kode_aset' => 'IT-2025-001'],
            [
                'nama' => 'PC Kasir 1',
                'asset_category_id' => $catId('IT'),
                'location_id' => $locId('Front Office'),
                'vendor_id' => $venId('PT Sumber Jaya'),
                'spesifikasi' => [
                    'tipe' => 'PC', 'cpu' => 'i5-9400F', 'ram_gb' => 16,
                    'os' => 'Windows 10 Pro', 'serial_number' => 'XYZ123', 'ip' => '192.168.1.20'
                ],
                'status' => 'AKTIF',
            ]
        );

        // PRODUKSI (Mesin)
        Asset::updateOrCreate(
            ['kode_aset' => 'MESIN-001'],
            [
                'nama' => 'Printer Large Format VF2-640',
                'asset_category_id' => $catId('PRODUKSI'),
                'location_id' => $locId('Produksi A'),
                'vendor_id' => $venId('CV Maju Teknik'),
                'spesifikasi' => [
                    'tipe' => 'Printer Large Format', 'merk' => 'Roland', 'model' => 'VF2-640',
                    'meter_counter' => 125430, 'tinta' => ['C','M','Y','K','Lc','Lm'], 'daya_watt' => 1500
                ],
                'status' => 'AKTIF',
            ]
        );

        // GA (AC)
        Asset::updateOrCreate(
            ['kode_aset' => 'GA-AC-01'],
            [
                'nama' => 'AC Ruang Produksi A',
                'asset_category_id' => $catId('GA'),
                'location_id' => $locId('Produksi A'),
                'vendor_id' => $venId(null),
                'spesifikasi' => [
                    'tipe' => 'AC', 'merk' => 'Daikin', 'btu' => 18000, 'freon' => 'R32',
                    'lokasi_detail' => 'Ruang Produksi A'
                ],
                'status' => 'AKTIF',
            ]
        );
    }
}
