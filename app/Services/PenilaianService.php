<?php

namespace App\Services;

use App\Models\Nilai;
use Illuminate\Support\Facades\DB;
use Exception;

class PenilaianService
{
    public function inputNilai($mahasiswaId, $kelasId, $nilaiAngka)
    {
        // Cari konversi
        $konversi = $this->getNilaiHuruf($nilaiAngka);

        return Nilai::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id'     => $kelasId,
            ],
            [
                'nilai_angka' => $nilaiAngka,
                'nilai_huruf' => $konversi['huruf'],
            ]
        );
    }

    private function getNilaiHuruf($angka)
    {
        $rules = config('siakad.nilai_konversi');
        
        foreach ($rules as $rule) {
            if ($angka >= $rule['min'] && $angka <= $rule['max']) {
                return $rule;
            }
        }
        
        // Fallback default E
        return ['huruf' => 'E', 'bobot' => 0];
    }

    public function bulkInputNilai($kelasId, array $dataNilai)
    {
        return DB::transaction(function () use ($kelasId, $dataNilai) {
            $updated = 0;
            foreach ($dataNilai as $mahasiswaId => $nilaiAngka) {
                if (is_null($nilaiAngka)) continue;
                
                $this->inputNilai($mahasiswaId, $kelasId, $nilaiAngka);
                $updated++;
            }
            return $updated;
        });
    }
}
