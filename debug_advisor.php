<?php

use App\Models\User;
use App\Models\Mahasiswa;
use App\Services\AiAdvisorService;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Finding real student...\n";
    $mahasiswa = Mahasiswa::where('nim', '2303113649')->first();
    
    if (!$mahasiswa) {
        echo "Student not found!, using finding any student\n";
        $mahasiswa = Mahasiswa::with('user')->first();
    }
    
    if (!$mahasiswa) {
        die("No student found at all.\n");
    }

    echo "Student found: " . $mahasiswa->nim . " - " . ($mahasiswa->user->name ?? 'No User') . "\n";
    echo "Prodi: " . ($mahasiswa->prodi->nama ?? 'No Prodi') . "\n";

    echo "Resolving Service...\n";
    $service = app(AiAdvisorService::class);

    echo "calling chat()...\n";
    $result = $service->chat($mahasiswa, "Sebutkan total SKS kelulusan prodi Sistem Informasi.");
    
    echo "Result:\n";
    print_r($result);

} catch (\Throwable $e) {
    echo "ERROR CAUGHT:\n";
    echo get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
