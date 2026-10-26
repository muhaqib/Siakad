<?php

use App\Http\Controllers\Dosen\PresensiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::get('/presensi/kelas/{kelas}', [PresensiController::class, 'showKelas'])->name('presensi.kelas');
    Route::get('/presensi/kelas/{kelas}/pertemuan/create', [PresensiController::class, 'createPertemuan'])->name('presensi.pertemuan.create');
    Route::post('/presensi/kelas/{kelas}/pertemuan', [PresensiController::class, 'storePertemuan'])->name('presensi.pertemuan.store');
    Route::get('/presensi/pertemuan/{pertemuan}/input', [PresensiController::class, 'inputPresensi'])->name('presensi.input');
    Route::delete('/presensi/pertemuan/{pertemuan}', [PresensiController::class, 'destroyPertemuan'])->name('presensi.pertemuan.destroy');
    Route::post('/presensi/pertemuan/{pertemuan}', [PresensiController::class, 'storePresensi'])->name('presensi.store');
});
