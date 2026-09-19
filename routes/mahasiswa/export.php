<?php

use App\Http\Controllers\Mahasiswa\ExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/export/transkrip', [ExportController::class, 'transkrip'])->name('export.transkrip');
    Route::get('/export/khs/{tahunAkademik}', [ExportController::class, 'khs'])->name('export.khs');
    Route::get('/export/jadwal', [ExportController::class, 'jadwal'])->name('export.jadwal');
});
