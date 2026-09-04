<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Pertemuan;
use App\Models\Presensi;
use App\Models\JadwalKuliah;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PresensiService
{
    /**
     * Create a new pertemuan (meeting session)
     */
    public function createPertemuan(int $jadwalKuliahId, int $pertemuanKe, string $tanggal, ?string $materi = null): Pertemuan
    {
        return Pertemuan::create([
            'jadwal_kuliah_id' => $jadwalKuliahId,
            'pertemuan_ke' => $pertemuanKe,
            'tanggal' => $tanggal,
            'materi' => $materi,
            'status' => Pertemuan::STATUS_TERLAKSANA,
        ]);
    }

    /**
     * Record attendance for a pertemuan
     * 
     * @param int $pertemuanId
     * @param array $presensiData Array of [mahasiswa_id => status, ...]
     */
    public function recordPresensi(int $pertemuanId, array $presensiData): void
    {
        DB::transaction(function () use ($pertemuanId, $presensiData) {
            foreach ($presensiData as $mahasiswaId => $status) {
                Presensi::updateOrCreate(
                    [
                        'pertemuan_id' => $pertemuanId,
                        'mahasiswa_id' => $mahasiswaId,
                    ],
                    [
                        'status' => $status,
                        'waktu_presensi' => $status === Presensi::STATUS_HADIR ? now()->format('H:i:s') : null,
                    ]
                );
            }
        });
    }

    /**
     * Get attendance summary for a mahasiswa in a kelas
     */
    public function getRekapPresensi(int $mahasiswaId, int $kelasId): array
    {
        $pertemuanIds = Pertemuan::byKelas($kelasId)->terlaksana()->pluck('id');
        
        $presensi = Presensi::where('mahasiswa_id', $mahasiswaId)
            ->whereIn('pertemuan_id', $pertemuanIds)
            ->get();

        $totalPertemuan = $pertemuanIds->count();
        $hadir = $presensi->where('status', Presensi::STATUS_HADIR)->count();
        $sakit = $presensi->where('status', Presensi::STATUS_SAKIT)->count();
        $izin = $presensi->where('status', Presensi::STATUS_IZIN)->count();
        $alpa = $presensi->where('status', Presensi::STATUS_ALPA)->count();

        $persentase = $totalPertemuan > 0 
            ? round(($hadir / $totalPertemuan) * 100, 1) 
            : 0;

        return [
            'total_pertemuan' => $totalPertemuan,
            'hadir' => $hadir,
            'sakit' => $sakit,
            'izin' => $izin,
            'alpa' => $alpa,
            'persentase' => $persentase,
        ];
    }

    /**
     * Get attendance summary for all mahasiswa in a kelas
     */
    public function getPresensiByKelas(int $kelasId): Collection
    {
        $kelas = Kelas::with(['krsDetail.krs.mahasiswa.user'])->findOrFail($kelasId);
        
        $mahasiswaList = $kelas->krsDetail
            ->filter(fn($d) => $d->krs->status === 'approved')
            ->map(fn($d) => $d->krs->mahasiswa);

        return $mahasiswaList->map(function ($mahasiswa) use ($kelasId) {
            $rekap = $this->getRekapPresensi($mahasiswa->id, $kelasId);
            return [
                'mahasiswa' => $mahasiswa,
                'rekap' => $rekap,
            ];
        });
    }

    /**
     * Check if minimum attendance requirement is met
     */
    public function isMinimumAttendanceMet(int $mahasiswaId, int $kelasId, float $threshold = 75): bool
    {
        $rekap = $this->getRekapPresensi($mahasiswaId, $kelasId);
        return $rekap['persentase'] >= $threshold;
    }

    /**
     * Get all kelas taught by a dosen
     */
    public function getKelasByDosen(int $dosenId): Collection
    {
        return Kelas::where('dosen_id', $dosenId)
            ->with(['mataKuliah', 'jadwal'])
            ->withCount(['krsDetail as jumlah_mahasiswa' => function ($q) {
                $q->whereHas('krs', fn($q2) => $q2->where('status', 'approved'));
            }])
            ->get();
    }

    /**
     * Get pertemuan list for a kelas
     */
    public function getPertemuanByKelas(int $kelasId): Collection
    {
        return Pertemuan::byKelas($kelasId)
            ->with('jadwalKuliah')
            ->orderBy('pertemuan_ke')
            ->get();
    }

    /**
     * Get next pertemuan_ke number for a jadwal
     */
    public function getNextPertemuanKe(int $jadwalKuliahId): int
    {
        $last = Pertemuan::where('jadwal_kuliah_id', $jadwalKuliahId)
            ->max('pertemuan_ke');
        
        return ($last ?? 0) + 1;
    }

    /**
     * Get mahasiswa list for a kelas (enrolled with approved KRS)
     */
    public function getMahasiswaByKelas(int $kelasId): Collection
    {
        return Mahasiswa::whereHas('krs', function ($q) use ($kelasId) {
            $q->where('status', 'approved')
              ->whereHas('krsDetail', fn($q2) => $q2->where('kelas_id', $kelasId));
        })->with('user')->get();
    }
}
