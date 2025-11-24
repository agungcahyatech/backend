<?php

namespace App\Filament\Resources\DepositVoucherResource\Pages;

use App\Filament\Resources\DepositVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepositVoucher extends EditRecord
{
    protected static string $resource = DepositVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Deposit Voucher updated successfully';
    }
} 