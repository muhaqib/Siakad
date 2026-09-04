<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\KpController;

Route::middleware(['auth', 'role:admin', 'fakultas.scope'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/kp', [KpController::class, 'index'])->name('kp.index');
    Route::get('/kp/{kp}', [KpController::class, 'show'])->name('kp.show');
    Route::post('/kp/{kp}/assign-pembimbing', [KpController::class, 'assignPembimbing'])->name('kp.assign-pembimbing');
    Route::put('/kp/{kp}/status', [KpController::class, 'updateStatus'])->name('kp.update-status');
    Route::put('/kp/{kp}/nilai', [KpController::class, 'updateNilai'])->name('kp.update-nilai');
});
