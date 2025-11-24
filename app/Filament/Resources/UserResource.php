<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Clusters\UserCluster;
use App\Models\User;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = UserCluster::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pengguna')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('no_handphone')
                            ->label('Nomor Handphone')
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->helperText('Minimal 8 karakter'),

                        TextInput::make('balance')
                            ->label('Saldo')
                            ->numeric()
                            ->prefix('Rp ')
                            ->default(0),

                        Select::make('role_id')
                            ->label('Role')
                            ->options(Role::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        TextInput::make('api_key')
                            ->label('API Key')
                            ->maxLength(255)
                            ->helperText('Akan di-generate otomatis')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(fn () => 'api_' . Str::random(32))
                            ->afterStateHydrated(function ($state, $context) {
                                if ($context === 'create' && empty($state)) {
                                    return 'api_' . Str::random(32);
                                }
                                return $state;
                            }),
                    ])
                    ->columns(2),

                Section::make('Pengaturan')
                    ->schema([
                        Toggle::make('email_verified_at')
                            ->label('Email Terverifikasi')
                            ->default(false),
                        
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('no_handphone')
                    ->label('No. HP')
                    ->searchable(),

                TextColumn::make('balance')
                    ->label('Saldo')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('role.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'primary',
                        'user' => 'success',
                        'moderator' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Terakhir Diupdate')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role_id')
                    ->label('Filter by Role')
                    ->options(Role::all()->pluck('name', 'id')),

                Filter::make('has_balance')
                    ->label('Memiliki Saldo')
                    ->query(fn ($query) => $query->where('balance', '>', 0)),

                Filter::make('no_balance')
                    ->label('Tidak Memiliki Saldo')
                    ->query(fn ($query) => $query->where('balance', '=', 0)),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit User')
                    ->modalSubmitActionLabel('Update User')
                    ->modalWidth('4xl')
                    ->slideOver()
                    ->using(function (array $data, \App\Models\User $record): \App\Models\User {
                        if (empty($data['api_key'])) {
                            $data['api_key'] = 'api_' . Str::random(32);
                        }
                        $record->update($data);
                        return $record;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
} 