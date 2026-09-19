<?php

use App\Http\Controllers\Mahasiswa\MidtransController;
use App\Http\Controllers\Mahasiswa\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // --- Midtrans Snap ---
    // Generate Snap Token (dipanggil via fetch/AJAX saat klik tombol bayar)
    Route::post('/payments/{payment}/midtrans/token', [MidtransController::class, 'generateToken'])
        ->name('payments.midtrans.token');
    // Halaman hasil setelah Snap popup ditutup (redirect dari Midtrans)
    Route::get('/payments/{payment}/midtrans/finish', [MidtransController::class, 'finish'])
        ->name('payments.midtrans.finish');
});
