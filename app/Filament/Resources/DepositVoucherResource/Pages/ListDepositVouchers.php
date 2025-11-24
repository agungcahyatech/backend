<?php

namespace App\Filament\Resources\DepositVoucherResource\Pages;

use App\Filament\Resources\DepositVoucherResource;
use App\Models\DepositVoucher;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDepositVouchers extends ListRecords
{
    protected static string $resource = DepositVoucherResource::class;

    // Optimasi query untuk mengurangi data yang diambil
    protected function getTableQuery(): Builder
    {
        return DepositVoucher::query()
            ->select(['id', 'code', 'amount', 'usage_limit', 'is_active', 'expired_at', 'created_at']);
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
                ->modalHeading('Create New Deposit Voucher')
                ->modalSubmitActionLabel('Create Deposit Voucher')
                ->modalWidth('4xl'),
        ];
    }
} 