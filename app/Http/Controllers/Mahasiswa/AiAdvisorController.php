<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Services\AiAdvisorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAdvisorController extends Controller
{
    protected AiAdvisorService $aiService;

    public function __construct(AiAdvisorService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Display AI Advisor chat page
     */
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            abort(403, 'Unauthorized');
        }

        $mahasiswa->load(['user', 'prodi']);

        return view('mahasiswa.ai-advisor.index', compact('mahasiswa'));
    }

    /**
     * Handle chat message
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
        ]);

        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $result = $this->aiService->chat(
            $mahasiswa,
            $request->input('message'),
            $request->input('history', [])
        );

        return response()->json($result);
    }
}
