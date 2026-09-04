<?php

namespace App\Services;

use App\Exceptions\KrsException;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\DB;

class KrsService
{
    protected AkademikService $akademikService;
    protected AkademikCalculationService $calculationService;

    public function __construct(
        AkademikService $akademikService,
        AkademikCalculationService $calculationService
    ) {
        $this->akademikService = $akademikService;
        $this->calculationService = $calculationService;
    }

    public function getActiveKrsOrNew(Mahasiswa $mahasiswa)
    {
        // Use cached Tahun Akademik from AkademikService
        $tahunAktif = $this->akademikService->getActiveTahun();
        if (!$tahunAktif) {
            throw KrsException::noActiveSemester();
        }

        return Krs::firstOrCreate(
            [
                'mahasiswa_id' => $mahasiswa->id,
                'tahun_akademik_id' => $tahunAktif->id
            ],
            ['status' => 'draft']
        );
    }

    /**
     * Calculate max SKS based on last semester's IPS
     */
    public function getMaxSksForMahasiswa(Mahasiswa $mahasiswa): int
    {
        // Get IPS history to find last semester's IPS
        $ipsHistory = $this->calculationService->getIPSHistory($mahasiswa);
        
        // Filter only semesters with actual grades (IPS > 0)
        $semestersWithGrades = $ipsHistory->filter(fn($s) => $s['ips'] > 0);
        
        if ($semestersWithGrades->isEmpty()) {
            // New student (semester 1) - use default max SKS
            return config('siakad.maks_sks.default', 24);
        }

        // Get the last semester's IPS
        $lastSemester = $semestersWithGrades->last();
        $lastIps = $lastSemester['ips'] ?? 0;

        // Calculate max SKS based on IPS using rules from config
        return $this->calculationService->getMaxSKS($lastIps);
    }

    public function addKelas(Krs $krs, $kelasId)
    {
        return DB::transaction(function () use ($krs, $kelasId) {
            if ($krs->status !== 'draft') {
                throw KrsException::alreadySubmitted();
            }

            $kelas = Kelas::with('mataKuliah')->findOrFail($kelasId);
            
            // 1. Cek Kapasitas
            $terisi = KrsDetail::where('kelas_id', $kelasId)->count();
            if ($terisi >= $kelas->kapasitas) {
                throw KrsException::classFull($kelas->nama_kelas, $kelas->kapasitas);
            }

            // 2. Cek apakah mata kuliah sudah diambil di KRS ini (beda kelas)
            $mkTaken = $krs->krsDetail()->whereHas('kelas', function($q) use ($kelas) {
                $q->where('mata_kuliah_id', $kelas->mata_kuliah_id);
            })->exists();

            if ($mkTaken) {
                throw KrsException::courseAlreadyTaken($kelas->mataKuliah->nama_mk);
            }

            // 3. Cek Batas SKS berdasarkan IPS semester lalu
            $sksSaatIni = $krs->krsDetail->sum(fn($detail) => $detail->kelas->mataKuliah->sks);
            $sksBaru = $kelas->mataKuliah->sks;
            
            // Get mahasiswa from KRS and calculate max SKS based on IPS
            $mahasiswa = $krs->mahasiswa;
            $maxSks = $this->getMaxSksForMahasiswa($mahasiswa);

            if (($sksSaatIni + $sksBaru) > $maxSks) {
                throw KrsException::sksLimitExceeded($sksSaatIni, $sksBaru, $maxSks);
            }

            // Add
            return KrsDetail::create([
                'krs_id' => $krs->id,
                'kelas_id' => $kelasId
            ]);
        });
    }

    public function removeKelas(Krs $krs, $detailId)
    {
        if ($krs->status !== 'draft') {
            throw KrsException::locked();
        }

        $detail = $krs->krsDetail()->findOrFail($detailId);
        $detail->delete();
    }

    public function submitKrs(Krs $krs)
    {
        if ($krs->krsDetail()->count() === 0) {
            throw KrsException::emptyKrs();
        }
        $krs->update(['status' => 'pending']);
    }

    public function approveKrs(Krs $krs)
    {
        if ($krs->status !== 'pending') {
            throw KrsException::invalidStatus($krs->status, 'pending');
        }
        $krs->update(['status' => 'approved']);
    }

    public function rejectKrs(Krs $krs)
    {
        if ($krs->status !== 'pending') {
            throw KrsException::invalidStatus($krs->status, 'pending');
        }
        $krs->update(['status' => 'rejected']);
    }
}
