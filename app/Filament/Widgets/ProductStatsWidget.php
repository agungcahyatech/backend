<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\User;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class ProductStatsWidget extends BaseWidget
{
    // Polling interval untuk refresh data secara berkala (dalam detik)
    protected static ?string $pollingInterval = '30s';
    
    // Cache TTL dalam menit
    protected int $cacheMinutes = 10;

    protected function getStats(): array
    {
        // Cache product stats untuk mengurangi query database
        $productStats = Cache::remember('dashboard.product_stats', $this->cacheMinutes * 60, function () {
            return [
                'total' => Product::count(),
                'active' => Product::where('is_active', true)->count(),
                'average_price' => Product::where('is_active', true)->avg('base_price') ?? 0,
            ];
        });

        // Cache additional stats
        $additionalStats = Cache::remember('dashboard.additional_stats', $this->cacheMinutes * 60, function () {
            return [
                'total_users' => User::count(),
                'total_transactions' => Transaction::count(),
                'revenue_today' => Transaction::whereDate('created_at', today())
                    ->where('status', 'success')
                    ->selectRaw('SUM(base_price * quantity) as total_revenue')
                    ->value('total_revenue') ?? 0,
            ];
        });

        return [
            Stat::make('Total Products', $productStats['total'])
                ->description('All products in the system')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),

            Stat::make('Active Products', $productStats['active'])
                ->description('Products currently available')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),

            Stat::make('Average Price', 'Rp ' . number_format($productStats['average_price'], 0))
                ->description('Average price of active products')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),

            Stat::make('Total Users', $additionalStats['total_users'])
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Transactions', $additionalStats['total_transactions'])
                ->description('All time transactions')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('secondary'),

            Stat::make('Today Revenue', 'Rp ' . number_format($additionalStats['revenue_today'], 0))
                ->description('Revenue from today')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
} 