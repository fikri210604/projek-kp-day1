<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('keperluans', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('nama'); // nama peminjam/pengguna
            $table->string('isi_keperluan'); // deskripsi singkat
            $table->foreignId('kendaraan_master_id')->nullable()->constrained('kendaraan_masters')->nullOnDelete();
            $table->string('plat_nomor')->nullable(); // fallback jika tidak ada master
            $table->string('tujuan_keperluan');
            $table->dateTime('waktu_pinjam');
            $table->dateTime('waktu_kembali')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keperluans');
    }
};
