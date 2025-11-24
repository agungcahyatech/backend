<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class TransactionCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Manajemen Transaksi';

    protected static ?string $navigationLabel = 'Transactions';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return \App\Models\Transaction::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
} 