<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class FilamentCacheService
{
    protected int $defaultTTL = 600; // 10 minutes

    /**
     * Cache dashboard statistics
     */
    public function cacheDashboardStats(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return Cache::remember(
            "dashboard.{$key}",
            $ttl ?? $this->defaultTTL,
            $callback
        );
    }

    /**
     * Cache filter options
     */
    public function cacheFilterOptions(string $resource, string $filter, callable $callback, ?int $ttl = null): mixed
    {
        return Cache::remember(
            "filament.{$resource}.filter.{$filter}",
            $ttl ?? $this->defaultTTL,
            $callback
        );
    }

    /**
     * Cache model collections
     */
    public function cacheModelData(string $model, string $key, callable $callback, ?int $ttl = null): mixed
    {
        return Cache::remember(
            "model.{$model}.{$key}",
            $ttl ?? $this->defaultTTL,
            $callback
        );
    }

    /**
     * Clear all Filament related cache
     */
    public function clearAllCache(): void
    {
        $patterns = [
            'dashboard.*',
            'filament.*',
            'model.*',
            'product.available_providers'
        ];

        foreach ($patterns as $pattern) {
            $this->clearCacheByPattern($pattern);
        }
    }

    /**
     * Clear cache by pattern
     */
    protected function clearCacheByPattern(string $pattern): void
    {
        try {
            // Try Redis if available
            if (config('cache.default') === 'redis' && method_exists(Cache::getStore(), 'getRedis')) {
                $redis = Cache::getStore()->getRedis();
                $keys = $redis->keys($pattern);
                foreach ($keys as $key) {
                    Cache::forget($key);
                }
            } else {
                // For database or file cache drivers, clear specific keys
                $specificKeys = [
                    'dashboard.product_stats',
                    'dashboard.additional_stats',
                    'filament.product_filter.games',
                    'filament.user_filter.roles',
                    'product.available_providers',
                    'game_configurations'
                ];
                
                foreach ($specificKeys as $key) {
                    Cache::forget($key);
                }
            }
        } catch (\Exception $e) {
            // Fallback: Clear all cache
            Cache::flush();
        }
    }

    /**
     * Warm up commonly used cache
     */
    public function warmUpCache(): void
    {
        // Warm up dashboard stats
        $this->cacheDashboardStats('product_stats', function () {
            return [
                'total' => \App\Models\Product::count(),
                'active' => \App\Models\Product::where('is_active', true)->count(),
                'average_price' => \App\Models\Product::where('is_active', true)->avg('base_price') ?? 0,
            ];
        });

        // Warm up filter options
        $this->cacheFilterOptions('product', 'games', function () {
            return \App\Models\Game::where('is_active', true)->pluck('name', 'id');
        });

        // Warm up provider options
        \App\Models\Product::getAvailableProviders();
    }
}