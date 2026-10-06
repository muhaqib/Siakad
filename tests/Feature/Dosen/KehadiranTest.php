<?php

use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\JadwalKuliah;
use App\Models\KehadiranDosen;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-10-03 11:00:00')); // Hari Sabtu

    $fakultas = Fakultas::factory()->create();
    $prodi = Prodi::factory()->create(['fakultas_id' => $fakultas->id]);

    $this->user = User::factory()->create(['role' => 'dosen']);
    $this->dosen = Dosen::create([
        'user_id' => $this->user->id,
        'nidn' => '1234567890',
        'prodi_id' => $prodi->id,
    ]);

    $tahunAkademik = TahunAkademik::create(['tahun' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
    $mataKuliah = MataKuliah::create(['kode_mk' => 'MK001', 'nama_mk' => 'Fiqih Ibadah', 'sks' => 2, 'semester' => 1, 'prodi_id' => $prodi->id]);

    $this->kelas = Kelas::create([
        'mata_kuliah_id' => $mataKuliah->id,
        'dosen_id' => $this->dosen->id,
        'nama_kelas' => 'A',
        'kapasitas' => 30,
        'tahun_akademik_id' => $tahunAkademik->id,
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

it('shows attendance widget and today schedule on dashboard', function () {
    JadwalKuliah::create([
        'kelas_id' => $this->kelas->id,
        'hari' => 'Sabtu',
        'jam_mulai' => '12:00',
        'jam_selesai' => '13:00',
        'ruangan' => 'R-101',
    ]);

    $response = $this->actingAs($this->user)->get(route('dosen.dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Absensi Hari Ini');
    $response->assertSee('Absen Masuk');
    $response->assertSee('Ada 1 kelas yang harus Anda ajar hari ini.');
});

it('allows dosen to clock in as hadir and then view classes and clock out button', function () {
    $jadwal = JadwalKuliah::create([
        'kelas_id' => $this->kelas->id,
        'hari' => 'Sabtu',
        'jam_mulai' => '12:00',
        'jam_selesai' => '13:00',
        'ruangan' => 'R-101',
    ]);

    $response = $this->actingAs($this->user)->post(route('dosen.kehadiran.store'), [
        'status' => KehadiranDosen::STATUS_HADIR,
    ]);

    $response->assertRedirect(route('dosen.dashboard'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('kehadiran_dosen', [
        'dosen_id' => $this->dosen->id,
        'status' => KehadiranDosen::STATUS_HADIR,
        'jadwal_kuliah_id' => null,
    ]);

    $dashboard = $this->actingAs($this->user)->get(route('dosen.dashboard'));
    $dashboard->assertSuccessful();
    $dashboard->assertSee('Fiqih Ibadah');
    $dashboard->assertSee('Absen Keluar');
});

it('requires keterangan when clocking in as sakit or izin', function () {
    $response = $this->actingAs($this->user)->post(route('dosen.kehadiran.store'), [
        'status' => KehadiranDosen::STATUS_SAKIT,
        'keterangan' => '',
    ]);

    $response->assertSessionHasErrors('keterangan');

    $this->assertDatabaseMissing('kehadiran_dosen', [
        'dosen_id' => $this->dosen->id,
    ]);
});

it('records sakit with keterangan and hides class presensi requirement', function () {
    $response = $this->actingAs($this->user)->post(route('dosen.kehadiran.store'), [
        'status' => KehadiranDosen::STATUS_SAKIT,
        'keterangan' => 'Demam tinggi dan flu',
    ]);

    $response->assertRedirect(route('dosen.dashboard'));

    $this->assertDatabaseHas('kehadiran_dosen', [
        'dosen_id' => $this->dosen->id,
        'status' => KehadiranDosen::STATUS_SAKIT,
        'keterangan' => 'Demam tinggi dan flu',
    ]);

    $dashboard = $this->actingAs($this->user)->get(route('dosen.dashboard'));
    $dashboard->assertSee('Sakit');
    $dashboard->assertSee('Demam tinggi dan flu');
    $dashboard->assertDontSee('Absen Keluar');
});

it('allows dosen to clock out after clocking in as hadir', function () {
    $kehadiran = KehadiranDosen::create([
        'dosen_id' => $this->dosen->id,
        'status' => KehadiranDosen::STATUS_HADIR,
        'tanggal' => now()->toDateString(),
        'jam_masuk' => '11:00:00',
    ]);

    $response = $this->actingAs($this->user)->post(route('dosen.kehadiran.checkout', $kehadiran));

    $response->assertRedirect(route('dosen.dashboard'));
    $response->assertSessionHas('success');

    $kehadiran->refresh();
    expect($kehadiran->jam_keluar)->not->toBeNull();
});

it('prevents duplicate clock in on the same day', function () {
    KehadiranDosen::create([
        'dosen_id' => $this->dosen->id,
        'status' => KehadiranDosen::STATUS_HADIR,
        'tanggal' => now()->toDateString(),
        'jam_masuk' => '11:00:00',
    ]);

    $response = $this->actingAs($this->user)->post(route('dosen.kehadiran.store'), [
        'status' => KehadiranDosen::STATUS_HADIR,
    ]);

    $response->assertSessionHasErrors('status');
});

it('renders dosen kehadiran index page without error', function () {
    JadwalKuliah::create([
        'kelas_id' => $this->kelas->id,
        'hari' => 'Sabtu',
        'jam_mulai' => '12:00',
        'jam_selesai' => '13:00',
        'ruangan' => 'R-101',
    ]);

    $response = $this->actingAs($this->user)->get(route('dosen.kehadiran.index'));

    $response->assertSuccessful();
    $response->assertSee('Kehadiran Dosen');
    $response->assertSee('Ringkasan Bulan Ini');
    $response->assertSee('Riwayat Kehadiran');
});

it('filters kehadiran history on index page', function () {
    KehadiranDosen::create([
        'dosen_id' => $this->dosen->id,
        'status' => KehadiranDosen::STATUS_HADIR,
        'tanggal' => now()->toDateString(),
        'jam_masuk' => '08:00:00',
        'keterangan' => 'Mengajar Fiqih',
    ]);

    $response = $this->actingAs($this->user)->get(route('dosen.kehadiran.index', [
        'month' => now()->month,
        'year' => now()->year,
        'search' => 'Fiqih',
    ]));

    $response->assertSuccessful();
    $response->assertSee('Mengajar Fiqih');
});
