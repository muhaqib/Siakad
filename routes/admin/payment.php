<?php

use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentDashboardController;
use App\Http\Controllers\Admin\PaymentTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin', 'fakultas.scope'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard & Reports
    Route::get('/payments/dashboard', [PaymentDashboardController::class, 'index'])->name('payments.dashboard');
    Route::get('/payments/export', [PaymentController::class, 'export'])->name('payments.export');
    Route::post('/payments/bulk-generate', [PaymentController::class, 'bulkGenerate'])->name('payments.bulk-generate');

    // Payments CRUD & Actions
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/student/{mahasiswa}', [PaymentController::class, 'studentPayments'])->name('payments.student');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::post('/payments/{payment}/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');
    Route::get('/payments/{payment}/history', [PaymentController::class, 'history'])->name('payments.history');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // Payment Types (Superadmin only)
    Route::get('/payment-types', [PaymentTypeController::class, 'index'])->name('payment-types.index');
    Route::get('/payment-types/{paymentType}/edit', [PaymentTypeController::class, 'edit'])->name('payment-types.edit');
    Route::put('/payment-types/{paymentType}', [PaymentTypeController::class, 'update'])->name('payment-types.update');
});
