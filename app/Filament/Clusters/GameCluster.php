<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class GameCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Manajemen Game';

    protected static ?string $navigationLabel = 'Games';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return \App\Models\Game::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
} 