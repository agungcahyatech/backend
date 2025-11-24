<?php

namespace App\Filament\Resources\DepositVoucherResource\Pages;

use App\Filament\Resources\DepositVoucherResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDepositVoucher extends CreateRecord
{
    protected static string $resource = DepositVoucherResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Deposit Voucher created successfully';
    }
} 