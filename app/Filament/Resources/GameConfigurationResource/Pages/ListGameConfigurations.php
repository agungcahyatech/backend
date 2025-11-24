<?php

namespace App\Filament\Resources\GameConfigurationResource\Pages;

use App\Filament\Resources\GameConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGameConfigurations extends ListRecords
{
    protected static string $resource = GameConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 