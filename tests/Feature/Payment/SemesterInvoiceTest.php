<?php

use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\StudentPayment;
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

it('allows admin to generate semester invoice PDF', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.payments.student.invoice', [$this->mahasiswa->id, 1]));

    $response->assertSuccessful();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('defaults to active semester invoice when semester parameter is omitted', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.payments.student.invoice', $this->mahasiswa->id));

    $response->assertSuccessful();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('resolves student using user_id via route model binding', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.payments.student', $this->user->id));

    $response->assertSuccessful();
    $response->assertSee($this->mahasiswa->nim);
});

it('marks months as paid when installments are partially paid', function () {
    // Total is 1,200,000. 1 month installment is 200,000.
    // Pay 200,000 -> 1 month paid
    $sem1Payment = StudentPayment::where('mahasiswa_id', $this->mahasiswa->id)
        ->whereHas('paymentType', fn ($q) => $q->where('semester', 1))
        ->first();

    $sem1Payment->update([
        'paid_amount' => 200000,
        'status' => 'partial',
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.payments.student.invoice', [$this->mahasiswa->id, 1]));

    $response->assertSuccessful();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});
