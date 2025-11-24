<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Models\Role;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Role Baru')
                ->modalHeading('Tambah Role Baru')
                ->modalSubmitActionLabel('Simpan Role')
                ->modalWidth('2xl')
                ->using(function (array $data): \App\Models\Role {
                    return \App\Models\Role::create($data);
                }),
        ];
    }
} 