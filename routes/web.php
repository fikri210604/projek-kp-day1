<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\KendaraanMasterController;
use App\Http\Controllers\KeperluanController;
use App\Models\KendaraanMaster;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Guest landing: halaman form keperluan (masuk/pinjam)
Route::get('/', function () {
    $masters = KendaraanMaster::where('aktif', true)->orderBy('plat_nomor')->get();
    return view('form', ['masters' => $masters]);
})->name('guest.form');

// Admin area (dashboard & profile dipindah ke /admin)
Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function () {
    // Halaman admin kustom
    Route::view('/', 'admin.dashboard')->name('dashboard');
    Route::view('/profile', 'admin.profile')->name('profile');
});

// Guest endpoints untuk pendataan masuk/keluar tanpa login
Route::post('guest/masuk', [KendaraanController::class, 'store'])->name('guest.kendaraan.masuk');
Route::post('guest/keluar', [KendaraanController::class, 'keluarByPlat'])->name('guest.kendaraan.keluar');

// Guest endpoints untuk form keperluan (keluar/masuk)
Route::post('guest/keperluan/keluar', [KeperluanController::class, 'store'])->name('guest.keperluan.keluar');
Route::post('guest/keperluan/masuk', [KeperluanController::class, 'kembaliByPlat'])->name('guest.keperluan.masuk');

// Admin endpoints: CRUD master + monitoring + export (diprefiks /admin)
Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function () {
    Route::resource('master-kendaraan', KendaraanMasterController::class)->only(['index','store','update','destroy']);

    Route::resource('kendaraan', KendaraanController::class)->only(['index','update','destroy']);
    Route::post('kendaraan/{kendaraan}/keluar', [KendaraanController::class, 'keluar'])->name('kendaraan.keluar');
    Route::get('kendaraan-export', [KendaraanController::class, 'export'])->name('kendaraan.export');

    Route::resource('keperluan', KeperluanController::class)->only(['index','update','destroy','store']);
    Route::post('keperluan/{keperluan}/masuk', [KeperluanController::class, 'kembali'])->name('keperluan.masuk');
    Route::get('keperluan-export', [KeperluanController::class, 'export'])->name('keperluan.export');
});

require __DIR__.'/auth.php';
