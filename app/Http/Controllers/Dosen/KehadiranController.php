<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dosen\AbsenMasukRequest;
use App\Models\KehadiranDosen;
use App\Services\KehadiranDosenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KehadiranController extends Controller
{
    public function __construct(private KehadiranDosenService $kehadiranDosenService) {}

    /**
     * Riwayat & rekap kehadiran. Absen masuk/keluar dilakukan dari dashboard.
     */
    public function index(Request $request): View
    {
        $dosen = Auth::user()->dosen;
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $search = $request->get('search');

        $absenHariIni = $this->kehadiranDosenService->getAbsenHariIni($dosen);
        $jadwalHariIni = $this->kehadiranDosenService->getJadwalHariIni($dosen);
        $kelasBelumPresensi = $this->kehadiranDosenService->hitungKelasBelumPresensi($jadwalHariIni);

        $stats = KehadiranDosen::where('dosen_id', $dosen->id)
            ->byMonth($year, $month)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $riwayatQuery = KehadiranDosen::where('dosen_id', $dosen->id)
            ->byMonth($year, $month)
            ->with('jadwalKuliah.kelas.mataKuliah')
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_masuk', 'desc');

        if ($search) {
            $riwayatQuery->where(function ($q) use ($search) {
                $q->whereHas('jadwalKuliah.kelas.mataKuliah', function ($mq) use ($search) {
                    $mq->where('nama_mk', 'like', "%{$search}%");
                })->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $riwayat = $riwayatQuery->get();

        return view('dosen.kehadiran.index', compact(
            'dosen',
            'absenHariIni',
            'jadwalHariIni',
            'kelasBelumPresensi',
            'stats',
            'riwayat',
            'month',
            'year',
            'search'
        ));
    }

    /**
     * Absen masuk harian (hadir / sakit / izin / tugas luar).
     */
    public function store(AbsenMasukRequest $request): RedirectResponse
    {
        $kehadiran = $this->kehadiranDosenService->absenMasuk(
            $request->user()->dosen,
            $request->validated(),
            $request->file('bukti_file'),
        );

        $message = $kehadiran->isHadir()
            ? 'Absen masuk berhasil. Selamat mengajar!'
            : 'Kehadiran tercatat sebagai '.$kehadiran->status_label.'.';

        return redirect()->back(fallback: route('dosen.dashboard'))->with('success', $message);
    }

    /**
     * Absen keluar harian.
     */
    public function checkout(KehadiranDosen $kehadiran): RedirectResponse
    {
        if ($kehadiran->dosen_id !== Auth::user()->dosen?->id) {
            abort(403);
        }

        $this->kehadiranDosenService->absenKeluar($kehadiran);

        return redirect()->back(fallback: route('dosen.dashboard'))->with('success', 'Absen keluar berhasil. Terima kasih!');
    }
}
