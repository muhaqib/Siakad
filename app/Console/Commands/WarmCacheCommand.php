<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

class WarmCacheCommand extends Command
{
    protected $signature = 'cache:warm {--clear : Clear existing cache before warming}';
    protected $description = 'Warm up frequently used caches for better performance';

    public function handle(CacheService $cacheService): int
    {
        if ($this->option('clear')) {
            $this->info('Clearing existing caches...');
            $cacheService->clearAllMasterCache();
        }

        $this->info('Warming up caches...');

        $warmed = $cacheService->warmUp();

        foreach ($warmed as $cache) {
            $this->line("  ✓ Warmed: {$cache}");
        }

        $this->newLine();
        $this->info('Cache warming completed! ' . count($warmed) . ' caches warmed.');

        return Command::SUCCESS;
    }
}
