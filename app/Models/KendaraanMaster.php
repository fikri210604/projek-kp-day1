<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KendaraanMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'plat_nomor',
        'jenis',
        'nama',
        'unit',
        'aktif',
    ];

    public function transaksi()
    {
        return $this->hasMany(Kendaraan::class, 'kendaraan_master_id');
    }
}

