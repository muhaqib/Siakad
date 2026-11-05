<?php

use App\Http\Controllers\Admin\LaporanAbsensiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/laporan-absensi', [LaporanAbsensiController::class, 'index'])->name('laporan-absensi.index');
});
