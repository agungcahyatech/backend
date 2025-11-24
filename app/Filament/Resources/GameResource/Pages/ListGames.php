<?php

namespace App\Filament\Resources\GameResource\Pages;

use App\Filament\Resources\GameResource;
use App\Models\Game;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListGames extends ListRecords
{
    protected static string $resource = GameResource::class;

    // Optimasi query dengan eager loading dan select specific columns
    protected function getTableQuery(): Builder
    {
        return Game::query()
            ->with(['category:id,name', 'gameConfiguration:id,name'])
            ->select(['id', 'name', 'slug', 'image_thumbnail_path', 'category_id', 'game_configuration_id', 'developer', 'is_active', 'is_popular', 'display_order']);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 