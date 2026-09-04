<?php

use App\Models\Kelas;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Models\User;

beforeEach(function () {
    // Create active tahun akademik
    TahunAkademik::factory()->create(['is_active' => true]);
});

test('mahasiswa can view krs page', function () {
    $user = User::factory()->create(['role' => 'mahasiswa']);
    $prodi = Prodi::factory()->create();
    Mahasiswa::factory()->create([
        'user_id' => $user->id,
        'prodi_id' => $prodi->id,
    ]);

    $response = $this->actingAs($user)->get(route('mahasiswa.krs.index'));
    
    $response->assertStatus(200);
});

test('mahasiswa cannot access admin routes', function () {
    $user = User::factory()->create(['role' => 'mahasiswa']);
    $prodi = Prodi::factory()->create();
    Mahasiswa::factory()->create([
        'user_id' => $user->id,
        'prodi_id' => $prodi->id,
    ]);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));
    
    $response->assertStatus(403);
});

test('unauthenticated user is redirected to login', function () {
    $response = $this->get(route('mahasiswa.krs.index'));
    
    $response->assertRedirect(route('login'));
});
