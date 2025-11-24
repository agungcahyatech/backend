<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositVoucherResource\Pages;
use App\Filament\Clusters\UserCluster;
use App\Models\DepositVoucher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepositVoucherResource extends Resource
{
    protected static ?string $model = DepositVoucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Deposit Vouchers';

    protected static ?int $navigationSort = 4;

    protected static ?string $cluster = UserCluster::class;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(12)
                    ->schema([
                        // Left Column - Main Voucher Information (8 columns)
                        Forms\Components\Section::make('Voucher Information')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('code')
                                            ->label('Voucher Code')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->placeholder('e.g., DEPOSIT50'),

                                        Forms\Components\TextInput::make('amount')
                                            ->label('Voucher Amount')
                                            ->numeric()
                                            ->required()
                                            ->prefix('Rp')
                                            ->placeholder('0.00')
                                            ->helperText('Amount of balance this voucher provides'),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('usage_limit')
                                            ->label('Usage Limit')
                                            ->numeric()
                                            ->default(1)
                                            ->placeholder('1')
                                            ->helperText('How many times this voucher can be used'),

                                        Forms\Components\DateTimePicker::make('expired_at')
                                            ->label('Expired At')
                                            ->placeholder('Select expiration date')
                                            ->helperText('When this voucher expires'),
                                    ]),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->helperText('This voucher will be hidden if inactive.')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(8),

                        // Right Column - Status and Usage (4 columns)
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Section::make('Status')
                                    ->schema([
                                        Forms\Components\Placeholder::make('usage_info')
                                            ->label('Usage Information')
                                            ->content(function ($record) {
                                                if (!$record) return 'No voucher data available.';
                                                
                                                $usageCount = $record->usages()->count();
                                                $usageLimit = $record->usage_limit;
                                                $remaining = $usageLimit - $usageCount;
                                                $isActive = $record->is_active ? 'Active' : 'Inactive';
                                                $isExpired = $record->expired_at && now()->isAfter($record->expired_at) ? 'Expired' : 'Valid';
                                                
                                                return "Status: {$isActive}\nPeriod: {$isExpired}\nUsed: {$usageCount}/{$usageLimit}\nRemaining: {$remaining}";
                                            })
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(4),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Usage Limit')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('usages_count')
                    ->label('Used')
                    ->counts('usages')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expired_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function ($record) {
                        if (!$record->is_active) return 'Inactive';
                        if ($record->expired_at && now()->isAfter($record->expired_at)) return 'Expired';
                        if ($record->usages()->count() >= $record->usage_limit) return 'Used Up';
                        return 'Active';
                    })
                    ->badge()
                    ->color(function ($record) {
                        if (!$record->is_active) return 'danger';
                        if ($record->expired_at && now()->isAfter($record->expired_at)) return 'gray';
                        if ($record->usages()->count() >= $record->usage_limit) return 'warning';
                        return 'success';
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All Vouchers')
                    ->trueLabel('Active Vouchers')
                    ->falseLabel('Inactive Vouchers'),

                Tables\Filters\Filter::make('expired')
                    ->label('Expired Vouchers')
                    ->query(fn ($query) => $query->where('expired_at', '<', now())),

                Tables\Filters\Filter::make('active_period')
                    ->label('Currently Valid')
                    ->query(fn ($query) => $query->where('expired_at', '>', now())->orWhereNull('expired_at')),

                Tables\Filters\Filter::make('used_up')
                    ->label('Used Up Vouchers')
                    ->query(fn ($query) => $query->whereRaw('(SELECT COUNT(*) FROM deposit_voucher_usages WHERE deposit_voucher_id = deposit_vouchers.id) >= usage_limit')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Deposit Voucher')
                    ->modalSubmitActionLabel('Update Deposit Voucher')
                    ->modalWidth('4xl'),
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
            'index' => Pages\ListDepositVouchers::route('/'),
        ];
    }
} 