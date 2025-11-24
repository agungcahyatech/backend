<?php

namespace App\Filament\Resources\DepositResource\Pages;

use App\Filament\Resources\DepositResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDeposit extends CreateRecord
{
    protected static string $resource = DepositResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Deposit created successfully';
    }
} 