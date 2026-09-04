<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MateriController extends Controller
{
    /**
     * Show materi for a specific kelas (mahasiswa view)
     * Supports historical access for archived semesters
     */
    public function index($kelasId)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            abort(403, 'Anda tidak memiliki akses sebagai mahasiswa.');
        }
        
        // Verify mahasiswa is enrolled in this kelas (from any semester)
        $isEnrolled = $mahasiswa->krs()
            ->where('status', 'approved')
            ->whereHas('krsDetail', fn($q) => $q->where('kelas_id', $kelasId))
            ->exists();

        if (!$isEnrolled) {
            abort(403, 'Anda tidak terdaftar di kelas ini.');
        }

        $kelas = Kelas::with('mataKuliah', 'dosen.user', 'tahunAkademik')->findOrFail($kelasId);

        // Check if this is an archived class
        $tahunAktif = TahunAkademik::active();
        $isArchived = $tahunAktif === null || $kelas->tahun_akademik_id !== $tahunAktif->id;

        // Get all pertemuan for this kelas
        $pertemuanList = Pertemuan::whereHas('jadwalKuliah', fn($q) => $q->where('kelas_id', $kelasId))
            ->with('materiList')
            ->orderBy('pertemuan_ke')
            ->get();

        return view('mahasiswa.materi.index', compact('kelas', 'pertemuanList', 'isArchived'));
    }

    /**
     * Download materi file
     */
    public function download($kelasId, Materi $materi)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        // Verify mahasiswa is enrolled in this kelas
        $isEnrolled = $mahasiswa->krs()
            ->where('status', 'approved')
            ->whereHas('krsDetail', fn($q) => $q->where('kelas_id', $kelasId))
            ->exists();

        if (!$isEnrolled) {
            abort(403, 'Anda tidak terdaftar di kelas ini.');
        }

        // Verify materi belongs to this kelas
        $pertemuan = $materi->pertemuan;
        if ($pertemuan->jadwalKuliah->kelas_id != $kelasId) {
            abort(403);
        }

        if (!$materi->file_path || !Storage::exists($materi->file_path)) {
            abort(404, 'File tidak ditemukan');
        }

        return Storage::download($materi->file_path, $materi->file_name);
    }
}
