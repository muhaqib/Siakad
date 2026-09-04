<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SkripsiController;

Route::middleware(['auth', 'role:admin', 'fakultas.scope'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/skripsi', [SkripsiController::class, 'index'])->name('skripsi.index');
    Route::get('/skripsi/{skripsi}', [SkripsiController::class, 'show'])->name('skripsi.show');
    Route::post('/skripsi/{skripsi}/assign-pembimbing', [SkripsiController::class, 'assignPembimbing'])->name('skripsi.assign-pembimbing');
    Route::put('/skripsi/{skripsi}/status', [SkripsiController::class, 'updateStatus'])->name('skripsi.update-status');
    Route::put('/skripsi/{skripsi}/nilai', [SkripsiController::class, 'updateNilai'])->name('skripsi.update-nilai');
});
