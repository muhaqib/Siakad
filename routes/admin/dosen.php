<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DosenController;

Route::middleware(['auth', 'role:admin', 'fakultas.scope'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dosen/export', [DosenController::class, 'export'])->name('dosen.export');
    Route::get('/dosen', [DosenController::class, 'index'])->name('dosen.index');
    Route::post('/dosen', [DosenController::class, 'store'])->name('dosen.store');
    Route::get('/dosen/{dosen}', [DosenController::class, 'show'])->name('dosen.show');
    Route::put('/dosen/{dosen}', [DosenController::class, 'update'])->name('dosen.update');
    Route::delete('/dosen/{dosen}', [DosenController::class, 'destroy'])->name('dosen.destroy');
});
