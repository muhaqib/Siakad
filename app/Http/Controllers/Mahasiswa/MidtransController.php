<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\StudentPayment;
use App\Services\MidtransService;
use App\Services\PaymentAccessService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class MidtransController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService,
        protected PaymentAccessService $paymentAccessService,
    ) {}

    /**
     * Generate Snap Token untuk sebuah tagihan pembayaran.
     *
     * Endpoint: POST /mahasiswa/payments/{payment}/midtrans/token
     * Dipanggil via JavaScript fetch() saat mahasiswa klik tombol "Bayar via Midtrans".
     */
    public function generateToken(StudentPayment $payment): JsonResponse
    {
        $mahasiswa = Auth::user()->mahasiswa;

        // Pastikan tagihan milik mahasiswa yang login
        if (! $mahasiswa || $payment->mahasiswa_id !== $mahasiswa->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Tagihan yang sudah lunas tidak bisa dibayar lagi
        if ($payment->isPaid()) {
            return response()->json(['message' => 'Tagihan ini sudah lunas.'], 422);
        }

        // Validasi urutan pembayaran (harus sesuai sequence)
        $check = $this->paymentAccessService->canExecutePayment($payment);
        if (! $check['allowed']) {
            return response()->json(['message' => $check['reason']], 422);
        }

        try {
            $token = $this->midtransService->generateSnapToken($payment);

            return response()->json([
                'snap_token' => $token,
                'client_key' => config('midtrans.client_key'),
                'payment_id' => $payment->id,
                'amount' => (int) $payment->remaining_amount,
                'invoice' => $payment->invoice_number,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Halaman setelah mahasiswa menyelesaikan pembayaran di Snap.
     *
     * Midtrans akan redirect ke URL finish callback ini.
     * Status aktual dikonfirmasi via webhook, halaman ini hanya informasional.
     */
    public function finish(StudentPayment $payment): RedirectResponse|View
    {
        $mahasiswa = Auth::user()->mahasiswa;

        if (! $mahasiswa || $payment->mahasiswa_id !== $mahasiswa->id) {
            abort(403);
        }

        $payment->refresh();

        return match ($payment->status) {
            'paid' => view('mahasiswa.payments.midtrans-success', compact('payment')),
            'pending' => view('mahasiswa.payments.midtrans-pending', compact('payment')),
            default => view('mahasiswa.payments.midtrans-failed', compact('payment')),
        };
    }
}
