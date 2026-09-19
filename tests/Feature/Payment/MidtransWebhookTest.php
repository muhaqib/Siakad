<?php

use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\PaymentHistory;
use App\Models\Prodi;
use App\Models\StudentPayment;
use App\Models\TahunAkademik;
use App\Models\User;
use Database\Seeders\PaymentTypeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Config;

/**
 * Helper: Buat signature Midtrans yang valid untuk testing.
 */
function makeMidtransSignature(string $orderId, string $statusCode, string $grossAmount, string $serverKey): string
{
    return hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PaymentTypeSeeder::class);

    $this->tahunAktif = TahunAkademik::factory()->create([
        'tahun' => '2024/2025',
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $fakultas = Fakultas::create(['nama' => 'Fakultas Tarbiyah', 'kode' => 'FT']);
    $prodi = Prodi::create(['nama' => 'Pendidikan Agama Islam', 'kode' => 'PAI', 'fakultas_id' => $fakultas->id]);
    $userMhs = User::factory()->create(['role' => 'mahasiswa', 'email' => 'mhswebhook@test.com']);

    $this->mahasiswa = Mahasiswa::create([
        'user_id' => $userMhs->id,
        'nim' => '2024002',
        'prodi_id' => $prodi->id,
        'angkatan' => '2024',
        'status' => 'aktif',
    ]);

    $this->serverKey = 'SB-Mid-server-testkey';
    Config::set('midtrans.server_key', $this->serverKey);
    Config::set('midtrans.client_key', 'SB-Mid-client-testkey');
    Config::set('midtrans.is_production', false);

    // Buat payment dalam status 'pending' (sudah memiliki midtrans_order_id)
    $this->orderId = 'INV-99-'.time();
    $this->payment = StudentPayment::create([
        'mahasiswa_id' => $this->mahasiswa->id,
        'payment_type_id' => 1,
        'amount' => 3000000,
        'paid_amount' => 0,
        'invoice_number' => 'INV-WEBHOOK-001',
        'midtrans_order_id' => $this->orderId,
        'midtrans_token' => 'snap-token-test',
        'status' => 'pending',
    ]);
});

/**
 * Test: Webhook settlement mengubah status menjadi 'paid'
 */
it('processes settlement notification and marks payment as paid', function () {
    $orderId = $this->orderId;
    $statusCode = '200';
    $grossAmount = '3000000.00';

    $payload = [
        'order_id' => $orderId,
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'payment_type' => 'bank_transfer',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'settlement_time' => now()->toISOString(),
        'signature_key' => makeMidtransSignature($orderId, $statusCode, $grossAmount, $this->serverKey),
    ];

    $response = $this->postJson('/midtrans/notification', $payload);

    $response->assertSuccessful()
        ->assertJson(['status' => 'ok', 'new_status' => 'paid']);

    $this->payment->refresh();
    expect($this->payment->status)->toBe('paid');
    expect((float) $this->payment->paid_amount)->toBe(3000000.0);
    expect($this->payment->payment_method)->toContain('Midtrans');
});

/**
 * Test: Webhook 'capture' dengan fraud_status 'accept' juga berhasil
 */
it('processes capture with accept fraud status as paid', function () {
    $orderId = $this->orderId;
    $statusCode = '200';
    $grossAmount = '3000000.00';

    $payload = [
        'order_id' => $orderId,
        'transaction_status' => 'capture',
        'fraud_status' => 'accept',
        'payment_type' => 'credit_card',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => makeMidtransSignature($orderId, $statusCode, $grossAmount, $this->serverKey),
    ];

    $response = $this->postJson('/midtrans/notification', $payload);

    $response->assertSuccessful();

    $this->payment->refresh();
    expect($this->payment->status)->toBe('paid');
});

/**
 * Test: Webhook pending tidak mengubah status menjadi paid
 */
it('keeps status as pending for pending notification', function () {
    $orderId = $this->orderId;
    $statusCode = '201';
    $grossAmount = '3000000.00';

    $payload = [
        'order_id' => $orderId,
        'transaction_status' => 'pending',
        'fraud_status' => '',
        'payment_type' => 'bca_va',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => makeMidtransSignature($orderId, $statusCode, $grossAmount, $this->serverKey),
    ];

    $response = $this->postJson('/midtrans/notification', $payload);

    $response->assertSuccessful();

    $this->payment->refresh();
    expect($this->payment->status)->toBe('pending');
    expect((float) $this->payment->paid_amount)->toBe(0.0); // Belum ada uang masuk
});

/**
 * Test: Webhook 'deny' mengubah status menjadi 'failed'
 */
it('processes deny notification and marks as failed', function () {
    $orderId = $this->orderId;
    $statusCode = '202';
    $grossAmount = '3000000.00';

    $payload = [
        'order_id' => $orderId,
        'transaction_status' => 'deny',
        'fraud_status' => 'deny',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => makeMidtransSignature($orderId, $statusCode, $grossAmount, $this->serverKey),
    ];

    $response = $this->postJson('/midtrans/notification', $payload);

    $response->assertSuccessful();

    $this->payment->refresh();
    expect($this->payment->status)->toBe('failed');
});

/**
 * Test: Webhook 'expire' mengubah status menjadi 'failed'
 */
it('processes expire notification and marks as failed', function () {
    $orderId = $this->orderId;
    $statusCode = '407';
    $grossAmount = '3000000.00';

    $payload = [
        'order_id' => $orderId,
        'transaction_status' => 'expire',
        'fraud_status' => '',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => makeMidtransSignature($orderId, $statusCode, $grossAmount, $this->serverKey),
    ];

    $response = $this->postJson('/midtrans/notification', $payload);

    $response->assertSuccessful();

    $this->payment->refresh();
    expect($this->payment->status)->toBe('failed');
});

/**
 * Test: Webhook 'cancel' mengubah status menjadi 'failed'
 */
it('processes cancel notification and marks as failed', function () {
    $orderId = $this->orderId;
    $statusCode = '200';
    $grossAmount = '3000000.00';

    $payload = [
        'order_id' => $orderId,
        'transaction_status' => 'cancel',
        'fraud_status' => '',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => makeMidtransSignature($orderId, $statusCode, $grossAmount, $this->serverKey),
    ];

    $response = $this->postJson('/midtrans/notification', $payload);

    $response->assertSuccessful();

    $this->payment->refresh();
    expect($this->payment->status)->toBe('failed');
});

/**
 * Test: Webhook dengan signature tidak valid harus ditolak (403)
 */
it('rejects webhook with invalid signature', function () {
    $payload = [
        'order_id' => $this->orderId,
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'status_code' => '200',
        'gross_amount' => '3000000.00',
        'signature_key' => 'this-is-totally-invalid-signature-key',
    ];

    $response = $this->postJson('/midtrans/notification', $payload);

    $response->assertStatus(403);

    // Status tidak berubah
    $this->payment->refresh();
    expect($this->payment->status)->toBe('pending');
});

/**
 * Test: Webhook dengan order_id yang tidak dikenal mengembalikan 200 (bukan crash)
 */
it('returns 200 for unknown order id to prevent midtrans retries', function () {
    $orderId = 'INV-UNKNOWN-999999';
    $statusCode = '200';
    $grossAmount = '1000000.00';

    $payload = [
        'order_id' => $orderId,
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => makeMidtransSignature($orderId, $statusCode, $grossAmount, $this->serverKey),
    ];

    $response = $this->postJson('/midtrans/notification', $payload);

    $response->assertSuccessful()
        ->assertJson(['status' => 'order_not_found']);
});

/**
 * Test: Webhook mencatat PaymentHistory saat settlement
 */
it('records payment history on settlement', function () {
    $orderId = $this->orderId;
    $statusCode = '200';
    $grossAmount = '3000000.00';

    $payload = [
        'order_id' => $orderId,
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'payment_type' => 'gopay',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => makeMidtransSignature($orderId, $statusCode, $grossAmount, $this->serverKey),
    ];

    $this->postJson('/midtrans/notification', $payload);

    $history = PaymentHistory::where('student_payment_id', $this->payment->id)
        ->where('action', 'midtrans_settlement')
        ->first();

    expect($history)->not->toBeNull();
    expect($history->new_status)->toBe('paid');
    expect($history->performed_by)->toBeNull(); // otomatis oleh sistem
});

/**
 * Test: Payment yang sudah 'paid' tidak diproses ulang (idempotency)
 */
it('skips already settled payment idempotently', function () {
    // Set ke paid dulu
    $this->payment->update(['status' => 'paid', 'paid_amount' => 3000000]);

    $orderId = $this->orderId;
    $statusCode = '200';
    $grossAmount = '3000000.00';

    $payload = [
        'order_id' => $orderId,
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => makeMidtransSignature($orderId, $statusCode, $grossAmount, $this->serverKey),
    ];

    $response = $this->postJson('/midtrans/notification', $payload);

    $response->assertSuccessful();

    // Status tetap 'paid' — tidak ada double processing
    $this->payment->refresh();
    expect($this->payment->status)->toBe('paid');
});
