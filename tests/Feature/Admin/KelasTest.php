<?php

namespace Tests\Feature\Admin;

use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'superadmin',
    ]);
});

test('admin can create kelas with jadwal and ruangan', function () {
    $fakultas = Fakultas::create(['nama' => 'Tarbiyah', 'kode' => 'TAR']);
    $prodi = Prodi::create(['nama' => 'Pendidikan Agama Islam', 'kode' => 'PAI', 'jenjang' => 'S1', 'fakultas_id' => $fakultas->id]);
    $tahunAkademik = TahunAkademik::create(['tahun' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
    $userDosen = User::factory()->create(['role' => 'dosen']);
    $dosen = Dosen::create(['user_id' => $userDosen->id, 'nidn' => '1234567890', 'prodi_id' => $prodi->id]);
    $mataKuliah = MataKuliah::create(['kode_mk' => 'MK001', 'nama_mk' => 'Fiqih', 'sks' => 3, 'semester' => 1, 'prodi_id' => $prodi->id]);

    $response = $this->actingAs($this->admin)->post(route('admin.kelas.store'), [
        'mata_kuliah_id' => $mataKuliah->id,
        'dosen_id' => $dosen->id,
        'nama_kelas' => 'PAI 1A',
        'kapasitas' => 30,
        'hari' => 'Sabtu',
        'jam_mulai' => '12:00',
        'jam_selesai' => '13:00',
        'ruangan' => '7y',
    ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('kelas', [
        'nama_kelas' => 'PAI 1A',
    ]);

    $this->assertDatabaseHas('jadwal_kuliah', [
        'hari' => 'Sabtu',
        'ruangan' => '7y',
    ]);
});

test('admin can update kelas and its jadwal ruangan', function () {
    $fakultas = Fakultas::create(['nama' => 'Tarbiyah', 'kode' => 'TAR']);
    $prodi = Prodi::create(['nama' => 'Pendidikan Agama Islam', 'kode' => 'PAI', 'jenjang' => 'S1', 'fakultas_id' => $fakultas->id]);
    $tahunAkademik = TahunAkademik::create(['tahun' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
    $userDosen = User::factory()->create(['role' => 'dosen']);
    $dosen = Dosen::create(['user_id' => $userDosen->id, 'nidn' => '1234567890', 'prodi_id' => $prodi->id]);
    $mataKuliah = MataKuliah::create(['kode_mk' => 'MK001', 'nama_mk' => 'Fiqih', 'sks' => 3, 'semester' => 1, 'prodi_id' => $prodi->id]);

    $kelas = Kelas::create([
        'mata_kuliah_id' => $mataKuliah->id,
        'dosen_id' => $dosen->id,
        'nama_kelas' => 'PAI 1A',
        'kapasitas' => 30,
        'tahun_akademik_id' => $tahunAkademik->id,
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.kelas.update', $kelas), [
        'mata_kuliah_id' => $mataKuliah->id,
        'dosen_id' => $dosen->id,
        'nama_kelas' => 'PAI 1A Updated',
        'kapasitas' => 35,
        'hari' => 'Senin',
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:00',
        'ruangan' => 'Lab Komputer',
    ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('jadwal_kuliah', [
        'kelas_id' => $kelas->id,
        'hari' => 'Senin',
        'ruangan' => 'Lab Komputer',
    ]);
});
