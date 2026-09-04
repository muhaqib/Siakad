<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\KehadiranDosenController;

Route::middleware(['auth', 'role:admin', 'fakultas.scope'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/kehadiran-dosen', [KehadiranDosenController::class, 'index'])->name('kehadiran-dosen.index');
    Route::get('/kehadiran-dosen/{dosen}', [KehadiranDosenController::class, 'show'])->name('kehadiran-dosen.show');
});
