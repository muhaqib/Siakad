<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Models\Tugas;
use App\Models\TugasSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    /**
     * List all tugas for a kelas
     * Supports historical access for archived semesters
     */
    public function index($kelasId)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        // Verify enrollment (from any semester)
        $isEnrolled = $mahasiswa->krs()
            ->where('status', 'approved')
            ->whereHas('krsDetail', fn($q) => $q->where('kelas_id', $kelasId))
            ->exists();

        if (!$isEnrolled) {
            abort(403, 'Anda tidak terdaftar di kelas ini.');
        }

        $kelas = Kelas::with(['mataKuliah', 'dosen.user', 'tahunAkademik'])->findOrFail($kelasId);
        
        // Check if this is an archived class
        $tahunAktif = TahunAkademik::active();
        $isArchived = $tahunAktif === null || $kelas->tahun_akademik_id !== $tahunAktif->id;
        
        // Get tugas with submission status (show all tugas for archived, only active for current)
        $tugasQuery = Tugas::where('kelas_id', $kelasId)
            ->with(['submissions' => fn($q) => $q->where('mahasiswa_id', $mahasiswa->id)]);
        
        // For active semester, only show active tugas. For archived, show all.
        if (!$isArchived) {
            $tugasQuery->where('is_active', true);
        }
        
        $tugasList = $tugasQuery->latest()->get();

        return view('mahasiswa.tugas.index', compact('kelas', 'tugasList', 'mahasiswa', 'isArchived'));
    }

    /**
     * Show tugas detail
     * Supports historical access for archived semesters
     */
    public function show($kelasId, Tugas $tugas)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        // Verify enrollment (from any semester)
        $isEnrolled = $mahasiswa->krs()
            ->where('status', 'approved')
            ->whereHas('krsDetail', fn($q) => $q->where('kelas_id', $kelasId))
            ->exists();

        if (!$isEnrolled || $tugas->kelas_id != $kelasId) {
            abort(403);
        }

        $kelas = Kelas::with(['mataKuliah', 'dosen.user', 'tahunAkademik'])->findOrFail($kelasId);
        
        // Check if this is an archived class
        $tahunAktif = TahunAkademik::active();
        $isArchived = $tahunAktif === null || $kelas->tahun_akademik_id !== $tahunAktif->id;
        
        $submission = $tugas->submissions()->where('mahasiswa_id', $mahasiswa->id)->first();

        return view('mahasiswa.tugas.show', compact('kelas', 'tugas', 'submission', 'isArchived'));
    }

    /**
     * Submit tugas
     */
    public function submit(Request $request, $kelasId, Tugas $tugas)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        // Verify enrollment
        $isEnrolled = $mahasiswa->krs()
            ->where('status', 'approved')
            ->whereHas('krsDetail', fn($q) => $q->where('kelas_id', $kelasId))
            ->exists();

        if (!$isEnrolled || $tugas->kelas_id != $kelasId) {
            abort(403);
        }

        // Check if still open
        if (!$tugas->isOpen()) {
            return back()->with('error', 'Deadline sudah lewat atau tugas tidak aktif.');
        }

        // Check if already submitted
        $existingSubmission = $tugas->submissions()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();

        if ($existingSubmission) {
            return back()->with('error', 'Anda sudah mengumpulkan tugas ini.');
        }

        // Validate file
        $maxSize = $tugas->max_file_size / 1024; // Convert to KB for validation
        $allowedExt = $tugas->allowed_extensions;
        
        $validated = $request->validate([
            'file' => "required|file|max:{$maxSize}|mimes:{$allowedExt}",
            'catatan' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $mahasiswa->nim . '_' . $file->getClientOriginalName();
        $path = $file->storeAs("tugas/kelas_{$kelasId}/tugas_{$tugas->id}", $filename);

        TugasSubmission::create([
            'tugas_id' => $tugas->id,
            'mahasiswa_id' => $mahasiswa->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'catatan' => $validated['catatan'] ?? null,
            'submitted_at' => now(),
        ]);

        return back()->with('success', 'Tugas berhasil dikumpulkan.');
    }

    /**
     * Download tugas file (soal)
     */
    public function download($kelasId, Tugas $tugas)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        // Verify enrollment
        $isEnrolled = $mahasiswa->krs()
            ->where('status', 'approved')
            ->whereHas('krsDetail', fn($q) => $q->where('kelas_id', $kelasId))
            ->exists();

        if (!$isEnrolled || $tugas->kelas_id != $kelasId) {
            abort(403);
        }

        if (!$tugas->file_tugas || !Storage::exists($tugas->file_tugas)) {
            abort(404, 'File tidak ditemukan');
        }

        return Storage::download($tugas->file_tugas);
    }
}
