<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keperluan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'isi_keperluan',
        'kendaraan_master_id',
        'plat_nomor',
        'tujuan_keperluan',
        'waktu_pinjam',
        'waktu_kembali',
    ];

    protected $casts = [
        'waktu_pinjam' => 'datetime',
        'waktu_kembali' => 'datetime',
    ];

    public function kendaraan()
    {
        return $this->belongsTo(\App\Models\KendaraanMaster::class, 'kendaraan_master_id');
    }
}
