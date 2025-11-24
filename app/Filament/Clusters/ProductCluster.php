<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class ProductCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Manajemen Products';

    protected static ?string $navigationLabel = 'Products';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return \App\Models\Product::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
} 