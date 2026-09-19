<?php

use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\PaymentAccessService;
use App\Services\PaymentInitializationService;
use Database\Seeders\PaymentTypeSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(PaymentTypeSeeder::class);

    $this->tahunAktif = TahunAkademik::factory()->create([
        'tahun' => '2024/2025',
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $this->fakultas = Fakultas::create(['nama' => 'Fakultas Tarbiyah', 'kode' => 'FT']);
    $this->prodi = Prodi::create(['nama' => 'Pendidikan Agama Islam', 'kode' => 'PAI', 'fakultas_id' => $this->fakultas->id]);

    $this->admin = User::factory()->create([
        'role' => 'admin_fakultas',
        'fakultas_id' => $this->fakultas->id,
    ]);
    $this->admin->assignRole('admin_fakultas');

    $this->user = User::factory()->create(['role' => 'mahasiswa']);
    $this->mahasiswa = Mahasiswa::create([
        'user_id' => $this->user->id,
        'nim' => '202401088',
        'prodi_id' => $this->prodi->id,
        'angkatan' => 2024,
        'status' => 'aktif',
        'is_krs_unlocked' => false,
    ]);

    app(PaymentInitializationService::class)->initializeStudentPayments($this->mahasiswa);
});

it('allows admin to toggle KRS lock to open KRS even if payments are unpaid', function () {
    $accessService = app(PaymentAccessService::class);

    // Initial state: unpaid, KRS should NOT be allowed
    $initialCheck = $accessService->checkKrsAccess($this->mahasiswa, 1);
    expect($initialCheck['allowed'])->toBeFalse();
    expect($this->mahasiswa->is_krs_unlocked)->toBeFalse();

    // Admin toggles lock -> unlocks KRS
    $response = $this->actingAs($this->admin)->post(route('admin.payments.student.toggle-krs', $this->mahasiswa->id));
    $response->assertSessionHas('success');

    $this->mahasiswa->refresh();
    expect($this->mahasiswa->is_krs_unlocked)->toBeTrue();

    // Now KRS check must allow entry despite unpaid bills
    $unlockedCheck = $accessService->checkKrsAccess($this->mahasiswa, 1);
    expect($unlockedCheck['allowed'])->toBeTrue();
    expect($unlockedCheck['reason'])->toContain('Dispensasi Pembayaran');

    // Admin toggles again -> locks KRS back
    $response2 = $this->actingAs($this->admin)->post(route('admin.payments.student.toggle-krs', $this->mahasiswa->id));
    $response2->assertSessionHas('success');

    $this->mahasiswa->refresh();
    expect($this->mahasiswa->is_krs_unlocked)->toBeFalse();

    $relockedCheck = $accessService->checkKrsAccess($this->mahasiswa, 1);
    expect($relockedCheck['allowed'])->toBeFalse();
});

it('displays the cashier layout and student payment details on admin student payment page', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.payments.student', $this->mahasiswa->id));

    $response->assertSuccessful();
    $response->assertSee('KASIR PEMBAYARAN TUNAI');
    $response->assertSee('Daftar Tagihan Mahasiswa');
    $response->assertSee('RIWAYAT &amp; CETAK KWITANSI', false);
    $response->assertSee('KRS Terkunci');
});

it('processes admin cash payment for student bills and logs payment history', function () {
    $payments = app(PaymentAccessService::class)->getOrderedPayments($this->mahasiswa);
    $regPayment = $payments->first();

    expect($regPayment->status)->toBe('unpaid');
    expect($regPayment->paid_amount)->toEqual(0);

    // Pay partial of registration fee (total 350000) as cash
    $payAmount = 150000;
    $response = $this->actingAs($this->admin)->post(route('admin.payments.student.cash-pay', $this->mahasiswa->id), [
        'payment_date' => now()->format('Y-m-d'),
        'reference_number' => 'CASH-TEST-001',
        'notes' => 'Pembayaran tunai di loket',
        'bills' => [
            [
                'id' => $regPayment->id,
                'amount' => $payAmount,
            ],
        ],
    ]);

    $response->assertSessionHas('success');

    $regPayment->refresh();
    expect($regPayment->status)->toBe('partial');
    expect((int) $regPayment->paid_amount)->toBe($payAmount);
    expect($regPayment->payment_method)->toBe('Tunai');

    // Check payment history
    $this->assertDatabaseHas('payment_histories', [
        'student_payment_id' => $regPayment->id,
        'action' => 'partial_payment',
        'amount' => $payAmount,
        'performed_by' => $this->admin->id,
    ]);
});

it('processes multiple bills cash payment without reference_number and notes provided in request', function () {
    $payments = app(PaymentAccessService::class)->getOrderedPayments($this->mahasiswa);
    $regPayment = $payments[0];
    $sem1Payment = $payments[1];

    // Pay registration full and sem 1 partial
    $response = $this->actingAs($this->admin)->post(route('admin.payments.student.cash-pay', $this->mahasiswa->id), [
        'payment_date' => now()->format('Y-m-d'),
        // reference_number and notes are omitted
        'bills' => [
            [
                'id' => $regPayment->id,
                'amount' => (int) $regPayment->remaining_amount,
            ],
            [
                'id' => $sem1Payment->id,
                'amount' => 500000,
            ],
        ],
    ]);

    $response->assertSessionHas('success');

    $regPayment->refresh();
    $sem1Payment->refresh();

    expect($regPayment->isPaid())->toBeTrue();
    expect($sem1Payment->status)->toBe('partial');
    expect((int) $sem1Payment->paid_amount)->toBe(500000);
});

it('allows kwitansi download for partial payments both for admin and mahasiswa', function () {
    $payments = app(PaymentAccessService::class)->getOrderedPayments($this->mahasiswa);
    $payment = $payments->first();

    // Partial payment
    $payment->update([
        'status' => 'partial',
        'paid_amount' => 300000,
        'payment_method' => 'Tunai',
        'payment_date' => now(),
    ]);

    // Admin kwitansi
    $adminResponse = $this->actingAs($this->admin)->get(route('admin.payments.receipt', $payment->id));
    $adminResponse->assertSuccessful();

    // Mahasiswa kwitansi
    $mhsResponse = $this->actingAs($this->user)->get(route('mahasiswa.payments.receipt', $payment->id));
    $mhsResponse->assertSuccessful();
});

it('blocks kwitansi download if payment is unpaid and has 0 paid amount', function () {
    $payments = app(PaymentAccessService::class)->getOrderedPayments($this->mahasiswa);
    $payment = $payments->first();

    expect($payment->status)->toBe('unpaid');
    expect($payment->paid_amount)->toEqual(0);

    // Admin tries to download empty kwitansi
    $adminResponse = $this->actingAs($this->admin)->get(route('admin.payments.receipt', $payment->id));
    $adminResponse->assertSessionHas('error');

    // Mahasiswa tries to download empty kwitansi
    $mhsResponse = $this->actingAs($this->user)->get(route('mahasiswa.payments.receipt', $payment->id));
    $mhsResponse->assertSessionHas('error');
});
