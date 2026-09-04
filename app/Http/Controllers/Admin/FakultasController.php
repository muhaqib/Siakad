<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AkademikService;
use Illuminate\Http\Request;

class FakultasController extends Controller
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
            abort(response()->view('errors.403', ['message' => 'Hanya superadmin yang dapat mengelola fakultas.'], 403));
        }
    }


    public function index()
    {
        $this->authorizeSuperAdmin();
        
        $fakultas = $this->akademikService->getAllFakultas();
        return view('admin.fakultas.index', compact('fakultas'));
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();
        
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $this->akademikService->createFakultas($validated);
        return redirect()->back()->with('success', 'Fakultas berhasil ditambahkan');
    }

    public function update(Request $request, \App\Models\Fakultas $fakultas)
    {
        $this->authorizeSuperAdmin();
        
        $validated = $request->validate(['nama' => 'required|string|max:255']);
        $fakultas->update($validated);
        return redirect()->back()->with('success', 'Fakultas berhasil diupdate');
    }

    public function destroy(\App\Models\Fakultas $fakultas)
    {
        $this->authorizeSuperAdmin();
        
        $fakultas->delete();
        return redirect()->back()->with('success', 'Fakultas berhasil dihapus');
    }
}
