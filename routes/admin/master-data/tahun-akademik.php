<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TahunAkademikController;

Route::middleware(['auth', 'role:admin', 'fakultas.scope'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/tahun-akademik', [TahunAkademikController::class, 'index'])->name('tahun-akademik.index');
    Route::post('/tahun-akademik', [TahunAkademikController::class, 'store'])->name('tahun-akademik.store');
    Route::put('/tahun-akademik/{tahunAkademik}', [TahunAkademikController::class, 'update'])->name('tahun-akademik.update');
    Route::delete('/tahun-akademik/{tahunAkademik}', [TahunAkademikController::class, 'destroy'])->name('tahun-akademik.destroy');
    Route::get('/tahun-akademik/active', [TahunAkademikController::class, 'getActive'])->name('tahun-akademik.active');
    Route::post('/tahun-akademik/{tahunAkademik}/activate', [TahunAkademikController::class, 'activate'])->name('tahun-akademik.activate');
});
