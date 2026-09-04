<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAkademik;
use App\Services\AkademikService;
use Illuminate\Http\Request;

class TahunAkademikController extends Controller
{
    protected $akademikService;

    public function __construct(AkademikService $akademikService)
    {
        $this->akademikService = $akademikService;
    }

    /**
     * Check if user is superadmin, abort if not
     */
    private function authorizeSuperAdmin(): void
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(response()->view('errors.403', ['message' => 'Hanya superadmin yang dapat mengelola tahun akademik.'], 403));
        }
    }


    public function index()
    {
        $tahunAkademik = TahunAkademik::orderBy('tahun', 'desc')
            ->orderBy('semester', 'desc')
            ->paginate(config('siakad.pagination', 15));

        return view('admin.tahun-akademik.index', compact('tahunAkademik'));
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();
        
        $validated = $request->validate([
            'tahun' => 'required|string|max:9', // e.g., "2024/2025"
            'semester' => 'required|in:ganjil,genap',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'tanggal_krs_mulai' => 'nullable|date',
            'tanggal_krs_selesai' => 'nullable|date|after_or_equal:tanggal_krs_mulai',
        ]);

        // Check for duplicate
        $exists = TahunAkademik::where('tahun', $validated['tahun'])
            ->where('semester', $validated['semester'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['tahun' => 'Tahun akademik dan semester ini sudah ada.']);
        }

        TahunAkademik::create([
            'tahun' => $validated['tahun'],
            'semester' => $validated['semester'],
            'is_active' => false,
            'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'tanggal_krs_mulai' => $validated['tanggal_krs_mulai'] ?? null,
            'tanggal_krs_selesai' => $validated['tanggal_krs_selesai'] ?? null,
        ]);

        return back()->with('success', 'Tahun akademik berhasil ditambahkan.');
    }

    public function update(Request $request, TahunAkademik $tahunAkademik)
    {
        $this->authorizeSuperAdmin();
        
        $validated = $request->validate([
            'tahun' => 'required|string|max:9',
            'semester' => 'required|in:ganjil,genap',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'tanggal_krs_mulai' => 'nullable|date',
            'tanggal_krs_selesai' => 'nullable|date|after_or_equal:tanggal_krs_mulai',
        ]);

        // Check for duplicate (excluding current)
        $exists = TahunAkademik::where('tahun', $validated['tahun'])
            ->where('semester', $validated['semester'])
            ->where('id', '!=', $tahunAkademik->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['tahun' => 'Tahun akademik dan semester ini sudah ada.']);
        }

        $tahunAkademik->update($validated);

        return back()->with('success', 'Tahun akademik berhasil diupdate.');
    }

    public function destroy(TahunAkademik $tahunAkademik)
    {
        $this->authorizeSuperAdmin();
        
        // Prevent deletion if active
        if ($tahunAkademik->is_active) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus tahun akademik yang sedang aktif.']);
        }

        // Check if there are related KRS
        if ($tahunAkademik->krs()->exists()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus tahun akademik yang memiliki data KRS.']);
        }

        $tahunAkademik->delete();

        return back()->with('success', 'Tahun akademik berhasil dihapus.');
    }

    public function getActive()
    {
        return response()->json($this->akademikService->getActiveTahun());
    }

    public function activate($id)
    {
        $this->authorizeSuperAdmin();
        
        $this->akademikService->activateTahun($id);
        return response()->json(['message' => 'Tahun akademik activated']);
    }
}
