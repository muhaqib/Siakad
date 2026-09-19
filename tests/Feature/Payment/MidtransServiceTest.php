<?php

use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\PaymentHistory;
use App\Models\Prodi;
use App\Models\StudentPayment;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\MidtransService;
use Database\Seeders\PaymentTypeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

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
    $userMhs = User::factory()->create(['role' => 'mahasiswa', 'email' => 'mhs@test.com']);

    $this->mahasiswa = Mahasiswa::create([
        'user_id' => $userMhs->id,
        'nim' => '2024001',
        'prodi_id' => $prodi->id,
        'angkatan' => '2024',
        'status' => 'aktif',
    ]);

    $this->payment = StudentPayment::create([
        'mahasiswa_id' => $this->mahasiswa->id,
        'payment_type_id' => 1,
        'amount' => 2500000,
        'paid_amount' => 0,
        'invoice_number' => 'INV-TEST-001',
        'status' => 'unpaid',
    ]);

    // Set config Midtrans untuk testing
    Config::set('midtrans.server_key', 'SB-Mid-server-testkey');
    Config::set('midtrans.client_key', 'SB-Mid-client-testkey');
    Config::set('midtrans.is_production', false);
    Config::set('midtrans.base_url.snap', 'https://app.sandbox.midtrans.com/snap/v1');

    $this->service = app(MidtransService::class);
});

/**
 * Test: Generate Snap Token sukses
 *
 * Mock HTTP call ke Midtrans agar tidak benar-benar memanggil API.
 */
it('generates snap token successfully', function () {
    Http::fake([
        'app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'test-snap-token-abc123',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/test-snap-token',
        ], 200),
    ]);

    $token = $this->service->generateSnapToken($this->payment);

    expect($token)->toBe('test-snap-token-abc123');

    // Cek kolom tersimpan di DB
    $this->payment->refresh();
    expect($this->payment->midtrans_token)->toBe('test-snap-token-abc123');
    expect($this->payment->midtrans_order_id)->toStartWith('INV-');
    expect($this->payment->status)->toBe('pending');
});

/**
 * Test: Generate Token mencatat PaymentHistory
 */
it('records payment history when token is generated', function () {
    Http::fake([
        'app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'snap-token-history-test',
        ], 200),
    ]);

    $this->service->generateSnapToken($this->payment);

    $history = PaymentHistory::where('student_payment_id', $this->payment->id)
        ->where('action', 'midtrans_initiated')
        ->first();

    expect($history)->not->toBeNull();
    expect($history->new_status)->toBe('pending');
});

/**
 * Test: Generate Token gagal jika Midtrans mengembalikan error
 */
it('throws exception when midtrans api returns error', function () {
    Http::fake([
        'app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'error_messages' => ['Transaction amount is required'],
        ], 422),
    ]);

    expect(fn () => $this->service->generateSnapToken($this->payment))
        ->toThrow(RuntimeException::class, 'Gagal membuat transaksi Midtrans');
});

/**
 * Test: Verifikasi signature yang valid
 */
it('verifies valid midtrans signature', function () {
    $orderId = 'INV-1-1234567890';
    $statusCode = '200';
    $grossAmount = '2500000.00';
    $serverKey = 'SB-Mid-server-testkey';

    $validSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

    $payload = [
        'order_id' => $orderId,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $validSignature,
    ];

    expect($this->service->verifySignature($payload))->toBeTrue();
});

/**
 * Test: Tolak signature yang tidak valid (cegah request palsu)
 */
it('rejects invalid midtrans signature', function () {
    $payload = [
        'order_id' => 'INV-1-1234567890',
        'status_code' => '200',
        'gross_amount' => '2500000.00',
        'signature_key' => 'invalid-signature-12345',
    ];

    expect($this->service->verifySignature($payload))->toBeFalse();
});

/**
 * Test: Generate Order ID memiliki format yang benar
 */
it('generates order id with correct format', function () {
    $orderId = $this->service->generateOrderId($this->payment);

    expect($orderId)->toStartWith('INV-'.$this->payment->id.'-');
    expect(strlen($orderId))->toBeGreaterThan(10);
});
