<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\PaymentHistory;
use App\Models\StudentPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    /**
     * Base64-encoded Server Key untuk HTTP Basic Auth ke Midtrans API.
     */
    private function serverKeyEncoded(): string
    {
        return base64_encode(config('midtrans.server_key').':');
    }

    /**
     * Generate Snap Token dari Midtrans API.
     *
     * Memanggil POST /snap/v1/transactions dan menyimpan token ke database.
     * Token ini digunakan di frontend untuk memicu popup Snap.
     *
     * @throws \RuntimeException jika Midtrans mengembalikan error
     */
    /**
     * Buat transaksi Snap di Midtrans API dan kembalikan token serta redirect_url.
     *
     * Memanggil POST /snap/v1/transactions sesuai spesifikasi resmi:
     * https://docs.midtrans.com/reference/snap-transaction
     *
     * @return array{token: string, redirect_url: string, order_id: string, amount: int, is_installment: bool}
     *
     * @throws \RuntimeException jika Midtrans mengembalikan error
     */
    public function createTransaction(StudentPayment $payment, ?float $payAmount = null): array
    {
        $payment->loadMissing(['mahasiswa.user', 'paymentType']);

        $remaining = (int) round($payment->remaining_amount);
        $amountToPay = $payAmount !== null ? (int) round($payAmount) : $remaining;
        $amountToPay = max(10000, min($remaining, $amountToPay));
        $isInstallment = $amountToPay < $remaining;

        $orderId = $this->generateOrderId($payment);
        $mahasiswa = $payment->mahasiswa;
        $user = $mahasiswa->user;

        $itemName = $isInstallment
            ? 'Cicilan '.($payment->paymentType->name ?? 'Pembayaran UKT')
            : ($payment->paymentType->name ?? 'Pembayaran UKT');

        $customerDetails = [
            'first_name' => substr($user->name ?? $mahasiswa->nim, 0, 50),
            'email' => $user->email ?? '',
        ];

        if (! empty($user->phone)) {
            $customerDetails['phone'] = substr(preg_replace('/[^0-9+]/', '', $user->phone), 0, 19);
        }

        // Parameter transaksi sesuai dokumentasi resmi Midtrans Snap API
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amountToPay,
            ],
            'item_details' => [
                [
                    'id' => (string) $payment->payment_type_id,
                    'price' => $amountToPay,
                    'quantity' => 1,
                    'name' => substr($itemName, 0, 50),
                ],
            ],
            'customer_details' => $customerDetails,
            'custom_field1' => substr('NIM: '.$mahasiswa->nim.' | Inv: '.$payment->invoice_number, 0, 255),
            'custom_field2' => (string) $payment->id,
            'custom_field3' => $isInstallment ? 'Cicilan' : 'Pelunasan',
            'callbacks' => [
                'finish' => route('mahasiswa.payments.midtrans.finish', $payment->id),
                'unfinish' => route('mahasiswa.payments.index'),
                'error' => route('mahasiswa.payments.index'),
            ],
            'page_expiry' => [
                'duration' => 24,
                'unit' => 'hour',
            ],
        ];

        $snapUrl = config('midtrans.base_url.snap').'/transactions';

        $response = Http::withHeaders([
            'Authorization' => 'Basic '.$this->serverKeyEncoded(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($snapUrl, $params);

        if (! $response->successful()) {
            $errorMessage = $response->json('error_messages.0') ?? $response->body();
            Log::error('Midtrans generateSnapToken failed', [
                'payment_id' => $payment->id,
                'status' => $response->status(),
                'error' => $errorMessage,
            ]);
            throw new \RuntimeException('Gagal membuat transaksi Midtrans: '.$errorMessage);
        }

        $token = (string) $response->json('token');
        $redirectUrl = (string) ($response->json('redirect_url') ?? '');

        // Simpan order_id dan token ke database, set status 'pending'
        $payment->update([
            'midtrans_order_id' => $orderId,
            'midtrans_token' => $token,
            'status' => 'pending',
        ]);

        // Catat di payment history
        PaymentHistory::create([
            'student_payment_id' => $payment->id,
            'action' => 'midtrans_initiated',
            'old_status' => $payment->getOriginal('status') ?? 'unpaid',
            'new_status' => 'pending',
            'amount' => $amountToPay,
            'notes' => $isInstallment
                ? 'Transaksi cicilan Midtrans dibuat: Rp '.number_format($amountToPay, 0, ',', '.')." (Order ID: {$orderId})"
                : "Transaksi Midtrans dibuat. Order ID: {$orderId}",
            'performed_by' => $mahasiswa->user_id,
        ]);

        return [
            'token' => $token,
            'redirect_url' => $redirectUrl,
            'order_id' => $orderId,
            'amount' => $amountToPay,
            'is_installment' => $isInstallment,
        ];
    }

    /**
     * Generate Snap Token dari Midtrans API (wrapper kemudahan createTransaction).
     *
     * @throws \RuntimeException jika Midtrans mengembalikan error
     */
    public function generateSnapToken(StudentPayment $payment, ?float $payAmount = null): string
    {
        $transaction = $this->createTransaction($payment, $payAmount);

        return $transaction['token'];
    }

    /**
     * Verifikasi signature key dari payload webhook Midtrans.
     *
     * Formula: SHA512(order_id + status_code + gross_amount + server_key)
     * Referensi: https://docs.midtrans.com/reference/verifying-payment-notification
     *
     * @return bool true jika signature valid
     */
    public function verifySignature(array $payload): bool
    {
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $serverKey = config('midtrans.server_key');

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expectedSignature, $payload['signature_key'] ?? '');
    }

    /**
     * Proses notifikasi webhook dari Midtrans dan update status pembayaran.
     *
     * Status Midtrans yang mungkin diterima:
     *  - settlement / capture → paid
     *  - pending               → pending
     *  - deny / expire / cancel → failed
     *
     * @throws \RuntimeException jika signature tidak valid atau payment tidak ditemukan
     */
    public function processNotification(array $payload): StudentPayment
    {
        // 1. Verifikasi signature untuk memastikan request dari Midtrans
        if (! $this->verifySignature($payload)) {
            Log::warning('Midtrans webhook: invalid signature', ['order_id' => $payload['order_id'] ?? null]);
            throw new \RuntimeException('Invalid Midtrans signature key.');
        }

        $orderId = $payload['order_id'] ?? '';

        // 2. Cari StudentPayment berdasarkan midtrans_order_id
        $payment = StudentPayment::where('midtrans_order_id', $orderId)
            ->with(['mahasiswa.user', 'paymentType'])
            ->firstOrFail();

        // 3. Tentukan status baru berdasarkan transaction_status dari Midtrans
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        $newStatus = $this->mapMidtransStatus($transactionStatus, $fraudStatus);

        // 4. Jangan proses ulang jika status sudah final (paid)
        if ($payment->isPaid()) {
            Log::info('Midtrans webhook: payment already settled, skipping.', ['order_id' => $orderId]);

            return $payment;
        }

        return DB::transaction(function () use ($payment, $payload, $newStatus, $transactionStatus) {
            $oldStatus = $payment->status;
            $grossAmount = (float) ($payload['gross_amount'] ?? $payment->remaining_amount);
            $midtransPayType = $payload['payment_type'] ?? null;
            $orderId = $payment->midtrans_order_id;

            // Jika pembayaran berhasil (settlement / capture)
            if ($newStatus === 'paid') {
                $newTotalPaid = (float) $payment->paid_amount + $grossAmount;
                $isFullPayment = $newTotalPaid >= (float) $payment->amount;
                $finalStatus = $isFullPayment ? 'paid' : 'partial';

                $updateData = [
                    'status' => $finalStatus,
                    'paid_amount' => min((float) $payment->amount, $newTotalPaid),
                    'midtrans_payment_type' => $midtransPayType,
                    'payment_method' => $this->humanizePaymentType($midtransPayType),
                    'payment_date' => now()->toDateString(),
                    'confirmed_at' => now(),
                ];

                $payment->update($updateData);

                // Catat di PaymentHistory untuk audit trail
                PaymentHistory::create([
                    'student_payment_id' => $payment->id,
                    'action' => $isFullPayment ? 'midtrans_'.$transactionStatus : 'midtrans_partial_'.$transactionStatus,
                    'old_status' => $oldStatus,
                    'new_status' => $finalStatus,
                    'amount' => $grossAmount,
                    'notes' => sprintf(
                        'Notifikasi Midtrans: %s | %s: Rp %s | Metode: %s | Order: %s',
                        strtoupper($transactionStatus),
                        $isFullPayment ? 'Pelunasan' : 'Pembayaran Cicilan',
                        number_format($grossAmount, 0, ',', '.'),
                        $midtransPayType ?? '-',
                        $orderId
                    ),
                    'performed_by' => null, // sistem otomatis
                ]);

                // Catat di ActivityLog
                $mhsName = $payment->mahasiswa->user->name ?? $payment->mahasiswa->nim;
                $typeName = $payment->paymentType->name ?? 'Pembayaran';
                $actionLabel = $isFullPayment ? 'Pelunasan otomatis' : 'Pembayaran cicilan otomatis';
                $remainingAfter = max(0, (float) $payment->amount - $newTotalPaid);

                app(ActivityLogService::class)->log(
                    action: "{$actionLabel} Midtrans {$typeName} mahasiswa {$mhsName} ({$payment->mahasiswa->nim}) sebesar Rp ".number_format($grossAmount, 0, ',', '.').' (Sisa: Rp '.number_format($remainingAfter, 0, ',', '.').')',
                    model: $payment,
                    changes: [
                        'status' => $finalStatus,
                        'paid_amount' => $newTotalPaid,
                        'installment_amount' => $grossAmount,
                        'remaining_amount' => $remainingAfter,
                    ]
                );

                // Kirim notifikasi in-app ke mahasiswa
                if ($payment->mahasiswa?->user) {
                    $this->sendPaymentNotification($payment, $finalStatus, $grossAmount);
                }

                $newStatus = $finalStatus;
            } else {
                $payment->update([
                    'status' => $newStatus,
                    'midtrans_payment_type' => $midtransPayType,
                ]);

                PaymentHistory::create([
                    'student_payment_id' => $payment->id,
                    'action' => 'midtrans_'.$transactionStatus,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'amount' => 0,
                    'notes' => sprintf(
                        'Notifikasi Midtrans: %s | Metode: %s | Order: %s',
                        strtoupper($transactionStatus),
                        $midtransPayType ?? '-',
                        $orderId
                    ),
                    'performed_by' => null,
                ]);

                if ($payment->mahasiswa?->user) {
                    $this->sendPaymentNotification($payment, $newStatus);
                }
            }

            Log::info('Midtrans webhook processed', [
                'order_id' => $orderId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return $payment->fresh();
        });
    }

    /**
     * Peta status Midtrans ke status internal aplikasi.
     */
    private function mapMidtransStatus(string $transactionStatus, string $fraudStatus): string
    {
        return match (true) {
            // Berhasil settlement (transfer bank, VA, dll)
            $transactionStatus === 'settlement' => 'paid',
            // Berhasil capture kartu kredit & bukan fraud
            $transactionStatus === 'capture' && $fraudStatus === 'accept' => 'paid',
            // Menunggu pembayaran
            $transactionStatus === 'pending' => 'pending',
            // Ditolak / kadaluarsa / dibatalkan
            default => 'failed',
        };
    }

    /**
     * Konversi kode payment_type Midtrans ke label yang lebih mudah dibaca.
     */
    private function humanizePaymentType(?string $paymentType): string
    {
        return match ($paymentType) {
            'credit_card' => 'Kartu Kredit (Midtrans)',
            'gopay' => 'GoPay (Midtrans)',
            'shopeepay' => 'ShopeePay (Midtrans)',
            'bank_transfer' => 'Transfer Bank (Midtrans)',
            'bca_va' => 'Virtual Account BCA (Midtrans)',
            'bni_va' => 'Virtual Account BNI (Midtrans)',
            'bri_va' => 'Virtual Account BRI (Midtrans)',
            'permata_va' => 'Virtual Account Permata (Midtrans)',
            'other_va' => 'Virtual Account (Midtrans)',
            'echannel' => 'Mandiri Bill (Midtrans)',
            'indomaret' => 'Indomaret (Midtrans)',
            'alfamart' => 'Alfamart (Midtrans)',
            'akulaku' => 'Akulaku (Midtrans)',
            default => 'Midtrans ('.($paymentType ?? 'Online').')',
        };
    }

    /**
     * Kirim notifikasi in-app ke mahasiswa setelah status berubah.
     */
    private function sendPaymentNotification(StudentPayment $payment, string $newStatus, float $paidAmount = 0): void
    {
        $typeName = $payment->paymentType?->name ?? 'Pembayaran';
        $amount = number_format($paidAmount > 0 ? $paidAmount : (float) $payment->paid_amount, 0, ',', '.');
        $remaining = number_format((float) $payment->remaining_amount, 0, ',', '.');

        [$notifType, $title, $message] = match ($newStatus) {
            'paid' => [
                Notification::TYPE_PAYMENT_CONFIRMED,
                'Pembayaran Berhasil via Midtrans',
                "Pembayaran {$typeName} sebesar Rp {$amount} telah berhasil lunas dikonfirmasi secara otomatis.",
            ],
            'partial' => [
                Notification::TYPE_PAYMENT_CONFIRMED,
                'Pembayaran Cicilan Diterima via Midtrans',
                "Pembayaran cicilan {$typeName} sebesar Rp {$amount} telah berhasil diterima. Sisa tagihan Anda saat ini: Rp {$remaining}.",
            ],
            'pending' => [
                Notification::TYPE_PAYMENT_PENDING,
                'Menunggu Konfirmasi Pembayaran',
                "Pembayaran {$typeName} Anda sedang dalam proses verifikasi. Kami akan memberi tahu Anda segera.",
            ],
            default => [
                Notification::TYPE_PAYMENT_FAILED,
                'Pembayaran Gagal / Dibatalkan',
                "Pembayaran {$typeName} Anda gagal atau dibatalkan. Silakan coba lagi.",
            ],
        };

        app(NotificationService::class)->send(
            user: $payment->mahasiswa->user,
            type: $notifType,
            title: $title,
            message: $message,
            data: [
                'payment_id' => $payment->id,
                'invoice_number' => $payment->invoice_number,
                'order_id' => $payment->midtrans_order_id,
            ]
        );
    }

    /**
     * Generate Order ID unik untuk dikirim ke Midtrans.
     * Format: INV-{payment_id}-{unix_timestamp}
     *
     * Timestamp ditambahkan agar bisa retry jika order sebelumnya expire.
     */
    public function generateOrderId(StudentPayment $payment): string
    {
        return 'INV-'.$payment->id.'-'.time().'-'.bin2hex(random_bytes(2));
    }
}
