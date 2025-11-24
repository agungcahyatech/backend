<?php

namespace App\Filament\Resources\GameConfigurationResource\Pages;

use App\Filament\Resources\GameConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGameConfiguration extends EditRecord
{
    protected static string $resource = GameConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 