<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LaporanAbsensiRequest;
use App\Models\KehadiranDosen;
use App\Models\Pertemuan;
use App\Models\Presensi;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LaporanAbsensiController extends Controller
{
    public function index(LaporanAbsensiRequest $request): View
    {
        $jenis = $request->validated('jenis', 'dosen');
        $isDosen = $jenis === 'dosen';
        $semester = $request->validated('semester');
        $prodiId = $request->validated('prodi_id');
        $studentQuery = Presensi::query()
            ->when($prodiId, fn (Builder $query) => $query->whereHas('mahasiswa', fn (Builder $mahasiswa) => $mahasiswa->where('prodi_id', $prodiId)))
            ->when($semester, fn (Builder $query) => $query->whereHas('pertemuan.jadwalKuliah.kelas.mataKuliah', fn (Builder $mataKuliah) => $mataKuliah->where('semester', $semester)));
        $tanggalTerakhir = $isDosen
            ? KehadiranDosen::query()->max('tanggal')
            : Pertemuan::query()->whereHas('presensi', fn (Builder $query) => $query->whereIn('id', (clone $studentQuery)->select('id')))->max('tanggal');
        $periodeTerakhir = $tanggalTerakhir ? CarbonImmutable::parse($tanggalTerakhir)->format('Y-m') : null;
        $periode = $request->validated('periode', $periodeTerakhir ?? now()->format('Y-m'));
        $bulan = CarbonImmutable::createFromFormat('!Y-m', $periode);
        $awal = $bulan->toDateString();
        $akhir = $bulan->addMonth()->toDateString();

        if ($isDosen) {
            $records = KehadiranDosen::query()
                ->with(['dosen.user', 'dosen.prodi', 'jadwalKuliah.kelas.mataKuliah', 'jadwalKuliah.kelas.tahunAkademik'])
                ->where('tanggal', '>=', $awal)->where('tanggal', '<', $akhir)
                ->orderBy('tanggal')->orderBy('dosen_id')->orderBy('id')->get();
        } else {
            $records = $studentQuery
                ->with(['mahasiswa.user', 'mahasiswa.prodi', 'pertemuan.jadwalKuliah.kelas.mataKuliah', 'pertemuan.jadwalKuliah.kelas.tahunAkademik'])
                ->whereHas('pertemuan', fn ($query) => $query->where('tanggal', '>=', $awal)->where('tanggal', '<', $akhir))
                ->get()->sortBy(fn (Presensi $presensi): string => $presensi->pertemuan->tanggal->format('Y-m-d').'-'.str_pad((string) $presensi->id, 20, '0', STR_PAD_LEFT));
        }

        $statuses = $isDosen ? KehadiranDosen::getStatusList() : ['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'];
        $title = $isDosen ? 'Absensi Dosen' : 'Absensi Mahasiswa';

        $rows = $this->attendanceRows($records, $isDosen);
        $prodiList = $isDosen ? collect() : Prodi::query()->orderBy('nama')->get();
        $prodiNama = $prodiList->firstWhere('id', $prodiId)?->nama ?? 'Semua Prodi';
        $tahunAjaran = $records->map(fn ($record) => $isDosen
            ? $record->jadwalKuliah?->kelas?->tahunAkademik?->display_name
            : $record->pertemuan?->jadwalKuliah?->kelas?->tahunAkademik?->display_name)
            ->filter()->unique()->sort()->implode(', ');
        if ($tahunAjaran === '') {
            $tahunAjaran = TahunAkademik::query()
                ->where('tanggal_mulai', '<', $akhir)->where('tanggal_selesai', '>=', $awal)
                ->get()->pluck('display_name')->implode(', ');
        }

        return view('admin.laporan-absensi.index', compact(
            'periode', 'bulan', 'jenis', 'isDosen', 'records', 'statuses', 'title', 'periodeTerakhir',
            'rows', 'semester', 'prodiId', 'prodiList', 'prodiNama', 'tahunAjaran',
        ));
    }

    /**
     * @return Collection<int, array{name: string, identity: string, days: array<int, string>, counts: array<string, int>}>
     */
    private function attendanceRows(Collection $records, bool $isDosen): Collection
    {
        $codes = ['hadir' => 'H', 'sakit' => 'S', 'izin' => 'I', 'alpa' => 'A', 'tugas' => 'T'];

        return $records->groupBy($isDosen ? 'dosen_id' : 'mahasiswa_id')
            ->map(function (Collection $attendance) use ($isDosen, $codes): array {
                $person = $isDosen ? $attendance->first()->dosen : $attendance->first()->mahasiswa;
                $days = $attendance->groupBy(fn ($record): int => (int) ($isDosen ? $record->tanggal : $record->pertemuan->tanggal)->day)
                    ->map(fn (Collection $daily): string => $daily->pluck('status')->unique()->sort()
                        ->map(fn (string $status): string => $codes[$status] ?? '?')->implode('/'))->all();

                return [
                    'name' => $person?->user?->name ?? 'Tanpa nama',
                    'identity' => (string) ($isDosen ? $person?->nidn : $person?->nim),
                    'days' => $days,
                    'counts' => $attendance->countBy('status')->all(),
                ];
            })->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }
}
