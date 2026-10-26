<?php

use App\Models\Dosen;
use App\Models\JadwalKuliah;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Pertemuan;
use App\Models\TahunAkademik;
use App\Models\User;

beforeEach(function () {
    $this->dosen = Dosen::factory()->create();
    $mataKuliah = MataKuliah::create(['kode_mk' => 'MK001', 'nama_mk' => 'Fiqih', 'sks' => 2, 'semester' => 1, 'prodi_id' => $this->dosen->prodi_id]);
    $this->kelas = Kelas::create([
        'mata_kuliah_id' => $mataKuliah->id,
        'dosen_id' => $this->dosen->id,
        'tahun_akademik_id' => TahunAkademik::factory()->create()->id,
        'nama_kelas' => 'A',
        'kapasitas' => 30,
    ]);
    $this->jadwal = JadwalKuliah::create(['kelas_id' => $this->kelas->id, 'hari' => 'Senin', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']);
    $this->pertemuan = Pertemuan::create(['jadwal_kuliah_id' => $this->jadwal->id, 'pertemuan_ke' => 1, 'tanggal' => '2026-10-26', 'materi' => 'Pengantar', 'status' => 'terlaksana']);
    $this->payload = ['jadwal_kuliah_id' => $this->jadwal->id, 'pertemuan_ke' => 2, 'tanggal' => '2026-10-27', 'materi' => 'Pembahasan'];
});

it('allows the lecturer to delete a meeting and its attendance', function () {
    $presensi = $this->pertemuan->presensi()->create(['mahasiswa_id' => Mahasiswa::factory()->create()->id, 'status' => 'hadir']);

    $this->actingAs($this->dosen->user)->delete(route('dosen.presensi.pertemuan.destroy', $this->pertemuan))
        ->assertRedirect(route('dosen.presensi.kelas', $this->kelas))->assertSessionHas('success');

    $this->assertModelMissing($this->pertemuan);
    $this->assertModelMissing($presensi);
});

it('forbids deleting another lecturers meeting', function () {
    $this->actingAs(Dosen::factory()->create()->user)->delete(route('dosen.presensi.pertemuan.destroy', $this->pertemuan))->assertForbidden();
    $this->assertModelExists($this->pertemuan);
});

it('forbids a lecturer without a profile from deleting meetings', function () {
    $this->actingAs(User::factory()->create(['role' => 'dosen']))->delete(route('dosen.presensi.pertemuan.destroy', $this->pertemuan))->assertForbidden();
    $this->assertModelExists($this->pertemuan);
});

it('requires authentication to delete meetings', function () {
    $this->delete(route('dosen.presensi.pertemuan.destroy', $this->pertemuan))->assertRedirect(route('login'));
    $this->assertModelExists($this->pertemuan);
});

it('rejects invalid meeting material', function ($materi) {
    $this->actingAs($this->dosen->user)->post(route('dosen.presensi.pertemuan.store', $this->kelas), [...$this->payload, 'materi' => $materi])
        ->assertSessionHasErrors('materi');
    $this->assertDatabaseCount('pertemuan', 1);
})->with([null, '', '   ', str_repeat('a', 256)]);

it('creates a meeting with material', function () {
    $this->actingAs($this->dosen->user)->post(route('dosen.presensi.pertemuan.store', $this->kelas), $this->payload)->assertSessionHasNoErrors()->assertRedirect();
    $this->assertDatabaseHas('pertemuan', [...$this->payload, 'tanggal' => '2026-10-27 00:00:00']);
});

it('forbids creating a meeting in another lecturers class', function () {
    $this->actingAs(Dosen::factory()->create()->user)->post(route('dosen.presensi.pertemuan.store', $this->kelas), $this->payload)->assertForbidden();
});

it('shows the delete action and required material field', function () {
    $this->withoutVite();
    $this->actingAs($this->dosen->user)->get(route('dosen.presensi.kelas', $this->kelas))->assertSuccessful()
        ->assertSee(route('dosen.presensi.pertemuan.destroy', $this->pertemuan), false)->assertSee('aria-label="Hapus pertemuan 1"', false)
        ->assertSee('title="Hapus pertemuan"', false)->assertDontSee('>Hapus</button>', false);
    $this->get(route('dosen.presensi.pertemuan.create', $this->kelas))->assertSuccessful()
        ->assertSee('Materi (Wajib)')->assertSee('id="materi" required', false)->assertDontSee('Materi (Opsional)');
});
