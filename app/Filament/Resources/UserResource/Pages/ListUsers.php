<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    // Optimasi query dengan eager loading untuk role
    protected function getTableQuery(): Builder
    {
        return User::query()
            ->with(['role:id,name'])
            ->select(['id', 'name', 'username', 'email', 'no_handphone', 'role_id', 'balance', 'is_active', 'created_at']);
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
                ->label('Tambah User Baru')
                ->modalHeading('Tambah User Baru')
                ->modalSubmitActionLabel('Simpan User')
                ->modalWidth('4xl')
                ->slideOver()
                ->using(function (array $data): \App\Models\User {
                    if (empty($data['api_key'])) {
                        $data['api_key'] = 'api_' . Str::random(32);
                    }
                    return \App\Models\User::create($data);
                }),
        ];
    }
} 