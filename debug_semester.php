<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Mahasiswa;
use App\Models\TahunAkademik;

$output = "";

// Get first mahasiswa
$mahasiswa = Mahasiswa::first();
$output .= "Mahasiswa: " . ($mahasiswa->user->name ?? 'Unknown') . "\n";
$output .= "ID: " . $mahasiswa->id . "\n\n";

// Get all approved KRS
$allKrs = $mahasiswa->krs()
    ->where('status', 'approved')
    ->with(['tahunAkademik', 'krsDetail.kelas'])
    ->get();

$output .= "=== KRS Records ===\n";
foreach ($allKrs as $krs) {
    $output .= "KRS #{$krs->id} - TahunAkademik ID: {$krs->tahun_akademik_id} - {$krs->tahunAkademik->display_name}\n";
    $output .= "  KRS Detail Count: " . $krs->krsDetail->count() . "\n";
    foreach ($krs->krsDetail as $detail) {
        $kelas = $detail->kelas;
        $output .= "    - Kelas ID: {$detail->kelas_id}, Kelas Tahun: " . ($kelas->tahun_akademik_id ?? 'null') . "\n";
    }
}

$output .= "\n=== Tahun Akademik ===\n";
$tahunAkademiks = TahunAkademik::orderBy('id')->get();
foreach ($tahunAkademiks as $ta) {
    $output .= "ID: {$ta->id} - {$ta->display_name} - Active: " . ($ta->is_active ? 'Yes' : 'No') . "\n";
}

file_put_contents('debug_output.txt', $output);
echo "Output written to debug_output.txt\n";
