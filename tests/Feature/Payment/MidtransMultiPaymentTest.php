<?php

use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\PaymentHistory;
use App\Models\PaymentType;
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

function makeSignature(string $orderId, string $statusCode, string $grossAmount, string $serverKey): string
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
    $this->userMhs = User::factory()->create(['role' => 'mahasiswa', 'email' => 'mhs_multi@test.com']);

    $this->mahasiswa = Mahasiswa::create([
        'user_id' => $this->userMhs->id,
        'nim' => '2024099',
        'prodi_id' => $prodi->id,
        'angkatan' => '2024',
        'status' => 'aktif',
    ]);

    $type1 = PaymentType::orderBy('id', 'asc')->first();
    $type2 = PaymentType::where('id', '!=', $type1->id)->orderBy('id', 'asc')->first();

    $this->payment1 = StudentPayment::create([
        'mahasiswa_id' => $this->mahasiswa->id,
        'payment_type_id' => $type1->id,
        'amount' => 1925000,
        'paid_amount' => 0,
        'invoice_number' => 'INV-MULTI-001',
        'status' => 'unpaid',
    ]);

    $this->payment2 = StudentPayment::create([
        'mahasiswa_id' => $this->mahasiswa->id,
        'payment_type_id' => $type2->id,
        'amount' => 2500000,
        'paid_amount' => 0,
        'invoice_number' => 'INV-MULTI-002',
        'status' => 'unpaid',
    ]);

    $this->serverKey = 'SB-Mid-server-testkey';
    Config::set('midtrans.server_key', $this->serverKey);
    Config::set('midtrans.client_key', 'SB-Mid-client-testkey');
    Config::set('midtrans.is_production', false);
    Config::set('midtrans.base_url.snap', 'https://app.sandbox.midtrans.com/snap/v1');

    $this->service = app(MidtransService::class);
});

it('generates token for multiple sequential bills with admin fee 4000', function () {
    Http::fake([
        'app.sandbox.midtrans.com/snap/v1/transactions' => function (Request $request) {
            $data = $request->data();
            // Total gross amount = 1925000 + 500000 + 4000 = 2429000
            expect($data['transaction_details']['gross_amount'])->toBe(2429000);
            expect($data['item_details'])->toHaveCount(3);
            expect($data['item_details'][0]['price'])->toBe(1925000);
            expect($data['item_details'][1]['price'])->toBe(500000);
            expect($data['item_details'][2]['id'])->toBe('ADMIN-FEE');
            expect($data['item_details'][2]['price'])->toBe(4000);

            return Http::response([
                'token' => 'snap-token-multi-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-multi-123',
            ], 200);
        },
    ]);

    $response = $this->actingAs($this->userMhs)
        ->postJson("/mahasiswa/payments/{$this->payment1->id}/midtrans/token", [
            'amount' => 2425000,
            'bills' => [
                ['id' => $this->payment1->id, 'amount' => 1925000],
                ['id' => $this->payment2->id, 'amount' => 500000],
            ],
            'admin_fee' => 4000,
        ]);

    $response->assertSuccessful()
        ->assertJson([
            'snap_token' => 'snap-token-multi-123',
            'gross_amount' => 2429000,
            'subtotal' => 2425000,
            'admin_fee' => 4000,
        ]);

    $this->payment1->refresh();
    $this->payment2->refresh();

    expect($this->payment1->status)->toBe('pending');
    expect($this->payment2->status)->toBe('pending');
    expect($this->payment1->midtrans_order_id)->toBe($this->payment2->midtrans_order_id);
});

it('rejects multi-bill payment if first bill is not paid in full', function () {
    $response = $this->actingAs($this->userMhs)
        ->postJson("/mahasiswa/payments/{$this->payment1->id}/midtrans/token", [
            'bills' => [
                ['id' => $this->payment1->id, 'amount' => 1000000], // Sisa 925rb, belum penuh!
                ['id' => $this->payment2->id, 'amount' => 500000],
            ],
        ]);

    $response->assertStatus(422)
        ->assertJsonFragment([
            'message' => "Tagihan {$this->payment1->paymentType->name} harus dibayar penuh sebelum membayar tagihan berikutnya.",
        ]);
});

it('settles multi-bill transaction via midtrans webhook correctly', function () {
    Http::fake([
        'app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'snap-token-webhook-multi',
            'redirect_url' => 'https://example.com/redirect',
        ], 200),
    ]);

    // Inisiasi transaksi multi-tagihan
    $this->actingAs($this->userMhs)
        ->postJson("/mahasiswa/payments/{$this->payment1->id}/midtrans/token", [
            'bills' => [
                ['id' => $this->payment1->id, 'amount' => 1925000],
                ['id' => $this->payment2->id, 'amount' => 500000],
            ],
            'admin_fee' => 4000,
        ]);

    $this->payment1->refresh();
    $orderId = $this->payment1->midtrans_order_id;
    $statusCode = '200';
    $grossAmount = '2429000.00';

    $payload = [
        'order_id' => $orderId,
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'payment_type' => 'bank_transfer',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'settlement_time' => now()->toISOString(),
        'signature_key' => makeSignature($orderId, $statusCode, $grossAmount, $this->serverKey),
    ];

    $response = $this->postJson('/midtrans/notification', $payload);

    $response->assertSuccessful()
        ->assertJson(['status' => 'ok']);

    $this->payment1->refresh();
    $this->payment2->refresh();

    // Payment 1 lunas penuh
    expect($this->payment1->status)->toBe('paid');
    expect((float) $this->payment1->paid_amount)->toBe(1925000.0);

    // Payment 2 dicicil 500.000
    expect($this->payment2->status)->toBe('partial');
    expect((float) $this->payment2->paid_amount)->toBe(500000.0);
    expect((float) $this->payment2->remaining_amount)->toBe(2000000.0);

    // PaymentHistory tercatat untuk keduanya
    $h1 = PaymentHistory::where('student_payment_id', $this->payment1->id)->where('action', 'midtrans_settlement')->first();
    $h2 = PaymentHistory::where('student_payment_id', $this->payment2->id)->where('action', 'midtrans_partial_settlement')->first();

    expect($h1)->not->toBeNull();
    expect((float) $h1->amount)->toBe(1925000.0);
    expect($h2)->not->toBeNull();
    expect((float) $h2->amount)->toBe(500000.0);
});
