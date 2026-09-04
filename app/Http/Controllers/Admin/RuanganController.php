<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    public function index(Request $request)
    {
        $query = Ruangan::with('fakultas');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_ruangan', 'like', "%{$search}%")
                  ->orWhere('kode_ruangan', 'like', "%{$search}%")
                  ->orWhere('gedung', 'like', "%{$search}%");
            });
        }

        // Faculty scoping for admin_fakultas
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $query->where(function($q) use ($fakultasId) {
                $q->where('fakultas_id', $fakultasId)
                  ->orWhereNull('fakultas_id'); // Also show unassigned ones
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'kode_ruangan');
        $sortDirection = $request->get('order', 'asc');
        $allowedSorts = ['kode_ruangan', 'nama_ruangan', 'kapasitas', 'gedung', 'lantai', 'is_active'];

        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('kode_ruangan', 'asc');
        }
        
        $ruanganList = $query->paginate(config('siakad.pagination', 15))->withQueryString();
        
        // Stats - also scoped
        $statsQuery = Ruangan::query();
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $statsQuery->where(function($q) use ($fakultasId) {
                $q->where('fakultas_id', $fakultasId)
                  ->orWhereNull('fakultas_id');
            });
        }
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('is_active', true)->count(),
            'capacity' => (clone $statsQuery)->sum('kapasitas'),
            'gedung_count' => (clone $statsQuery)->distinct('gedung')->count('gedung'),
        ];
        
        // Fakultas list for dropdown (only for superadmin)
        $fakultasList = auth()->user()->isSuperAdmin() ? Fakultas::all() : collect();
        
        return view('admin.ruangan.index', compact('ruanganList', 'stats', 'fakultasList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_ruangan' => 'required|string|max:20|unique:ruangan,kode_ruangan',
            'nama_ruangan' => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1',
            'gedung' => 'nullable|string|max:50',
            'lantai' => 'nullable|integer|min:1',
            'fasilitas' => 'nullable|string',
            'is_active' => 'boolean',
            'fakultas_id' => 'nullable|exists:fakultas,id',
        ]);

        $validated['is_active'] = $request->has('is_active');
        
        // Auto-assign fakultas for admin_fakultas
        if (empty($validated['fakultas_id']) && $request->get('fakultas_scoped')) {
            $validated['fakultas_id'] = $request->get('fakultas_scope');
        }

        Ruangan::create($validated);

        return redirect()->back()->with('success', 'Ruangan berhasil ditambahkan');
    }

    public function update(Request $request, Ruangan $ruangan)
    {
        $validated = $request->validate([
            'kode_ruangan' => 'required|string|max:20|unique:ruangan,kode_ruangan,' . $ruangan->id,
            'nama_ruangan' => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1',
            'gedung' => 'nullable|string|max:50',
            'lantai' => 'nullable|integer|min:1',
            'fasilitas' => 'nullable|string',
            'is_active' => 'boolean',
            'fakultas_id' => 'nullable|exists:fakultas,id',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $ruangan->update($validated);

        return redirect()->back()->with('success', 'Ruangan berhasil diupdate');
    }

    public function destroy(Ruangan $ruangan)
    {
        // Check if ruangan is used in jadwal
        if ($ruangan->jadwalKuliah()->exists()) {
            return redirect()->back()->withErrors(['error' => 'Tidak dapat menghapus ruangan yang digunakan dalam jadwal.']);
        }
        
        $ruangan->delete();
        return redirect()->back()->with('success', 'Ruangan berhasil dihapus');
    }
}

