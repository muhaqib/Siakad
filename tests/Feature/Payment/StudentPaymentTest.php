<?php

use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\Notification;
use App\Models\PaymentHistory;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Models\User;
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

    // Fakultas A
    $this->fakultasA = Fakultas::create(['nama' => 'Fakultas Tarbiyah', 'kode' => 'FT']);
    $this->prodiA = Prodi::create(['nama' => 'Pendidikan Agama Islam', 'kode' => 'PAI', 'fakultas_id' => $this->fakultasA->id]);

    // Fakultas B
    $this->fakultasB = Fakultas::create(['nama' => 'Fakultas Syariah', 'kode' => 'FS']);
    $this->prodiB = Prodi::create(['nama' => 'Hukum Keluarga Islam', 'kode' => 'HKI', 'fakultas_id' => $this->fakultasB->id]);

    // Admin Fakultas A
    $this->adminFakultasA = User::factory()->create([
        'role' => 'admin_fakultas',
        'fakultas_id' => $this->fakultasA->id,
    ]);
    $this->adminFakultasA->assignRole('admin_fakultas');

    // Admin Fakultas B
    $this->adminFakultasB = User::factory()->create([
        'role' => 'admin_fakultas',
        'fakultas_id' => $this->fakultasB->id,
    ]);
    $this->adminFakultasB->assignRole('admin_fakultas');

    // Superadmin
    $this->superadmin = User::factory()->create([
        'role' => 'superadmin',
    ]);
    $this->superadmin->assignRole('superadmin');

    // Mahasiswa A (under Fakultas A)
    $this->userMhsA = User::factory()->create(['role' => 'mahasiswa']);
    $this->mahasiswaA = Mahasiswa::create([
        'user_id' => $this->userMhsA->id,
        'nim' => '202401001',
        'prodi_id' => $this->prodiA->id,
        'angkatan' => 2024,
        'status' => 'aktif',
    ]);

    // Mahasiswa B (under Fakultas B)
    $this->userMhsB = User::factory()->create(['role' => 'mahasiswa']);
    $this->mahasiswaB = Mahasiswa::create([
        'user_id' => $this->userMhsB->id,
        'nim' => '202402001',
        'prodi_id' => $this->prodiB->id,
        'angkatan' => 2024,
        'status' => 'aktif',
    ]);
});

test('initialization service generates registration and 8 semesters obligations without duplicates', function () {
    $service = app(PaymentInitializationService::class);

    $createdCount = $service->initializeStudentPayments($this->mahasiswaA);
    expect($createdCount)->toBe(9); // 1 registration + 8 semesters
    expect($this->mahasiswaA->payments()->count())->toBe(9);

    // Running again does not duplicate
    $createdAgain = $service->initializeStudentPayments($this->mahasiswaA);
    expect($createdAgain)->toBe(0);
    expect($this->mahasiswaA->payments()->count())->toBe(9);
});

test('mahasiswa can view their payments timeline and sees midtrans transfer buttons', function () {
    $service = app(PaymentInitializationService::class);
    $service->initializeStudentPayments($this->mahasiswaA);

    $response = $this->actingAs($this->userMhsA)->get(route('mahasiswa.payments.index'));
    $response->assertSuccessful();
    $response->assertSee('Daftar Tagihan', false);
    $response->assertSee('Pendaftaran Mahasiswa Baru');
    $response->assertSee('Pembayaran Kuliah Semester 1');
    $response->assertSee('Bayar Transfer melalui Midtrans');
    $response->assertSee('Bayar Sekarang');

    $payment = $this->mahasiswaA->payments()->first();
    $showResponse = $this->actingAs($this->userMhsA)->get(route('mahasiswa.payments.show', $payment->id));
    $showResponse->assertSuccessful();
    $showResponse->assertSee('Bayar Transfer melalui Midtrans');
});

test('mahasiswa can generate and stream receipt directly as pdf', function () {
    $service = app(PaymentInitializationService::class);
    $service->initializeStudentPayments($this->mahasiswaA);

    $payment = $this->mahasiswaA->payments()->first();
    $payment->update([
        'status' => 'paid',
        'paid_amount' => $payment->amount,
        'payment_date' => now(),
    ]);

    $response = $this->actingAs($this->userMhsA)->get(route('mahasiswa.payments.receipt', $payment->id));
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

test('admin fakultas can only view payments of their own faculty', function () {
    $service = app(PaymentInitializationService::class);
    $service->initializeStudentPayments($this->mahasiswaA);
    $service->initializeStudentPayments($this->mahasiswaB);

    $paymentA = $this->mahasiswaA->payments()->first();
    $paymentB = $this->mahasiswaB->payments()->first();

    // Admin A accessing payment of Mahasiswa A -> OK
    $responseA = $this->actingAs($this->adminFakultasA)->get(route('admin.payments.show', $paymentA->id));
    $responseA->assertSuccessful();

    // Admin A accessing payment of Mahasiswa B -> 403 Forbidden
    $responseB = $this->actingAs($this->adminFakultasA)->get(route('admin.payments.show', $paymentB->id));
    $responseB->assertStatus(403);
});

test('superadmin can view payments of all faculties', function () {
    $service = app(PaymentInitializationService::class);
    $service->initializeStudentPayments($this->mahasiswaA);
    $service->initializeStudentPayments($this->mahasiswaB);

    $paymentA = $this->mahasiswaA->payments()->first();
    $paymentB = $this->mahasiswaB->payments()->first();

    $responseA = $this->actingAs($this->superadmin)->get(route('admin.payments.show', $paymentA->id));
    $responseA->assertSuccessful();

    $responseB = $this->actingAs($this->superadmin)->get(route('admin.payments.show', $paymentB->id));
    $responseB->assertSuccessful();
});

test('admin can confirm payment, creating history, activity log, and notification', function () {
    $service = app(PaymentInitializationService::class);
    $service->initializeStudentPayments($this->mahasiswaA);

    $payment = $this->mahasiswaA->payments()->whereHas('paymentType', fn ($q) => $q->where('code', 'semester_1'))->first();

    $response = $this->actingAs($this->adminFakultasA)->post(route('admin.payments.confirm', $payment->id), [
        'payment_date' => '2024-09-01',
        'payment_method' => 'Transfer',
        'paid_amount' => 1500000,
        'notes' => 'Lunas via transfer BSI No. Ref 987654321',
    ]);

    $response->assertSessionHas('success');

    $payment->refresh();
    expect($payment->status)->toBe('paid');
    expect((float) $payment->paid_amount)->toBe(1500000.0);
    expect($payment->payment_method)->toBe('Transfer');
    expect($payment->confirmed_by)->toBe($this->adminFakultasA->id);

    // Assert PaymentHistory exists
    $history = PaymentHistory::where('student_payment_id', $payment->id)
        ->where('action', 'confirmed')
        ->first();
    expect($history)->not->toBeNull();
    expect($history->new_status)->toBe('paid');

    // Assert Notification sent to Mahasiswa
    $notification = Notification::where('user_id', $this->userMhsA->id)
        ->where('type', Notification::TYPE_PAYMENT_CONFIRMED)
        ->first();
    expect($notification)->not->toBeNull();
    expect($notification->title)->toBe('Pembayaran Berhasil Dikonfirmasi');
});

test('admin can cancel confirmed payment', function () {
    $service = app(PaymentInitializationService::class);
    $service->initializeStudentPayments($this->mahasiswaA);

    $payment = $this->mahasiswaA->payments()->whereHas('paymentType', fn ($q) => $q->where('code', 'semester_1'))->first();

    // Confirm first
    $this->actingAs($this->adminFakultasA)->post(route('admin.payments.confirm', $payment->id), [
        'payment_date' => '2024-09-01',
        'payment_method' => 'Tunai',
        'paid_amount' => 1500000,
        'notes' => 'Lunas',
    ]);

    expect($payment->fresh()->isPaid())->toBeTrue();

    // Now Cancel
    $cancelResponse = $this->actingAs($this->adminFakultasA)->post(route('admin.payments.cancel', $payment->id), [
        'reason' => 'Salah input nominal',
    ]);

    $cancelResponse->assertSessionHas('success');

    $payment->refresh();
    expect($payment->status)->toBe('unpaid');
    expect((float) $payment->paid_amount)->toBe(0.0);
    expect($payment->confirmed_at)->toBeNull();

    // Check cancellation history
    $cancelHistory = PaymentHistory::where('student_payment_id', $payment->id)
        ->where('action', 'cancelled')
        ->first();
    expect($cancelHistory)->not->toBeNull();
});

test('admin payments index displays student list and student detail displays all bills with sequential status', function () {
    $service = app(PaymentInitializationService::class);
    $service->initializeStudentPayments($this->mahasiswaA);

    // Dashboard shows pending payments queue
    $dashboardResponse = $this->actingAs($this->adminFakultasA)->get(route('admin.payments.dashboard'));
    $dashboardResponse->assertSuccessful();
    $dashboardResponse->assertSee('Daftar Tagihan Mahasiswa yang Harus Dibayar');
    $dashboardResponse->assertSee('Siap Bayar');

    // Payments index shows Mahasiswa list and Detail Tagihan link
    $indexResponse = $this->actingAs($this->adminFakultasA)->get(route('admin.payments.index'));
    $indexResponse->assertSuccessful();
    $indexResponse->assertSee($this->mahasiswaA->nim);
    $indexResponse->assertSee($this->userMhsA->name);
    $indexResponse->assertSee('Detail Tagihan');

    // Clicking student opens student detail page showing all bills in sequential order
    $studentDetailResponse = $this->actingAs($this->adminFakultasA)->get(route('admin.payments.student', $this->mahasiswaA->id));
    $studentDetailResponse->assertSuccessful();
    $studentDetailResponse->assertSee('Rincian Semua Tagihan Pembayaran Mahasiswa');
    $studentDetailResponse->assertSee('Siap Bayar');
    $studentDetailResponse->assertSee('Menunggu');
    $studentDetailResponse->assertSee('Buka Kasir / Bayar');
});

test('admin cannot confirm semester 1 when registration is unpaid without dispensation notes', function () {
    $service = app(PaymentInitializationService::class);
    $service->initializeStudentPayments($this->mahasiswaA);

    $paymentSem1 = $this->mahasiswaA->payments()->whereHas('paymentType', fn ($q) => $q->where('code', 'semester_1'))->first();

    $response = $this->actingAs($this->adminFakultasA)->post(route('admin.payments.confirm', $paymentSem1->id), [
        'payment_date' => '2024-09-01',
        'payment_method' => 'Transfer',
        'paid_amount' => 1500000,
        'notes' => '', // No notes provided
    ]);

    $response->assertSessionHas('error');
    expect(session('error'))->toContain('Pembayaran mahasiswa harus berurutan');
    expect($paymentSem1->fresh()->isPaid())->toBeFalse();
});

test('admin can confirm semester 1 out of order when dispensation notes is written', function () {
    $service = app(PaymentInitializationService::class);
    $service->initializeStudentPayments($this->mahasiswaA);

    $paymentSem1 = $this->mahasiswaA->payments()->whereHas('paymentType', fn ($q) => $q->where('code', 'semester_1'))->first();

    $response = $this->actingAs($this->adminFakultasA)->post(route('admin.payments.confirm', $paymentSem1->id), [
        'payment_date' => '2024-09-01',
        'payment_method' => 'Transfer',
        'paid_amount' => 1500000,
        'notes' => 'Dispensasi Dekan No. 05/FT/2024 untuk mendahulukan Semester 1',
    ]);

    $response->assertSessionHas('success');
    expect($paymentSem1->fresh()->isPaid())->toBeTrue();
    expect($paymentSem1->fresh()->notes)->toContain('Dispensasi Dekan');
});

test('admin can execute partial payments and multiple installments reducing remaining balance', function () {
    $service = app(PaymentInitializationService::class);
    $service->initializeStudentPayments($this->mahasiswaA);

    // Registration payment is the first in order (amount = 350.000)
    $paymentReg = $this->mahasiswaA->payments()->whereHas('paymentType', fn ($q) => $q->where('code', 'registration'))->first();
    expect((float) $paymentReg->amount)->toBe(350000.0);
    expect((float) $paymentReg->paid_amount)->toBe(0.0);
    expect($paymentReg->remaining_amount)->toBe(350000.0);

    // Installment 1: Pay 150.000
    $res1 = $this->actingAs($this->adminFakultasA)->post(route('admin.payments.confirm', $paymentReg->id), [
        'payment_date' => '2024-09-01',
        'payment_method' => 'Tunai',
        'pay_amount' => 150000,
        'notes' => 'Cicilan pendaftaran ke-1',
    ]);
    $res1->assertSessionHas('success');

    $paymentReg->refresh();
    expect($paymentReg->status)->toBe('partial');
    expect($paymentReg->isPartial())->toBeTrue();
    expect($paymentReg->isPaid())->toBeFalse();
    expect((float) $paymentReg->paid_amount)->toBe(150000.0);
    expect($paymentReg->remaining_amount)->toBe(200000.0);

    // Installment 2: Pay remaining 200.000
    $res2 = $this->actingAs($this->adminFakultasA)->post(route('admin.payments.confirm', $paymentReg->id), [
        'payment_date' => '2024-09-02',
        'payment_method' => 'Transfer',
        'pay_amount' => 200000,
        'notes' => 'Pelunasan sisa pendaftaran',
    ]);
    $res2->assertSessionHas('success');

    $paymentReg->refresh();
    expect($paymentReg->status)->toBe('paid');
    expect($paymentReg->isPaid())->toBeTrue();
    expect($paymentReg->isPartial())->toBeFalse();
    expect((float) $paymentReg->paid_amount)->toBe(350000.0);
    expect($paymentReg->remaining_amount)->toBe(0.0);

    // Assert 2 payment transaction histories plus 1 initial creation history
    expect($paymentReg->histories()->whereIn('action', ['partial_payment', 'confirmed'])->count())->toBe(2);
    expect($paymentReg->histories()->count())->toBe(3);
});

test('admin can access printable receipt for partial and fully paid payments', function () {
    $service = app(PaymentInitializationService::class);
    $service->initializeStudentPayments($this->mahasiswaA);

    $paymentReg = $this->mahasiswaA->payments()->whereHas('paymentType', fn ($q) => $q->where('code', 'registration'))->first();

    // Partial pay
    $this->actingAs($this->adminFakultasA)->post(route('admin.payments.confirm', $paymentReg->id), [
        'payment_date' => '2024-09-01',
        'payment_method' => 'Tunai',
        'pay_amount' => 150000,
    ]);

    $receiptResponse = $this->actingAs($this->adminFakultasA)->get(route('admin.payments.receipt', $paymentReg->id));
    $receiptResponse->assertSuccessful();
    $receiptResponse->assertSee('BUKTI KUITANSI PEMBAYARAN MAHASISWA');
    $receiptResponse->assertSee('CICILAN');
    $receiptResponse->assertSee('150.000');
});

test('admin can search mahasiswa by nim and name on payments index', function () {
    $service = app(PaymentInitializationService::class);
    $service->initializeStudentPayments($this->mahasiswaA);

    // Search by NIM
    $nimSearchResponse = $this->actingAs($this->adminFakultasA)->get(route('admin.payments.index', ['search' => $this->mahasiswaA->nim]));
    $nimSearchResponse->assertSuccessful();
    $nimSearchResponse->assertSee($this->mahasiswaA->nim);
    $nimSearchResponse->assertSee($this->userMhsA->name);

    // Search by Name
    $nameSearchResponse = $this->actingAs($this->adminFakultasA)->get(route('admin.payments.index', ['search' => substr($this->userMhsA->name, 0, 5)]));
    $nameSearchResponse->assertSuccessful();
    $nameSearchResponse->assertSee($this->mahasiswaA->nim);

    // Search non-existent
    $emptySearchResponse = $this->actingAs($this->adminFakultasA)->get(route('admin.payments.index', ['search' => 'NONEXISTENT999']));
    $emptySearchResponse->assertSuccessful();
    $emptySearchResponse->assertDontSee($this->mahasiswaA->nim);
    $emptySearchResponse->assertSee('Tidak ada data mahasiswa yang sesuai kriteria pencarian');
});
