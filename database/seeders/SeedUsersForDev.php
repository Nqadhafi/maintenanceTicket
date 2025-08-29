<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SeedUsersForDev extends Seeder
{
    public function run(): void
    {
        // Superadmin
        User::updateOrCreate(
            ['email' => 'admin@swg.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'role' => 'SUPERADMIN',
                'aktif' => true,
                'no_wa' => '08xxxxxxxxxx',
            ]
        );

        // PJ IT
        User::updateOrCreate(
            ['email' => 'pj.it@swg.local'],
            [
                'name' => 'PJ IT',
                'password' => Hash::make('pjit123'),
                'role' => 'PJ',
                'divisi' => 'IT',
                'aktif' => true,
                'no_wa' => '08xxxxxxxxxx',
            ]
        );

        // PJ Produksi
        User::updateOrCreate(
            ['email' => 'pj.prod@swg.local'],
            [
                'name' => 'PJ Produksi',
                'password' => Hash::make('pjprod123'),
                'role' => 'PJ',
                'divisi' => 'PRODUKSI',
                'aktif' => true,
                'no_wa' => '08xxxxxxxxxx',
            ]
        );

        // PJ GA
        User::updateOrCreate(
            ['email' => 'pj.ga@swg.local'],
            [
                'name' => 'PJ GA',
                'password' => Hash::make('pjga123'),
                'role' => 'PJ',
                'divisi' => 'GA',
                'aktif' => true,
                'no_wa' => '08xxxxxxxxxx',
            ]
        );
    }
}
