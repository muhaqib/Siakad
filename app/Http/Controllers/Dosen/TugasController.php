<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Tugas;
use App\Models\TugasSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    /**
     * List all tugas for a kelas
     */
    public function index($kelasId)
    {
        $dosen = Auth::user()->dosen;
        $kelas = $dosen->kelas()
            ->with(['mataKuliah', 'tugas' => fn($q) => $q->latest()])
            ->findOrFail($kelasId);

        return view('dosen.tugas.index', compact('kelas'));
    }

    /**
     * Create new tugas
     */
    public function store(Request $request, $kelasId)
    {
        $dosen = Auth::user()->dosen;
        $kelas = $dosen->kelas()->findOrFail($kelasId);

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'deadline' => 'required|date|after:now',
            'file_tugas' => 'nullable|file|max:10240',
            'allowed_extensions' => 'nullable|string|max:255',
        ]);

        $tugas = new Tugas([
            'kelas_id' => $kelas->id,
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'deadline' => $validated['deadline'],
            'allowed_extensions' => $validated['allowed_extensions'] ?? 'pdf,doc,docx,zip,rar',
        ]);

        if ($request->hasFile('file_tugas')) {
            $file = $request->file('file_tugas');
            $filename = time() . '_' . $file->getClientOriginalName();
            $tugas->file_tugas = $file->storeAs("tugas/kelas_{$kelasId}", $filename);
        }

        $tugas->save();

        return back()->with('success', 'Tugas berhasil dibuat.');
    }

    /**
     * Show tugas detail with submissions
     */
    public function show($kelasId, Tugas $tugas)
    {
        $dosen = Auth::user()->dosen;
        $kelas = $dosen->kelas()->findOrFail($kelasId);

        if ($tugas->kelas_id != $kelasId) {
            abort(403);
        }

        $tugas->load(['submissions.mahasiswa.user']);

        // Get enrolled students
        $enrolledStudents = \App\Models\Mahasiswa::whereHas('krs', function($q) use ($kelasId) {
            $q->where('status', 'approved')
              ->whereHas('krsDetail', fn($q2) => $q2->where('kelas_id', $kelasId));
        })->with('user')->get();

        return view('dosen.tugas.show', compact('kelas', 'tugas', 'enrolledStudents'));
    }

    /**
     * Grade a submission
     */
    public function grade(Request $request, $kelasId, TugasSubmission $submission)
    {
        $dosen = Auth::user()->dosen;
        $kelas = $dosen->kelas()->findOrFail($kelasId);

        if ($submission->tugas->kelas_id != $kelasId) {
            abort(403);
        }

        $validated = $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $submission->update([
            'nilai' => $validated['nilai'],
            'feedback' => $validated['feedback'] ?? null,
            'graded_at' => now(),
            'graded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Nilai berhasil disimpan.');
    }

    /**
     * Download submission file
     */
    public function downloadSubmission($kelasId, TugasSubmission $submission)
    {
        $dosen = Auth::user()->dosen;
        $kelas = $dosen->kelas()->findOrFail($kelasId);

        if ($submission->tugas->kelas_id != $kelasId) {
            abort(403);
        }

        if (!Storage::exists($submission->file_path)) {
            abort(404, 'File tidak ditemukan');
        }

        return Storage::download($submission->file_path, $submission->file_name);
    }

    /**
     * Toggle tugas active status
     */
    public function toggle($kelasId, Tugas $tugas)
    {
        $dosen = Auth::user()->dosen;
        $kelas = $dosen->kelas()->findOrFail($kelasId);

        if ($tugas->kelas_id != $kelasId) {
            abort(403);
        }

        $tugas->update(['is_active' => !$tugas->is_active]);

        return back()->with('success', 'Status tugas berhasil diubah.');
    }

    /**
     * Delete tugas
     */
    public function destroy($kelasId, Tugas $tugas)
    {
        $dosen = Auth::user()->dosen;
        $kelas = $dosen->kelas()->findOrFail($kelasId);

        if ($tugas->kelas_id != $kelasId) {
            abort(403);
        }

        // Delete related files
        if ($tugas->file_tugas) {
            Storage::delete($tugas->file_tugas);
        }
        
        foreach ($tugas->submissions as $submission) {
            Storage::delete($submission->file_path);
        }

        $tugas->delete();

        return back()->with('success', 'Tugas berhasil dihapus.');
    }
}
