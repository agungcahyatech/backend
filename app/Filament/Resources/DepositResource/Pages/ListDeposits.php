<?php

namespace App\Filament\Resources\DepositResource\Pages;

use App\Filament\Resources\DepositResource;
use App\Models\Deposit;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDeposits extends ListRecords
{
    protected static string $resource = DepositResource::class;

    // Optimasi query dengan eager loading
    protected function getTableQuery(): Builder
    {
        return Deposit::query()
            ->with(['user:id,username,email'])
            ->select(['id', 'user_id', 'amount', 'status', 'expired_at', 'created_at', 'updated_at']);
    }

    // Pagination yang lebih efisien
    public function getTableRecordsPerPage(): int|string|null
    {
        return 25;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Create New Deposit')
                ->modalSubmitActionLabel('Create Deposit')
                ->modalWidth('4xl'),
        ];
    }
} 