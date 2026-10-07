<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\JadwalKuliah;
use App\Models\KehadiranDosen;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class KehadiranDosenService
{
    /**
     * @var array<int, string>
     */
    private const NAMA_HARI = ['Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function namaHari(?Carbon $tanggal = null): string
    {
        return self::NAMA_HARI[($tanggal ?? now())->dayOfWeek];
    }

    public function getAbsenHariIni(Dosen $dosen): ?KehadiranDosen
    {
        return KehadiranDosen::query()
            ->where('dosen_id', $dosen->id)
            ->harian()
            ->whereDate('tanggal', now()->toDateString())
            ->first();
    }

    /**
     * Jadwal mengajar hari ini beserta pertemuan yang dibuat hari ini (untuk status presensi mahasiswa).
     *
     * @return Collection<int, JadwalKuliah>
     */
    public function getJadwalHariIni(Dosen $dosen): Collection
    {
        $today = now()->toDateString();
        $hari = $this->namaHari();
        $hariList = in_array($hari, ['Ahad', 'Minggu']) ? ['Ahad', 'Minggu'] : [$hari];

        return JadwalKuliah::query()
            ->whereHas('kelas', fn ($query) => $query->where('dosen_id', $dosen->id))
            ->whereIn('hari', $hariList)
            ->with([
                'kelas' => fn ($query) => $query->withCount('krsDetail'),
                'kelas.mataKuliah',
                'pertemuan' => fn ($query) => $query->whereDate('tanggal', $today)->withCount('presensi'),
            ])
            ->orderBy('jam_mulai')
            ->get();
    }

    /**
     * Jumlah kelas hari ini yang presensi mahasiswanya belum diisi.
     *
     * @param  Collection<int, JadwalKuliah>  $jadwalHariIni
     */
    public function hitungKelasBelumPresensi(Collection $jadwalHariIni): int
    {
        return $jadwalHariIni
            ->filter(fn (JadwalKuliah $jadwal) => $jadwal->pertemuan->sum('presensi_count') === 0)
            ->count();
    }

    /**
     * @param  array{status: string, keterangan?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function absenMasuk(Dosen $dosen, array $data, ?UploadedFile $bukti = null): KehadiranDosen
    {
        if ($this->getAbsenHariIni($dosen)) {
            throw ValidationException::withMessages([
                'status' => 'Anda sudah melakukan absen masuk hari ini.',
            ]);
        }

        $isHadir = $data['status'] === KehadiranDosen::STATUS_HADIR;

        return KehadiranDosen::create([
            'dosen_id' => $dosen->id,
            'jadwal_kuliah_id' => null,
            'tanggal' => now()->toDateString(),
            'jam_masuk' => now()->format('H:i:s'),
            'status' => $data['status'],
            'keterangan' => $isHadir ? null : ($data['keterangan'] ?? null),
            'bukti_file' => $bukti && ! $isHadir
                ? $bukti->store("kehadiran-dosen/{$dosen->id}")
                : null,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function absenKeluar(KehadiranDosen $kehadiran): KehadiranDosen
    {
        if (! $kehadiran->tanggal->isToday()) {
            throw ValidationException::withMessages(['absen' => 'Absen keluar hanya bisa dilakukan di hari yang sama.']);
        }

        if (! $kehadiran->isHadir()) {
            throw ValidationException::withMessages(['absen' => 'Absen keluar hanya untuk status hadir.']);
        }

        if ($kehadiran->jam_keluar) {
            throw ValidationException::withMessages(['absen' => 'Anda sudah melakukan absen keluar hari ini.']);
        }

        $kehadiran->update(['jam_keluar' => now()->format('H:i:s')]);

        return $kehadiran;
    }
}
