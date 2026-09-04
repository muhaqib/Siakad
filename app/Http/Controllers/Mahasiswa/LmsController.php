<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LmsController extends Controller
{
    /**
     * Show LMS dashboard with enrolled kelas
     * Supports semester filtering: aktif, arsip, semua
     */
    public function index(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            abort(403, 'Anda tidak memiliki akses sebagai mahasiswa.');
        }

        // Get active tahun akademik
        $tahunAktif = TahunAkademik::active();
        
        // Get all approved KRS with kelas data
        $allKrs = $mahasiswa->krs()
            ->where('status', 'approved')
            ->with(['tahunAkademik', 'krsDetail.kelas.mataKuliah', 'krsDetail.kelas.dosen.user', 'krsDetail.kelas.tahunAkademik'])
            ->get();

        // Extract all kelas from KRS details with the KRS tahun_akademik for proper filtering
        $allKelas = $allKrs->flatMap(function($krs) {
            return $krs->krsDetail->map(function($detail) use ($krs) {
                $kelas = $detail->kelas;
                $kelas->krs_tahun_akademik = $krs->tahunAkademik;
                $kelas->krs_tahun_akademik_id = $krs->tahun_akademik_id; // Use this for filtering!
                return $kelas;
            });
        })->unique('id');

        // Get available semesters for dropdown with sequential numbering
        $semesterList = $allKrs->pluck('tahunAkademik')->unique('id')->sortBy('id'); // Sort chronologically (oldest first)
        
        // Add semester number (Semester 1, 2, 3...) based on enrollment order
        $semesterNumber = 1;
        $availableSemesters = $semesterList->map(function($semester) use (&$semesterNumber) {
            $semester->semester_number = $semesterNumber;
            $semester->semester_label = "Semester {$semesterNumber}";
            $semesterNumber++;
            return $semester;
        })->sortByDesc('id'); // Sort back to newest first for dropdown display
        
        // Get current semester number (for active semester)
        $currentSemesterNumber = $tahunAktif ? $availableSemesters->firstWhere('id', $tahunAktif->id)?->semester_number : null;
        
        // Determine semester filter - USE krs_tahun_akademik_id for filtering (semester when enrolled)
        if ($tahunAktif === null) {
            // Libur semester: check if specific semester is selected
            $semesterFilter = $request->query('semester', 'semua');
            
            if (is_numeric($semesterFilter)) {
                // Filter by specific semester (using KRS tahun akademik)
                $kelasList = $allKelas->filter(fn($k) => $k->krs_tahun_akademik_id == $semesterFilter);
            } else {
                // Show all classes
                $kelasList = $allKelas;
                $semesterFilter = 'semua';
            }
        } else {
            $semesterFilter = $request->query('semester', 'aktif');
            
            if (is_numeric($semesterFilter)) {
                // Filter by specific semester ID (using KRS tahun akademik)
                $kelasList = $allKelas->filter(fn($k) => $k->krs_tahun_akademik_id == $semesterFilter);
            } elseif ($semesterFilter === 'aktif') {
                // Show active semester classes (using KRS tahun akademik)
                $kelasList = $allKelas->filter(fn($k) => $k->krs_tahun_akademik_id === $tahunAktif->id);
            } else {
                // Default to active
                $kelasList = $allKelas->filter(fn($k) => $k->krs_tahun_akademik_id === $tahunAktif->id);
                $semesterFilter = 'aktif';
            }
        }

        // Add pending tugas count and archive status to each kelas
        $kelasList = $kelasList->map(function($kelas) use ($mahasiswa, $tahunAktif) {
            // Count pending tugas (only for active semester)
            // Use krs_tahun_akademik_id to determine if archived (based on enrollment semester)
            $isArchived = $tahunAktif === null || $kelas->krs_tahun_akademik_id !== $tahunAktif->id;
            $kelas->is_archived = $isArchived;
            
            if (!$isArchived) {
                $submittedTugasIds = $mahasiswa->tugasSubmissions()->pluck('tugas_id')->toArray();
                $activeTugas = $kelas->tugas()->where('is_active', true)->get();
                $kelas->pending_tugas = $activeTugas->whereNotIn('id', $submittedTugasIds)->count();
            } else {
                $kelas->pending_tugas = 0;
            }
            
            return $kelas;
        });

        // Group by semester for display (especially for 'semua' views)
        // Use krs_tahun_akademik_id for proper grouping by enrollment semester
        $kelasGrouped = $kelasList->groupBy(function($kelas) use ($availableSemesters) {
            $semester = $availableSemesters->firstWhere('id', $kelas->krs_tahun_akademik_id);
            return $semester?->semester_label ?? $kelas->krs_tahun_akademik->display_name ?? 'Unknown';
        });

        return view('mahasiswa.lms.index', compact(
            'kelasList', 
            'kelasGrouped',
            'semesterFilter', 
            'availableSemesters', 
            'tahunAktif',
            'currentSemesterNumber'
        ));
    }
}
