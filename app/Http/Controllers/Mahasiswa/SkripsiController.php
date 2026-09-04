<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Skripsi;
use App\Models\BimbinganSkripsi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SkripsiController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            abort(403);
        }

        $skripsi = Skripsi::where('mahasiswa_id', $mahasiswa->id)
            ->with(['pembimbing1.user', 'pembimbing2.user', 'bimbingan'])
            ->first();

        $bimbinganList = $skripsi ? $skripsi->bimbingan()->with('dosen.user')->get() : collect();

        return view('mahasiswa.skripsi.index', compact('mahasiswa', 'skripsi', 'bimbinganList'));
    }

    public function create()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        // Check if already has skripsi
        $existing = Skripsi::where('mahasiswa_id', $mahasiswa->id)->first();
        if ($existing) {
            return redirect()->route('mahasiswa.skripsi.index')
                ->with('error', 'Anda sudah memiliki pengajuan skripsi');
        }

        return view('mahasiswa.skripsi.create', compact('mahasiswa'));
    }

    public function store(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;

        $validated = $request->validate([
            'judul' => 'required|string|max:500',
            'abstrak' => 'nullable|string',
            'bidang_kajian' => 'nullable|string|max:100',
        ]);

        Skripsi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'judul' => $validated['judul'],
            'abstrak' => $validated['abstrak'],
            'bidang_kajian' => $validated['bidang_kajian'],
            'status' => Skripsi::STATUS_PENGAJUAN,
            'tanggal_pengajuan' => now(),
        ]);

        return redirect()->route('mahasiswa.skripsi.index')
            ->with('success', 'Pengajuan judul skripsi berhasil dikirim');
    }

    public function storeBimbingan(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $skripsi = Skripsi::where('mahasiswa_id', $mahasiswa->id)->firstOrFail();

        $validated = $request->validate([
            'catatan_mahasiswa' => 'required|string',
            'file_dokumen' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file_dokumen')) {
            $filePath = $request->file('file_dokumen')->store('skripsi/bimbingan', 'public');
        }

        // Default to pembimbing 1
        $dosenId = $skripsi->pembimbing1_id;

        BimbinganSkripsi::create([
            'skripsi_id' => $skripsi->id,
            'dosen_id' => $dosenId,
            'tanggal_bimbingan' => now(),
            'catatan_mahasiswa' => $validated['catatan_mahasiswa'],
            'file_dokumen' => $filePath,
            'status' => BimbinganSkripsi::STATUS_MENUNGGU,
        ]);

        return redirect()->route('mahasiswa.skripsi.index')
            ->with('success', 'Catatan bimbingan berhasil dikirim');
    }
}
