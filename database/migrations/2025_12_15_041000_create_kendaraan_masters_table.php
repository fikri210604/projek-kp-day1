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
        Schema::create('kendaraan_masters', function (Blueprint $table) {
            $table->id();
            $table->string('plat_nomor')->unique();
            $table->enum('jenis', ['mobil', 'motor'])->nullable();
            $table->string('nama')->nullable();
            $table->string('unit')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kendaraan_masters');
    }
};

