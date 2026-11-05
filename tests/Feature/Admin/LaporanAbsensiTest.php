<?php

use App\Models\Dosen;
use App\Models\JadwalKuliah;
use App\Models\KehadiranDosen;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Pertemuan;
use App\Models\TahunAkademik;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

it('requires authentication for attendance reports', function () {
    $this->get(route('admin.laporan-absensi.index'))->assertRedirect(route('login'));
});

it('restricts attendance reports to superadmins', function (string $role) {
    $this->actingAs(User::factory()->create(['role' => $role]))
        ->get(route('admin.laporan-absensi.index'))->assertForbidden();
})->with(['admin_fakultas', 'dosen', 'mahasiswa']);

it('shows an empty printable report for the current month', function () {
    $this->actingAs(User::factory()->create(['role' => 'superadmin']))
        ->get(route('admin.laporan-absensi.index'))->assertSuccessful()
        ->assertViewHas('periode', now()->format('Y-m'))
        ->assertSee('Laporan Absensi')->assertSee('Cetak / Simpan PDF')
        ->assertSee('Belum ada absensi dosen')->assertSee('ABSENSI DOSEN STIT MAMBAUL HIKMAH')
        ->assertDontSee('ABSENSI MAHASISWA STIT MAMBAUL HIKMAH');
});

it('rejects malformed report periods', function (mixed $periode) {
    $this->actingAs(User::factory()->create(['role' => 'superadmin']))
        ->getJson(route('admin.laporan-absensi.index', ['periode' => $periode]))
        ->assertUnprocessable()->assertJsonValidationErrors('periode');
})->with(['2026-13', '2026-00', 'invalid', '2026-1', '', [['2026-10']]]);

it('includes all faculties and month boundaries using the meeting date for students', function (string $jenis) {
    $dosen = Dosen::factory()->create();
    $otherDosen = Dosen::factory()->create();
    $mataKuliah = MataKuliah::create(['kode_mk' => 'LAP001', 'nama_mk' => 'Fiqih Laporan', 'sks' => 2, 'semester' => 1, 'prodi_id' => $dosen->prodi_id]);
    $kelas = Kelas::create(['mata_kuliah_id' => $mataKuliah->id, 'dosen_id' => $dosen->id, 'tahun_akademik_id' => TahunAkademik::factory()->create()->id, 'nama_kelas' => 'A', 'kapasitas' => 30]);
    $jadwal = JadwalKuliah::create(['kelas_id' => $kelas->id, 'hari' => 'Senin', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']);
    $mahasiswa = Mahasiswa::factory()->create();

    foreach (['2026-09-30', '2026-10-01', '2026-10-31', '2026-11-01'] as $index => $tanggal) {
        KehadiranDosen::create(['dosen_id' => $dosen->id, 'tanggal' => $tanggal, 'status' => 'hadir', 'keterangan' => 'Dosen '.$tanggal]);
        $pertemuan = Pertemuan::create(['jadwal_kuliah_id' => $jadwal->id, 'pertemuan_ke' => $index + 1, 'tanggal' => $tanggal, 'status' => 'terlaksana']);
        $pertemuan->presensi()->create(['mahasiswa_id' => $mahasiswa->id, 'status' => 'izin', 'waktu_presensi' => '2026-11-05 08:00:00', 'keterangan' => 'Mahasiswa '.$tanggal]);
    }
    KehadiranDosen::create(['dosen_id' => $otherDosen->id, 'tanggal' => '2026-10-15', 'status' => 'sakit']);

    $response = $this->actingAs(User::factory()->create(['role' => 'superadmin']))
        ->get(route('admin.laporan-absensi.index', ['periode' => '2026-10', 'jenis' => $jenis]))
        ->assertSuccessful()->assertViewHas('records', fn ($records) => $records->count() === ($jenis === 'dosen' ? 3 : 2));

    if ($jenis === 'dosen') {
        $response->assertSee($otherDosen->user->name)->assertDontSee($mahasiswa->user->name)
            ->assertViewHas('rows', fn ($rows) => $rows->count() === 2 && $rows->firstWhere('identity', $dosen->nidn)['days'] === [1 => 'H', 31 => 'H']);
    } else {
        $response->assertSee($mahasiswa->user->name)->assertDontSee($otherDosen->user->name)
            ->assertViewHas('rows', fn ($rows) => $rows->count() === 1 && $rows->first()['days'] === [1 => 'I', 31 => 'I']);
    }
})->with(['dosen', 'mahasiswa']);

it('opens the latest available lecturer month without overriding an explicit period', function () {
    $dosen = Dosen::factory()->create();
    KehadiranDosen::create(['dosen_id' => $dosen->id, 'tanggal' => '2025-09-30', 'status' => 'hadir']);
    KehadiranDosen::create(['dosen_id' => $dosen->id, 'tanggal' => '2025-10-01', 'status' => 'sakit']);

    $this->actingAs(User::factory()->create(['role' => 'superadmin']))
        ->get(route('admin.laporan-absensi.index'))->assertSuccessful()
        ->assertViewHas('periode', '2025-10')->assertViewHas('records', fn ($records) => $records->count() === 1);
    $this->get(route('admin.laporan-absensi.index', ['periode' => '2025-11']))
        ->assertSuccessful()->assertViewHas('periode', '2025-11')
        ->assertViewHas('records', fn ($records) => $records->isEmpty())
        ->assertSee('Lihat bulan terakhir');
});

it('opens the latest student month with attendance rather than an empty newer meeting', function () {
    $dosen = Dosen::factory()->create();
    $mataKuliah = MataKuliah::create(['kode_mk' => 'LATEST', 'nama_mk' => 'Fiqih', 'sks' => 2, 'semester' => 1, 'prodi_id' => $dosen->prodi_id]);
    $kelas = Kelas::create(['mata_kuliah_id' => $mataKuliah->id, 'dosen_id' => $dosen->id, 'tahun_akademik_id' => TahunAkademik::factory()->create()->id, 'nama_kelas' => 'A', 'kapasitas' => 30]);
    $jadwal = JadwalKuliah::create(['kelas_id' => $kelas->id, 'hari' => 'Senin', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']);
    $pertemuan = Pertemuan::create(['jadwal_kuliah_id' => $jadwal->id, 'pertemuan_ke' => 1, 'tanggal' => '2025-08-31', 'status' => 'terlaksana']);
    $pertemuan->presensi()->create(['mahasiswa_id' => Mahasiswa::factory()->create()->id, 'status' => 'hadir', 'waktu_presensi' => '08:15:00']);
    Pertemuan::create(['jadwal_kuliah_id' => $jadwal->id, 'pertemuan_ke' => 2, 'tanggal' => '2025-09-01', 'status' => 'terlaksana']);
    KehadiranDosen::create(['dosen_id' => $dosen->id, 'tanggal' => '2025-10-01', 'status' => 'hadir']);

    $this->actingAs(User::factory()->create(['role' => 'superadmin']))
        ->get(route('admin.laporan-absensi.index', ['jenis' => 'mahasiswa']))->assertSuccessful()
        ->assertViewHas('periode', '2025-08')->assertViewHas('records', fn ($records) => $records->count() === 1)
        ->assertSee('ABSENSI MAHASISWA STIT MAMBAUL HIKMAH')
        ->assertViewHas('rows', fn ($rows) => $rows->first()['days'] === [31 => 'H'])
        ->assertDontSee('ABSENSI DOSEN STIT MAMBAUL HIKMAH');
});

it('rejects invalid report types', function (mixed $jenis) {
    $this->actingAs(User::factory()->create(['role' => 'superadmin']))
        ->getJson(route('admin.laporan-absensi.index', ['jenis' => $jenis]))
        ->assertUnprocessable()->assertJsonValidationErrors('jenis');
})->with(['invalid', '', [['dosen']]]);

it('shows exactly the days in the selected month in the printable grid', function (string $periode, int $days) {
    $response = $this->actingAs(User::factory()->create(['role' => 'superadmin']))
        ->get(route('admin.laporan-absensi.index', ['periode' => $periode]))->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
    $xpath = new DOMXPath($document);
    $headings = $xpath->query('//table[contains(@class,"attendance-grid")]/thead/tr[2]/th');
    expect($headings->length)->toBe($days + 3)
        ->and($headings->item($days - 1)->textContent)->toBe((string) $days)
        ->and($headings->item($days)->textContent)->toBe('S');
})->with([['2026-02', 28], ['2024-02', 29], ['2026-04', 30], ['2026-10', 31]]);

it('only exposes the month filter for lecturers and ignores student filter parameters', function () {
    $this->actingAs(User::factory()->create(['role' => 'superadmin']))
        ->get(route('admin.laporan-absensi.index', ['jenis' => 'dosen', 'semester' => 'invalid', 'prodi_id' => 'invalid']))
        ->assertSuccessful()->assertSee('name="periode"', false)
        ->assertDontSee('name="semester"', false)->assertDontSee('name="prodi_id"', false)
        ->assertViewHas('semester', null)->assertViewHas('prodiId', null);
});

describe('student report filters and daily totals', function () {
    beforeEach(function () {
        $this->dosen = Dosen::factory()->create();
        $this->mahasiswa = Mahasiswa::factory()->create(['prodi_id' => $this->dosen->prodi_id]);
        $this->otherMahasiswa = Mahasiswa::factory()->create();
        $this->tahun = TahunAkademik::factory()->create(['tahun' => '2026/2027', 'semester' => 'Ganjil']);
        $this->meetings = collect();
        foreach ([1, 3] as $semester) {
            $mataKuliah = MataKuliah::create(['kode_mk' => 'SEM'.$semester, 'nama_mk' => 'Fiqih '.$semester, 'sks' => 2, 'semester' => $semester, 'prodi_id' => $this->dosen->prodi_id]);
            $kelas = Kelas::create(['mata_kuliah_id' => $mataKuliah->id, 'dosen_id' => $this->dosen->id, 'tahun_akademik_id' => $this->tahun->id, 'nama_kelas' => 'A', 'kapasitas' => 30]);
            $jadwal = JadwalKuliah::create(['kelas_id' => $kelas->id, 'hari' => 'Senin', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']);
            $pertemuan = Pertemuan::create(['jadwal_kuliah_id' => $jadwal->id, 'pertemuan_ke' => 1, 'tanggal' => '2026-10-05', 'status' => 'terlaksana']);
            $pertemuan->presensi()->create(['mahasiswa_id' => $this->mahasiswa->id, 'status' => $semester === 1 ? 'sakit' : 'izin']);
            $pertemuan->presensi()->create(['mahasiswa_id' => $this->otherMahasiswa->id, 'status' => 'alpa']);
            $this->meetings->put($semester, $pertemuan);
        }
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
    });

    it('filters by semester and student program together', function () {
        $this->get(route('admin.laporan-absensi.index', ['jenis' => 'mahasiswa', 'periode' => '2026-10', 'semester' => 3, 'prodi_id' => $this->mahasiswa->prodi_id]))
            ->assertSuccessful()->assertViewHas('records', fn ($records) => $records->count() === 1)
            ->assertViewHas('rows', fn ($rows) => $rows->first()['days'] === [5 => 'I'] && $rows->first()['counts'] === ['izin' => 1])
            ->assertSee($this->mahasiswa->user->name)->assertDontSee($this->otherMahasiswa->user->name)
            ->assertSee($this->mahasiswa->prodi->nama)->assertSee('Tahun Ajaran 2026/2027 Ganjil')
            ->assertSee('name="semester"', false)->assertSee('name="prodi_id"', false);
    });

    it('filters by semester alone', function () {
        $this->get(route('admin.laporan-absensi.index', ['jenis' => 'mahasiswa', 'periode' => '2026-10', 'semester' => 1]))
            ->assertSuccessful()->assertViewHas('records', fn ($records) => $records->count() === 2)
            ->assertViewHas('rows', fn ($rows) => $rows->count() === 2);
    });

    it('keeps multiple statuses on the same day and counts every recorded absence', function () {
        $this->get(route('admin.laporan-absensi.index', ['jenis' => 'mahasiswa', 'periode' => '2026-10', 'prodi_id' => $this->mahasiswa->prodi_id]))
            ->assertSuccessful()->assertViewHas('rows', function ($rows) {
                expect($rows)->toHaveCount(1)
                    ->and($rows->first()['days'])->toBe([5 => 'I/S'])
                    ->and($rows->first()['counts'])->toBe(['sakit' => 1, 'izin' => 1]);

                return true;
            });
    });

    it('chooses the latest month matching the student filters', function () {
        $this->meetings[3]->update(['tanggal' => '2026-09-05']);
        $this->get(route('admin.laporan-absensi.index', ['jenis' => 'mahasiswa', 'semester' => 3, 'prodi_id' => $this->mahasiswa->prodi_id]))
            ->assertSuccessful()->assertViewHas('periode', '2026-09')
            ->assertViewHas('records', fn ($records) => $records->count() === 1);
    });

    it('accepts empty student filters as all programs and semesters', function () {
        $this->get(route('admin.laporan-absensi.index', ['jenis' => 'mahasiswa', 'periode' => '2026-10', 'semester' => '', 'prodi_id' => '']))
            ->assertSuccessful()->assertViewHas('records', fn ($records) => $records->count() === 4);
    });

    it('rejects invalid student filters', function (array $filters, string $field) {
        $this->getJson(route('admin.laporan-absensi.index', ['jenis' => 'mahasiswa', ...$filters]))
            ->assertUnprocessable()->assertJsonValidationErrors($field);
    })->with([
        [['semester' => 0], 'semester'],
        [['semester' => 15], 'semester'],
        [['semester' => 'invalid'], 'semester'],
        [['semester' => ['1']], 'semester'],
        [['prodi_id' => 999999], 'prodi_id'],
        [['prodi_id' => 'invalid'], 'prodi_id'],
        [['prodi_id' => ['1']], 'prodi_id'],
    ]);
});
