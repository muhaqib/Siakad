<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Services\PenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenilaianController extends Controller
{
    protected $penilaianService;

    public function __construct(PenilaianService $penilaianService)
    {
        $this->penilaianService = $penilaianService;
    }

    public function index(Request $request)
    {
        $dosen = Auth::user()->dosen;
        $query = $dosen->kelas()->with(['mataKuliah', 'krsDetail']);

        // Filter by semester
        if ($request->filled('semester')) {
            $query->whereHas('mataKuliah', fn($q) => $q->where('semester', $request->semester));
        }

        // Search by nama mk or kode mk
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('mataKuliah', function ($q) use ($search) {
                $q->where('nama_mk', 'like', "%{$search}%")
                  ->orWhere('kode_mk', 'like', "%{$search}%");
            });
        }

        $kelasAjar = $query->get();

        // Group by semester
        $kelasGrouped = $kelasAjar->groupBy(fn($k) => $k->mataKuliah->semester);

        // Get available semesters from already loaded data (avoid N+1)
        $semesterList = $dosen->kelas()
            ->with('mataKuliah')
            ->get()
            ->pluck('mataKuliah.semester')
            ->unique()
            ->sort()
            ->values();

        if ($request->ajax()) {
            return view('dosen.penilaian._cards', compact('kelasAjar', 'kelasGrouped'))->render();
        }

        return view('dosen.penilaian.index', compact('kelasAjar', 'kelasGrouped', 'semesterList'));
    }

    public function show($kelasId)
    {
        $dosen = Auth::user()->dosen;
        $kelas = $dosen->kelas()->with(['mataKuliah', 'krsDetail.krs.mahasiswa.user', 'nilai'])->findOrFail($kelasId);
        
        return view('dosen.penilaian.show', compact('kelas'));
    }

    public function store(Request $request, $kelasId)
    {
        $request->validate([
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $this->penilaianService->bulkInputNilai($kelasId, $request->nilai);
            return redirect()->back()->with('success', 'Nilai berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
