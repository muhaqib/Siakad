<?php

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\User;

test('dosen can view penilaian index page', function () {
    $user = User::factory()->create(['role' => 'dosen']);
    $prodi = Prodi::factory()->create();
    Dosen::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('dosen.penilaian.index'));
    
    $response->assertStatus(200);
});

test('dosen cannot access admin routes', function () {
    $user = User::factory()->create(['role' => 'dosen']);
    Dosen::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));
    
    $response->assertStatus(403);
});

test('mahasiswa cannot access penilaian routes', function () {
    $user = User::factory()->create(['role' => 'mahasiswa']);
    $prodi = Prodi::factory()->create();
    \App\Models\Mahasiswa::factory()->create([
        'user_id' => $user->id,
        'prodi_id' => $prodi->id,
    ]);

    $response = $this->actingAs($user)->get(route('dosen.penilaian.index'));
    
    $response->assertStatus(403);
});
