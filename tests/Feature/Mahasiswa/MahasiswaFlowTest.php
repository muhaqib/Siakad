<?php

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
