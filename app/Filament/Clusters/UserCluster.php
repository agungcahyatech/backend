<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class UserCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Manajemen User';

    protected static ?string $navigationLabel = 'Users';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return \App\Models\User::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
} 