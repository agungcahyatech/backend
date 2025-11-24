<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class PromoCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Promo';

    protected static ?string $navigationLabel = 'Promotions';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return \App\Models\Voucher::count() + \App\Models\FlashSale::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
} 