<?php

use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\PaymentType;
use App\Models\Prodi;
use App\Models\StudentPayment;
use App\Models\User;
use Database\Seeders\PaymentTypeSeeder;

beforeEach(function () {
    $this->seed(PaymentTypeSeeder::class);

    $this->fakultas = Fakultas::create([
        'kode' => 'FTIK',
        'nama' => 'Fakultas Tarbiyah dan Ilmu Keguruan',
    ]);

    $this->prodi = Prodi::create([
        'kode' => 'PAI',
        'nama' => 'Pendidikan Agama Islam',
        'fakultas_id' => $this->fakultas->id,
    ]);

    $this->superadmin = User::factory()->create([
        'role' => 'superadmin',
        'fakultas_id' => null,
    ]);

    $this->adminFakultas = User::factory()->create([
        'role' => 'admin_fakultas',
        'fakultas_id' => $this->fakultas->id,
    ]);

    $this->mahasiswaUser = User::factory()->create([
        'role' => 'mahasiswa',
        'fakultas_id' => $this->fakultas->id,
    ]);

    $this->mahasiswa = Mahasiswa::create([
        'user_id' => $this->mahasiswaUser->id,
        'nim' => '202601001',
        'prodi_id' => $this->prodi->id,
        'angkatan' => 2026,
        'status' => 'aktif',
    ]);
});

test('superadmin can view payment types index with tambah biaya button', function () {
    $response = $this->actingAs($this->superadmin)->get(route('admin.payment-types.index'));

    $response->assertSuccessful();
    $response->assertSee('Pengaturan Jenis');
    $response->assertSee('Tambah Biaya');
    $response->assertSee('registration');
    $response->assertSee('semester_1');
});

test('non-superadmin cannot access payment types settings', function () {
    // Admin fakultas
    $response = $this->actingAs($this->adminFakultas)->get(route('admin.payment-types.index'));
    $response->assertForbidden();

    // Mahasiswa
    $response = $this->actingAs($this->mahasiswaUser)->get(route('admin.payment-types.index'));
    $response->assertForbidden();
});

test('superadmin can view create payment type page', function () {
    $response = $this->actingAs($this->superadmin)->get(route('admin.payment-types.create'));

    $response->assertSuccessful();
    $response->assertSee('Tambah Jenis');
    $response->assertSee('Kode Biaya');
    $response->assertSee('Simpan Biaya');
});

test('superadmin can create a new payment type successfully', function () {
    $payload = [
        'code' => 'WISUDA',
        'name' => 'Biaya Wisuda & Ijazah',
        'category' => 'other',
        'semester' => null,
        'default_amount' => 750000,
        'is_active' => '1',
        'description' => 'Biaya wisuda kelulusan sarjana.',
    ];

    $response = $this->actingAs($this->superadmin)
        ->post(route('admin.payment-types.store'), $payload);

    $response->assertRedirect(route('admin.payment-types.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('payment_types', [
        'code' => 'WISUDA',
        'name' => 'Biaya Wisuda & Ijazah',
        'category' => 'other',
        'default_amount' => 750000,
        'is_active' => true,
    ]);
});

test('validation prevents duplicate code or negative amount', function () {
    // Duplicate code
    $response = $this->actingAs($this->superadmin)
        ->post(route('admin.payment-types.store'), [
            'code' => 'registration', // already in seeder
            'name' => 'Duplikat Pendaftaran',
            'category' => 'registration',
            'default_amount' => 100000,
            'is_active' => '1',
        ]);

    $response->assertSessionHasErrors(['code']);

    // Negative amount
    $response = $this->actingAs($this->superadmin)
        ->post(route('admin.payment-types.store'), [
            'code' => 'TEST_FEE',
            'name' => 'Test Fee Negatif',
            'category' => 'other',
            'default_amount' => -50000,
            'is_active' => '1',
        ]);

    $response->assertSessionHasErrors(['default_amount']);
});

test('superadmin can edit and update an existing payment type', function () {
    $paymentType = PaymentType::where('code', 'semester_1')->first();

    $response = $this->actingAs($this->superadmin)
        ->get(route('admin.payment-types.edit', $paymentType->id));

    $response->assertSuccessful();
    $response->assertSee($paymentType->name);

    $updateResponse = $this->actingAs($this->superadmin)
        ->put(route('admin.payment-types.update', $paymentType->id), [
            'name' => 'SPP Semester 1 Revisi',
            'category' => 'semester',
            'semester' => 1,
            'default_amount' => 1750000,
            'is_active' => '1',
            'description' => 'Revisi tarif SPP semester 1.',
        ]);

    $updateResponse->assertRedirect(route('admin.payment-types.index'));
    $updateResponse->assertSessionHas('success');

    $this->assertDatabaseHas('payment_types', [
        'id' => $paymentType->id,
        'name' => 'SPP Semester 1 Revisi',
        'default_amount' => 1750000,
    ]);
});

test('superadmin can delete a payment type without associated payments', function () {
    $newType = PaymentType::create([
        'code' => 'TEST_DELETE',
        'name' => 'Biaya Uji Coba',
        'category' => 'other',
        'default_amount' => 50000,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superadmin)
        ->delete(route('admin.payment-types.destroy', $newType->id));

    $response->assertRedirect(route('admin.payment-types.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('payment_types', [
        'id' => $newType->id,
    ]);
});

test('superadmin cannot delete a payment type with associated student payments', function () {
    $paymentType = PaymentType::where('code', 'registration')->first();

    // Create a student payment linking to this payment type
    StudentPayment::create([
        'mahasiswa_id' => $this->mahasiswa->id,
        'payment_type_id' => $paymentType->id,
        'invoice_number' => 'INV/TEST/REG/001',
        'amount' => 350000,
        'paid_amount' => 0,
        'status' => 'unpaid',
    ]);

    $response = $this->actingAs($this->superadmin)
        ->delete(route('admin.payment-types.destroy', $paymentType->id));

    $response->assertRedirect(route('admin.payment-types.index'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('payment_types', [
        'id' => $paymentType->id,
    ]);
});
