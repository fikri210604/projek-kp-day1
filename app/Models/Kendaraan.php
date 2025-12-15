<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plat_nomor',
        'jenis',
        'waktu_masuk',
        'waktu_keluar',
        'durasi_menit',
        'biaya',
        'status',
        'catatan',
    ];

    protected $casts = [
        'waktu_masuk' => 'datetime',
        'waktu_keluar' => 'datetime',
    ];

    public function master()
    {
        return $this->belongsTo(KendaraanMaster::class, 'kendaraan_master_id');
    }
}
