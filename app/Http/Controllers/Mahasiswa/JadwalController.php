<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    /**
     * Display jadwal kuliah berdasarkan KRS mahasiswa
     */
    public function index()
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;
        
        if (!$mahasiswa) {
            abort(403, 'Unauthorized');
        }

        // Get active tahun akademik
        $activeTA = TahunAkademik::where('is_active', true)->first();
        
        if (!$activeTA) {
            return view('mahasiswa.jadwal.index', [
                'mahasiswa' => $mahasiswa,
                'jadwalPerHari' => collect(),
                'activeTA' => null,
                'message' => 'Tidak ada tahun akademik aktif'
            ]);
        }

        // Get kelas from approved KRS for current semester
        $kelasList = Kelas::whereHas('krsDetail', function ($q) use ($mahasiswa, $activeTA) {
            $q->whereHas('krs', fn($q2) => $q2
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('tahun_akademik_id', $activeTA->id)
                ->where('status', 'approved')
            );
        })->with(['mataKuliah', 'dosen.user', 'jadwal'])->get();

        // Group jadwal by hari
        $hariOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $jadwalPerHari = collect();
        
        foreach ($hariOrder as $hari) {
            $jadwalHariIni = collect();
            
            foreach ($kelasList as $kelas) {
                foreach ($kelas->jadwal as $jadwal) {
                    if ($jadwal->hari === $hari) {
                        $jadwalHariIni->push([
                            'kelas' => $kelas,
                            'jadwal' => $jadwal,
                        ]);
                    }
                }
            }
            
            if ($jadwalHariIni->isNotEmpty()) {
                // Sort by jam_mulai
                $jadwalHariIni = $jadwalHariIni->sortBy(fn($j) => $j['jadwal']->jam_mulai);
                $jadwalPerHari[$hari] = $jadwalHariIni;
            }
        }

        return view('mahasiswa.jadwal.index', compact('mahasiswa', 'jadwalPerHari', 'activeTA'));
    }
}
