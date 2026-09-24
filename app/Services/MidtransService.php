<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\PaymentHistory;
use App\Models\StudentPayment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
    public const ADMIN_FEE = 4000;

    /**
     * Buat transaksi Snap di Midtrans API untuk satu atau banyak tagihan (termasuk biaya admin).
     *
     * @param  array<int, array{payment: StudentPayment, amount: float|int}>  $items
     * @return array{token: string, redirect_url: string, order_id: string, gross_amount: int, amount: int, subtotal: int, admin_fee: int, is_installment: bool}
     *
     * @throws \RuntimeException jika Midtrans mengembalikan error
     */
    public function createBatchTransaction(array $items, int $adminFee = 0): array
    {
        if (empty($items)) {
            throw new \InvalidArgumentException('Tidak ada tagihan yang dipilih untuk pembayaran.');
        }

        $primaryPayment = $items[0]['payment'];
        $primaryPayment->loadMissing(['mahasiswa.user', 'paymentType']);
        $mahasiswa = $primaryPayment->mahasiswa;
        $user = $mahasiswa->user;

        $orderId = $this->generateOrderId($primaryPayment);

        $itemDetails = [];
        $subtotal = 0;
        $hasInstallment = false;

        foreach ($items as $item) {
            /** @var StudentPayment $payment */
            $payment = $item['payment'];
            $payment->loadMissing(['paymentType']);

            $remaining = (int) round($payment->remaining_amount);
            $payAmount = (int) round($item['amount']);
            $payAmount = max(10000, min($remaining, $payAmount));

            $isInstallment = $payAmount < $remaining;
            if ($isInstallment) {
                $hasInstallment = true;
            }

            $subtotal += $payAmount;
            $typeName = $payment->paymentType->name ?? 'Pembayaran UKT';
            $itemName = $isInstallment ? 'Cicilan '.$typeName : $typeName;

            $itemDetails[] = [
                'id' => (string) $payment->id,
                'price' => $payAmount,
                'quantity' => 1,
                'name' => substr($itemName, 0, 50),
            ];
        }

        if ($adminFee > 0) {
            $itemDetails[] = [
                'id' => 'ADMIN-FEE',
                'price' => $adminFee,
                'quantity' => 1,
                'name' => 'Biaya Admin Transaksi',
            ];
        }

        $grossAmount = $subtotal + $adminFee;

        $customerDetails = [
            'first_name' => substr($user->name ?? $mahasiswa->nim, 0, 50),
            'email' => $user->email ?? '',
        ];

        if (! empty($user->phone)) {
            $customerDetails['phone'] = substr(preg_replace('/[^0-9+]/', '', $user->phone), 0, 19);
        }

        $paymentIdsStr = implode(',', array_map(fn ($it) => $it['payment']->id, $items));

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'custom_field1' => substr('NIM: '.$mahasiswa->nim.' | Inv: '.$primaryPayment->invoice_number, 0, 255),
            'custom_field2' => substr($paymentIdsStr, 0, 255),
            'custom_field3' => count($items) > 1 ? 'Multi-Tagihan' : ($hasInstallment ? 'Cicilan' : 'Pelunasan'),
            'callbacks' => [
                'finish' => route('mahasiswa.payments.midtrans.finish', $primaryPayment->id),
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
                'order_id' => $orderId,
                'status' => $response->status(),
                'error' => $errorMessage,
            ]);
            throw new \RuntimeException('Gagal membuat transaksi Midtrans: '.$errorMessage);
        }

        $token = (string) $response->json('token');
        $redirectUrl = (string) ($response->json('redirect_url') ?? '');

        // Simpan order_id dan token ke database untuk setiap tagihan, set status 'pending'
        foreach ($items as $item) {
            /** @var StudentPayment $payment */
            $payment = $item['payment'];
            $payAmount = (int) round($item['amount']);
            $remaining = (int) round($payment->remaining_amount);
            $isInstallment = $payAmount < $remaining;

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
                'amount' => $payAmount,
                'notes' => $isInstallment
                    ? 'Transaksi cicilan Midtrans dibuat: Rp '.number_format($payAmount, 0, ',', '.')." (Order ID: {$orderId})"
                    : 'Transaksi Midtrans dibuat: Rp '.number_format($payAmount, 0, ',', '.')." (Order ID: {$orderId})",
                'performed_by' => $mahasiswa->user_id,
            ]);
        }

        return [
            'token' => $token,
            'redirect_url' => $redirectUrl,
            'order_id' => $orderId,
            'gross_amount' => $grossAmount,
            'amount' => $grossAmount,
            'subtotal' => $subtotal,
            'admin_fee' => $adminFee,
            'is_installment' => $hasInstallment,
        ];
    }

    /**
     * Buat transaksi Snap di Midtrans API untuk satu tagihan (wrapper).
     *
     * @return array{token: string, redirect_url: string, order_id: string, amount: int, gross_amount: int, subtotal: int, admin_fee: int, is_installment: bool}
     *
     * @throws \RuntimeException jika Midtrans mengembalikan error
     */
    public function createTransaction(StudentPayment $payment, ?float $payAmount = null, int $adminFee = 0): array
    {
        $remaining = (int) round($payment->remaining_amount);
        $amountToPay = $payAmount !== null ? (int) round($payAmount) : $remaining;
        $amountToPay = max(10000, min($remaining, $amountToPay));

        return $this->createBatchTransaction([
            ['payment' => $payment, 'amount' => $amountToPay],
        ], $adminFee);
    }

    /**
     * Generate Snap Token dari Midtrans API (wrapper kemudahan createTransaction).
     *
     * @throws \RuntimeException jika Midtrans mengembalikan error
     */
    public function generateSnapToken(StudentPayment $payment, ?float $payAmount = null, int $adminFee = 0): string
    {
        $transaction = $this->createTransaction($payment, $payAmount, $adminFee);

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
     * Mendukung pemrosesan multi-tagihan yang berada dalam satu order ID.
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

        // 2. Cari semua StudentPayment yang terkait dengan midtrans_order_id
        $payments = StudentPayment::where('midtrans_order_id', $orderId)
            ->with(['mahasiswa.user', 'paymentType'])
            ->orderBy('id', 'asc')
            ->get();

        if ($payments->isEmpty()) {
            throw (new ModelNotFoundException)->setModel(StudentPayment::class);
        }

        // 3. Tentukan status baru berdasarkan transaction_status dari Midtrans
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        $newStatus = $this->mapMidtransStatus($transactionStatus, $fraudStatus);

        // 4. Jangan proses ulang jika semua tagihan sudah final (paid)
        if ($payments->every(fn ($p) => $p->isPaid())) {
            Log::info('Midtrans webhook: payments already settled, skipping.', ['order_id' => $orderId]);

            return $payments->first();
        }

        return DB::transaction(function () use ($payments, $payload, $newStatus, $transactionStatus) {
            $midtransPayType = $payload['payment_type'] ?? null;
            $orderId = $payload['order_id'] ?? '';
            $grossAmount = (float) ($payload['gross_amount'] ?? 0);

            foreach ($payments as $payment) {
                if ($payment->isPaid()) {
                    continue;
                }

                $oldStatus = $payment->status;

                // Jika pembayaran berhasil (settlement / capture)
                if ($newStatus === 'paid') {
                    // Cari nominal alokasi tagihan dari riwayat midtrans_initiated
                    $initiatedHistory = PaymentHistory::where('student_payment_id', $payment->id)
                        ->where('action', 'midtrans_initiated')
                        ->where('notes', 'like', "%{$orderId}%")
                        ->latest()
                        ->first();

                    $allocatedAmount = $initiatedHistory?->amount !== null
                        ? (float) $initiatedHistory->amount
                        : min((float) $payment->remaining_amount, $grossAmount);

                    $newTotalPaid = (float) $payment->paid_amount + $allocatedAmount;
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

                    // Note: KRS access is now determined dynamically by PaymentAccessService
                    // based on the minimum payment threshold (config: siakad.krs_minimum_payment).
                    // The is_krs_unlocked flag is reserved for manual admin dispensation only.

                    // Catat di PaymentHistory untuk audit trail
                    PaymentHistory::create([
                        'student_payment_id' => $payment->id,
                        'action' => $isFullPayment ? 'midtrans_'.$transactionStatus : 'midtrans_partial_'.$transactionStatus,
                        'old_status' => $oldStatus,
                        'new_status' => $finalStatus,
                        'amount' => $allocatedAmount,
                        'notes' => sprintf(
                            'Notifikasi Midtrans: %s | %s: Rp %s | Metode: %s | Order: %s',
                            strtoupper($transactionStatus),
                            $isFullPayment ? 'Pelunasan' : 'Pembayaran Cicilan',
                            number_format($allocatedAmount, 0, ',', '.'),
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
                        action: "{$actionLabel} Midtrans {$typeName} mahasiswa {$mhsName} ({$payment->mahasiswa->nim}) sebesar Rp ".number_format($allocatedAmount, 0, ',', '.').' (Sisa: Rp '.number_format($remainingAfter, 0, ',', '.').')',
                        model: $payment,
                        changes: [
                            'status' => $finalStatus,
                            'paid_amount' => $newTotalPaid,
                            'installment_amount' => $allocatedAmount,
                            'remaining_amount' => $remainingAfter,
                        ]
                    );

                    // Kirim notifikasi in-app ke mahasiswa
                    if ($payment->mahasiswa?->user) {
                        $this->sendPaymentNotification($payment, $finalStatus, $allocatedAmount);
                    }
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
            }

            Log::info('Midtrans webhook processed for all payments', [
                'order_id' => $orderId,
                'count' => $payments->count(),
                'new_status' => $newStatus,
            ]);

            return $payments->first()->fresh();
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
