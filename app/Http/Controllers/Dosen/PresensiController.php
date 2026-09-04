<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\JadwalKuliah;
use App\Models\Pertemuan;
use App\Models\Presensi;
use App\Services\PresensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{
    protected PresensiService $presensiService;

    public function __construct(PresensiService $presensiService)
    {
        $this->presensiService = $presensiService;
    }

    /**
     * Daftar kelas yang diampu
     */
    public function index()
    {
        $dosen = Auth::user()->dosen;
        
        if (!$dosen) {
            abort(403, 'Unauthorized');
        }

        $kelasList = $this->presensiService->getKelasByDosen($dosen->id);
        
        // Group by semester
        $kelasGrouped = $kelasList->groupBy(fn($k) => $k->mataKuliah->semester ?? 0);
        
        // Get semester list for filter
        $semesterList = $kelasList->pluck('mataKuliah.semester')->unique()->sort()->values();

        return view('dosen.presensi.index', compact('kelasList', 'kelasGrouped', 'semesterList'));
    }

    /**
     * Detail presensi per kelas
     */
    public function showKelas(Kelas $kelas)
    {
        $dosen = Auth::user()->dosen;
        
        if ($kelas->dosen_id !== $dosen->id) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini');
        }

        $kelas->load(['mataKuliah', 'jadwal']);
        $pertemuanList = $this->presensiService->getPertemuanByKelas($kelas->id);
        $rekapMahasiswa = $this->presensiService->getPresensiByKelas($kelas->id);

        return view('dosen.presensi.kelas', compact('kelas', 'pertemuanList', 'rekapMahasiswa'));
    }

    /**
     * Form buat pertemuan baru
     */
    public function createPertemuan(Kelas $kelas)
    {
        $dosen = Auth::user()->dosen;
        
        if ($kelas->dosen_id !== $dosen->id) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini');
        }

        $kelas->load(['mataKuliah', 'jadwal']);
        $jadwalList = $kelas->jadwal;
        $nextPertemuanKe = [];
        
        foreach ($jadwalList as $jadwal) {
            $nextPertemuanKe[$jadwal->id] = $this->presensiService->getNextPertemuanKe($jadwal->id);
        }

        return view('dosen.presensi.pertemuan-create', compact('kelas', 'jadwalList', 'nextPertemuanKe'));
    }

    /**
     * Simpan pertemuan baru
     */
    public function storePertemuan(Request $request, Kelas $kelas)
    {
        $dosen = Auth::user()->dosen;
        
        if ($kelas->dosen_id !== $dosen->id) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini');
        }

        $validated = $request->validate([
            'jadwal_kuliah_id' => 'required|exists:jadwal_kuliah,id',
            'pertemuan_ke' => 'required|integer|min:1|max:16',
            'tanggal' => 'required|date',
            'materi' => 'nullable|string|max:255',
        ]);

        // Check jadwal belongs to this kelas
        $jadwal = JadwalKuliah::findOrFail($validated['jadwal_kuliah_id']);
        if ($jadwal->kelas_id !== $kelas->id) {
            abort(403, 'Jadwal tidak valid');
        }

        // Check duplicate pertemuan_ke
        $exists = Pertemuan::where('jadwal_kuliah_id', $validated['jadwal_kuliah_id'])
            ->where('pertemuan_ke', $validated['pertemuan_ke'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['pertemuan_ke' => 'Pertemuan ke-' . $validated['pertemuan_ke'] . ' sudah ada'])
                ->withInput();
        }

        $pertemuan = $this->presensiService->createPertemuan(
            $validated['jadwal_kuliah_id'],
            $validated['pertemuan_ke'],
            $validated['tanggal'],
            $validated['materi']
        );

        return redirect()->route('dosen.presensi.input', $pertemuan)
            ->with('success', 'Pertemuan berhasil dibuat. Silakan input presensi.');
    }

    /**
     * Form input presensi
     */
    public function inputPresensi(Pertemuan $pertemuan)
    {
        $dosen = Auth::user()->dosen;
        $kelas = $pertemuan->jadwalKuliah->kelas;
        
        if ($kelas->dosen_id !== $dosen->id) {
            abort(403, 'Anda tidak memiliki akses ke pertemuan ini');
        }

        $pertemuan->load(['jadwalKuliah.kelas.mataKuliah', 'presensi']);
        $mahasiswaList = $this->presensiService->getMahasiswaByKelas($kelas->id);
        
        // Get existing presensi data
        $existingPresensi = $pertemuan->presensi->keyBy('mahasiswa_id');

        return view('dosen.presensi.input', compact('pertemuan', 'kelas', 'mahasiswaList', 'existingPresensi'));
    }

    /**
     * Simpan presensi
     */
    public function storePresensi(Request $request, Pertemuan $pertemuan)
    {
        $dosen = Auth::user()->dosen;
        $kelas = $pertemuan->jadwalKuliah->kelas;
        
        if ($kelas->dosen_id !== $dosen->id) {
            abort(403, 'Anda tidak memiliki akses ke pertemuan ini');
        }

        $validated = $request->validate([
            'presensi' => 'required|array',
            'presensi.*' => 'required|in:hadir,sakit,izin,alpa',
        ]);

        $this->presensiService->recordPresensi($pertemuan->id, $validated['presensi']);

        return redirect()->route('dosen.presensi.kelas', $kelas)
            ->with('success', 'Presensi berhasil disimpan');
    }
}
