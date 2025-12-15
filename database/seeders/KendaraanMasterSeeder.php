<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KendaraanMaster;

class KendaraanMasterSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['plat_nomor' => 'B 1234 ABC', 'jenis' => 'mobil', 'nama' => 'Avanza Dinas', 'unit' => 'Umum', 'aktif' => true],
            ['plat_nomor' => 'B 5678 XYZ', 'jenis' => 'mobil', 'nama' => 'Innova Dinas', 'unit' => 'Operasional', 'aktif' => true],
            ['plat_nomor' => 'B 1111 MTR', 'jenis' => 'motor', 'nama' => 'Beat Dinas', 'unit' => 'Operasional', 'aktif' => true],
        ];

        foreach ($data as $row) {
            KendaraanMaster::updateOrCreate(
                ['plat_nomor' => $row['plat_nomor']],
                $row
            );
        }
    }
}

