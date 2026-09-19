<?php

use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\PaymentInitializationService;
use App\Services\PaymentService;
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

    $this->adminFakultas = User::factory()->create([
        'role' => 'admin_fakultas',
        'fakultas_id' => $this->fakultas->id,
    ]);
    $this->adminFakultas->assignRole('admin_fakultas');

    $this->user = User::factory()->create(['role' => 'mahasiswa']);
    $this->mahasiswa = Mahasiswa::create([
        'user_id' => $this->user->id,
        'nim' => '202401099',
        'prodi_id' => $this->prodi->id,
        'angkatan' => 2024,
        'status' => 'aktif',
    ]);

    $this->userDosen = User::factory()->create(['role' => 'dosen']);
    $this->dosen = Dosen::create([
        'user_id' => $this->userDosen->id,
        'nidn' => '0987654321',
        'prodi_id' => $this->prodi->id,
    ]);

    $this->mataKuliah = MataKuliah::create([
        'prodi_id' => $this->prodi->id,
        'kode_mk' => 'TAR101',
        'nama_mk' => 'Pengantar Ilmu Tarbiyah',
        'sks' => 3,
        'semester' => 1,
    ]);

    $this->kelas = Kelas::create([
        'mata_kuliah_id' => $this->mataKuliah->id,
        'dosen_id' => $this->dosen->id,
        'tahun_akademik_id' => $this->tahunAktif->id,
        'nama_kelas' => 'PAI-1A',
        'kapasitas' => 30,
    ]);

    // Initialize payments for this student
    app(PaymentInitializationService::class)->initializeStudentPayments($this->mahasiswa);
});

test('mahasiswa with unpaid semester payment sees locked state on KRS page', function () {
    $response = $this->actingAs($this->user)->get(route('mahasiswa.krs.index'));

    $response->assertSuccessful();
    $response->assertSee('KRS Belum Dapat Diakses');
    $response->assertSee('Akses Akademik Terkunci');
    $response->assertSee('Lihat Rincian Pembayaran');
    $response->assertDontSee('+ Ambil Kelas');
});

test('mahasiswa cannot add kelas when payment is unpaid', function () {
    $response = $this->actingAs($this->user)->post(route('mahasiswa.krs.store'), [
        'kelas_id' => $this->kelas->id,
    ]);

    $response->assertSessionHas('error');
    $errorMessage = session('error');
    expect($errorMessage)->toContain('Pembayaran');
});

test('once payment is confirmed by admin, KRS unlocks and student can add kelas', function () {
    $paymentService = app(PaymentService::class);

    // Confirm Registration
    $regPayment = $this->mahasiswa->payments()->whereHas('paymentType', fn ($q) => $q->where('code', 'registration'))->first();
    $paymentService->confirmPayment($regPayment, [
        'payment_date' => '2024-09-01',
        'payment_method' => 'Transfer',
        'paid_amount' => $regPayment->amount,
    ], $this->adminFakultas);

    // Confirm Semester 1
    $sem1Payment = $this->mahasiswa->payments()->whereHas('paymentType', fn ($q) => $q->where('code', 'semester_1'))->first();
    $paymentService->confirmPayment($sem1Payment, [
        'payment_date' => '2024-09-01',
        'payment_method' => 'Transfer',
        'paid_amount' => $sem1Payment->amount,
    ], $this->adminFakultas);

    // Mahasiswa accesses KRS page -> Unlocked!
    $response = $this->actingAs($this->user)->get(route('mahasiswa.krs.index'));
    $response->assertSuccessful();
    $response->assertDontSee('KRS Belum Dapat Diakses');
    $response->assertSee('Kartu Rencana Studi');
    $response->assertSee('Pengantar Ilmu Tarbiyah');

    // Mahasiswa adds kelas -> SUCCESS!
    $postResponse = $this->actingAs($this->user)->post(route('mahasiswa.krs.store'), [
        'kelas_id' => $this->kelas->id,
    ]);

    $postResponse->assertSessionHas('success', 'Kelas berhasil diambil');
});

test('paid semester 1 does not unlock semester 2 when student advances to semester 2', function () {
    $paymentService = app(PaymentService::class);

    // Confirm Registration and Semester 1
    $regPayment = $this->mahasiswa->payments()->whereHas('paymentType', fn ($q) => $q->where('code', 'registration'))->first();
    $paymentService->confirmPayment($regPayment, ['paid_amount' => $regPayment->amount], $this->adminFakultas);

    $sem1Payment = $this->mahasiswa->payments()->whereHas('paymentType', fn ($q) => $q->where('code', 'semester_1'))->first();
    $paymentService->confirmPayment($sem1Payment, ['paid_amount' => $sem1Payment->amount], $this->adminFakultas);

    // Now advance academic year to Genap (Semester 2 for angkatan 2024)
    $this->tahunAktif->update([
        'semester' => 'Genap',
    ]);

    // Student visits KRS in semester 2 -> Locked because semester 2 is unpaid!
    $response = $this->actingAs($this->user)->get(route('mahasiswa.krs.index'));
    $response->assertSuccessful();
    $response->assertSee('KRS Belum Dapat Diakses');
    $response->assertSee('Semester 2');
});
