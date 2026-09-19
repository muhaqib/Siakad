<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\StudentPayment;
use App\Services\MidtransService;
use App\Services\PaymentAccessService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function generateToken(Request $request, StudentPayment $payment): JsonResponse
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

        $remaining = (float) $payment->remaining_amount;

        // Validasi nominal pembayaran jika diinput untuk cicilan
        $validated = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:10000', 'max:'.$remaining],
        ], [
            'amount.numeric' => 'Nominal pembayaran harus berupa angka valid.',
            'amount.min' => 'Nominal pembayaran minimal adalah Rp 10.000.',
            'amount.max' => 'Nominal pembayaran tidak boleh melebihi sisa tagihan (Rp '.number_format($remaining, 0, ',', '.').').',
        ]);

        $payAmount = isset($validated['amount']) && (float) $validated['amount'] > 0
            ? (float) $validated['amount']
            : $remaining;

        try {
            $transaction = $this->midtransService->createTransaction($payment, $payAmount);

            return response()->json([
                'snap_token' => $transaction['token'],
                'redirect_url' => $transaction['redirect_url'],
                'client_key' => config('midtrans.client_key'),
                'payment_id' => $payment->id,
                'amount' => (int) $payAmount,
                'invoice' => $payment->invoice_number,
                'is_installment' => $transaction['is_installment'],
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
            'paid', 'partial' => view('mahasiswa.payments.midtrans-success', compact('payment')),
            'pending' => view('mahasiswa.payments.midtrans-pending', compact('payment')),
            default => view('mahasiswa.payments.midtrans-failed', compact('payment')),
        };
    }
}
