<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Page;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    // Optimasi query dengan select column yang diperlukan saja
    protected function getTableQuery(): Builder
    {
        return Page::query()
            ->select(['id', 'title', 'slug', 'content', 'is_published', 'created_at', 'updated_at']);
    }

    // Pagination yang lebih efisien
    public function getTableRecordsPerPage(): int|string|null
    {
        return 20;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Create New Page')
                ->modalDescription('Add a new page with title, content, and publish settings.')
                ->modalSubmitActionLabel('Create Page'),
        ];
    }
} 