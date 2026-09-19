<?php

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Models\User;

beforeEach(function () {
    TahunAkademik::factory()->create(['is_active' => true]);
    $this->user = User::factory()->create(['role' => 'mahasiswa']);
    $this->prodi = Prodi::factory()->create();
    $this->mahasiswa = Mahasiswa::factory()->create([
        'user_id' => $this->user->id,
        'prodi_id' => $this->prodi->id,
    ]);
});

test('mahasiswa can view their dashboard', function () {
    $response = $this->actingAs($this->user)->get(route('mahasiswa.dashboard'));

    $response->assertStatus(200);
});

test('mahasiswa can view jadwal', function () {
    $response = $this->actingAs($this->user)->get(route('mahasiswa.jadwal.index'));

    $response->assertStatus(200);
});

test('mahasiswa can view presensi', function () {
    $response = $this->actingAs($this->user)->get(route('mahasiswa.presensi.index'));

    $response->assertStatus(200);
});

test('mahasiswa can view transkrip', function () {
    $response = $this->actingAs($this->user)->get(route('mahasiswa.transkrip.index'));

    $response->assertStatus(200);
});

test('mahasiswa can view khs', function () {
    $response = $this->actingAs($this->user)->get(route('mahasiswa.khs.index'));

    $response->assertStatus(200);
});

test('mahasiswa can view biodata', function () {
    $response = $this->actingAs($this->user)->get(route('mahasiswa.biodata.index'));

    $response->assertStatus(200);
});

test('mahasiswa can view skripsi page', function () {
    $response = $this->actingAs($this->user)->get(route('mahasiswa.skripsi.index'));

    $response->assertStatus(200);
});

test('mahasiswa can view kp page', function () {
    $response = $this->actingAs($this->user)->get(route('mahasiswa.kp.index'));

    $response->assertStatus(200);
});

test('mahasiswa can export jadwal', function () {
    $response = $this->actingAs($this->user)->get(route('mahasiswa.export.jadwal'));

    $response->assertStatus(200);
    $response->assertSee('SEKOLAH TINGGI ILMU TARBIYAH (STIT) MAMBAUL HIKMAH');
    $response->assertSee('Jadwal Perkuliahan Mahasiswa');
});

test('mahasiswa schedule view contains all days Senin to Ahad and print button', function () {
    $response = $this->actingAs($this->user)->get(route('mahasiswa.jadwal.index'));

    $response->assertStatus(200);
    $response->assertSee('Senin');
    $response->assertSee('Selasa');
    $response->assertSee('Rabu');
    $response->assertSee('Kamis');
    $response->assertSee('Jumat');
    $response->assertSee('Sabtu');
    $response->assertSee('Ahad');
    $response->assertSee('Cetak Jadwal');
    $response->assertSee('Nama Mahasiswa');
    $response->assertSee('Program Studi');
    $response->assertSee('Dosen Pembimbing');
    $response->assertSee('Total SKS Terjadwal');
});

test('mahasiswa schedule view displays student details and PA dosen', function () {
    $dosenUser = User::factory()->create(['name' => 'Dr. Ahmad Fauzi, M.Kom.']);
    $dosen = Dosen::factory()->create(['user_id' => $dosenUser->id]);
    $this->mahasiswa->update(['dosen_pa_id' => $dosen->id]);

    $response = $this->actingAs($this->user)->get(route('mahasiswa.jadwal.index'));

    $response->assertStatus(200);
    $response->assertSee($this->user->name);
    $response->assertSee($this->mahasiswa->nim);
    $response->assertSee('Dr. Ahmad Fauzi, M.Kom.');
    $response->assertSee($this->prodi->nama);
});
