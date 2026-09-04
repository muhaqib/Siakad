<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Services\AkademikService;
use Illuminate\Http\Request;

class ProdiController extends Controller
{
    protected $akademikService;

    public function __construct(AkademikService $akademikService)
    {
        $this->akademikService = $akademikService;
    }

    /**
     * Check if user can access a specific fakultas
     */
    private function authorizeProdiAccess(Prodi $prodi): void
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $user->fakultas_id !== $prodi->fakultas_id) {
            abort(response()->view('errors.403', ['message' => 'Anda tidak memiliki akses ke prodi ini.'], 403));
        }
    }

    /**
     * Get fakultas IDs that user can access
     */
    private function getAccessibleFakultasIds()
    {
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            return Fakultas::pluck('id')->toArray();
        }
        return $user->fakultas_id ? [$user->fakultas_id] : [];
    }

    public function index()
    {
        $user = auth()->user();
        
        $query = Fakultas::with(['prodi' => function($q) {
            $q->withCount(['mahasiswa', 'dosen']);
        }]);
        
        // Scope to user's fakultas if not superadmin
        if (!$user->isSuperAdmin() && $user->fakultas_id) {
            $query->where('id', $user->fakultas_id);
        }
        
        $fakultas = $query->get();
        $isSuperAdmin = $user->isSuperAdmin();
        
        return view('admin.prodi.index', compact('fakultas', 'isSuperAdmin'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fakultas_id' => 'required|exists:fakultas,id',
            'nama'        => 'required|string|max:255',
        ]);
        
        // Check if user can create prodi in this fakultas
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $user->fakultas_id != $validated['fakultas_id']) {
            abort(response()->view('errors.403', ['message' => 'Anda tidak dapat menambah prodi di fakultas ini.'], 403));
        }
        
        $this->akademikService->createProdi($validated);
        return redirect()->back()->with('success', 'Prodi berhasil ditambahkan');
    }

    public function update(Request $request, Prodi $prodi)
    {
        $this->authorizeProdiAccess($prodi);
        
        $validated = $request->validate([
            'fakultas_id' => 'required|exists:fakultas,id',
            'nama'        => 'required|string|max:255',
        ]);
        
        // Check if user can move prodi to target fakultas
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $user->fakultas_id != $validated['fakultas_id']) {
            abort(response()->view('errors.403', ['message' => 'Anda tidak dapat memindahkan prodi ke fakultas lain.'], 403));
        }
        
        $prodi->update($validated);
        return redirect()->back()->with('success', 'Prodi berhasil diupdate');
    }

    public function destroy(Prodi $prodi)
    {
        $this->authorizeProdiAccess($prodi);
        
        $prodi->delete();
        return redirect()->back()->with('success', 'Prodi berhasil dihapus');
    }
}

