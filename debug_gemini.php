<?php

use App\Services\AiAdvisorService;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Config;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Checking configuration...\n";
    $apiKey = config('services.gemini.api_key');
    if (empty($apiKey)) {
        echo "WARNING: GEMINI_API_KEY is empty in config. Test will likely fail.\n";
    } else {
        echo "GEMINI_API_KEY is set (length: " . strlen($apiKey) . ")\n";
    }

    echo "Resolving AiAdvisorService...\n";
    $service = app(AiAdvisorService::class);

    echo "Finding student...\n";
    $mahasiswa = Mahasiswa::with('user')->first();
    if (!$mahasiswa) die("No student found.\n");
    
    echo "Testing connection to Gemini (gemini-2.5-flash-lite)...\n";
    $result = $service->chat($mahasiswa, "Halo, tes koneksi. Jawab singkat satu kata: 'Berhasil'.");
    
    echo "Result:\n";
    print_r($result);

} catch (\Throwable $e) {
    echo "ERROR:\n";
    echo $e->getMessage() . "\n";
}
