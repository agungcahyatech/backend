<?php

namespace App\Console\Commands;

use App\Services\FilamentCacheService;
use Illuminate\Console\Command;

class ClearFilamentCache extends Command
{
    protected $signature = 'filament:clear-cache {--warmup : Warm up cache after clearing}';
    protected $description = 'Clear all Filament-related cache for better performance';

    public function handle(FilamentCacheService $cacheService)
    {
        $this->info('Clearing Filament cache...');
        
        $cacheService->clearAllCache();
        
        $this->info('Filament cache cleared successfully!');
        
        if ($this->option('warmup')) {
            $this->info('Warming up cache...');
            $cacheService->warmUpCache();
            $this->info('Cache warmed up successfully!');
        }
        
        return 0;
    }
}