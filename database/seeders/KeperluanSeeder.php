<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Keperluan;
use App\Models\KendaraanMaster;

class KeperluanSeeder extends Seeder
{
    public function run(): void
    {
        $masters = KendaraanMaster::get();
        if ($masters->isEmpty()) {
            return;
        }

        $rows = [
            [
                'nama' => 'Budi',
                'isi_keperluan' => 'Kunjungan kerja',
                'tujuan_keperluan' => 'Kantor Cabang',
                'kendaraan_master_id' => $masters[0]->id,
                'waktu_pinjam' => now()->subHours(5),
                'waktu_kembali' => now()->subHours(1),
            ],
            [
                'nama' => 'Sari',
                'isi_keperluan' => 'Ambil dokumen',
                'tujuan_keperluan' => 'Gudang',
                'kendaraan_master_id' => $masters[1]->id,
                'waktu_pinjam' => now()->subHour(),
                'waktu_kembali' => null,
            ],
        ];

        foreach ($rows as $r) {
            Keperluan::create($r);
        }
    }
}

