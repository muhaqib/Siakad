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
    public function generateSnapToken(StudentPayment $payment): string
    {
        $payment->loadMissing(['mahasiswa.user', 'paymentType']);

        $orderId = $this->generateOrderId($payment);
        $mahasiswa = $payment->mahasiswa;
        $user = $mahasiswa->user;

        // Parameter transaksi yang dikirim ke Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $payment->remaining_amount, // nominal sisa tagihan
            ],
            'item_details' => [
                [
                    'id' => (string) $payment->payment_type_id,
                    'price' => (int) $payment->remaining_amount,
                    'quantity' => 1,
                    'name' => substr($payment->paymentType->name ?? 'Pembayaran UKT', 0, 50),
                ],
            ],
            'customer_details' => [
                'first_name' => $user->name ?? $mahasiswa->nim,
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
                'metadata' => [
                    'nim' => $mahasiswa->nim,
                    'invoice' => $payment->invoice_number,
                    'payment_id' => $payment->id,
                ],
            ],
            // Aktifkan semua metode pembayaran yang tersedia
            'enabled_payments' => [
                'credit_card', 'mandiri_clickpay', 'cimb_clicks',
                'bca_klikbca', 'bca_klikpay', 'bri_epay', 'echannel',
                'permata_va', 'bca_va', 'bni_va', 'bri_va', 'other_va',
                'gopay', 'indomaret', 'alfamart', 'danamon_online',
                'akulaku', 'shopeepay', 'qris',
            ],
            // Callback URL setelah transaksi selesai di sisi Snap
            'callbacks' => [
                'finish' => route('mahasiswa.payments.midtrans.finish', $payment->id),
            ],
            // Waktu kadaluarsa token (24 jam)
            'expiry' => [
                'unit' => 'hours',
                'duration' => 24,
            ],
        ];

        $snapUrl = config('midtrans.base_url.snap').'/transactions';

        $response = Http::withHeaders([
            'Authorization' => 'Basic '.$this->serverKeyEncoded(),
            'Content-Type' => 'application/json',
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

        $token = $response->json('token');

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
            'amount' => $payment->remaining_amount,
            'notes' => "Transaksi Midtrans dibuat. Order ID: {$orderId}",
            'performed_by' => $mahasiswa->user_id,
        ]);

        return $token;
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
            $transactionTime = $payload['settlement_time'] ?? $payload['transaction_time'] ?? now();

            $updateData = [
                'status' => $newStatus,
                'midtrans_payment_type' => $midtransPayType,
            ];

            // Jika pembayaran berhasil (settlement / capture)
            if ($newStatus === 'paid') {
                $updateData['paid_amount'] = $grossAmount;
                $updateData['payment_method'] = $this->humanizePaymentType($midtransPayType);
                $updateData['payment_date'] = now()->toDateString();
                $updateData['confirmed_at'] = now();
            }

            $payment->update($updateData);

            // Catat di PaymentHistory untuk audit trail
            PaymentHistory::create([
                'student_payment_id' => $payment->id,
                'action' => 'midtrans_'.$transactionStatus,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'amount' => $newStatus === 'paid' ? $grossAmount : 0,
                'notes' => sprintf(
                    'Notifikasi Midtrans: %s | Metode: %s | Order: %s',
                    strtoupper($transactionStatus),
                    $midtransPayType ?? '-',
                    $payment->midtrans_order_id
                ),
                'performed_by' => null, // sistem otomatis
            ]);

            // Kirim notifikasi in-app ke mahasiswa
            if ($payment->mahasiswa?->user) {
                $this->sendPaymentNotification($payment, $newStatus);
            }

            Log::info('Midtrans webhook processed', [
                'order_id' => $payment->midtrans_order_id,
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
    private function sendPaymentNotification(StudentPayment $payment, string $newStatus): void
    {
        $typeName = $payment->paymentType?->name ?? 'Pembayaran';
        $amount = number_format((float) $payment->paid_amount, 0, ',', '.');

        [$notifType, $title, $message] = match ($newStatus) {
            'paid' => [
                Notification::TYPE_PAYMENT_CONFIRMED,
                'Pembayaran Berhasil via Midtrans',
                "Pembayaran {$typeName} sebesar Rp {$amount} telah berhasil dikonfirmasi secara otomatis.",
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
        return 'INV-'.$payment->id.'-'.time();
    }
}
