<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Filament\Clusters\PromoCluster;
use App\Models\Voucher;
use App\Models\Game;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Vouchers';

    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = PromoCluster::class;

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
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->placeholder('e.g., SAVE50'),

                                        Forms\Components\Select::make('discount_type')
                                            ->label('Discount Type')
                                            ->options([
                                                'percentage' => 'Percentage',
                                                'flat' => 'Flat Amount',
                                            ])
                                            ->required()
                                            ->default('flat')
                                            ->placeholder('Select discount type'),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('discount_value')
                                            ->label('Discount Value')
                                            ->numeric()
                                            ->required()
                                            ->placeholder('0.00')
                                            ->helperText('Enter percentage (e.g., 10) or flat amount (e.g., 50000)'),

                                        Forms\Components\TextInput::make('min_purchase')
                                            ->label('Minimum Purchase')
                                            ->numeric()
                                            ->default(0)
                                            ->placeholder('0.00')
                                            ->helperText('Minimum purchase amount required to use this voucher'),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('start_date')
                                            ->label('Start Date')
                                            ->required()
                                            ->placeholder('Select start date'),

                                        Forms\Components\DateTimePicker::make('end_date')
                                            ->label('End Date')
                                            ->required()
                                            ->placeholder('Select end date'),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('total_usage_limit')
                                            ->label('Total Usage Limit')
                                            ->numeric()
                                            ->default(1)
                                            ->placeholder('1')
                                            ->helperText('Maximum times this voucher can be used'),

                                        Forms\Components\TextInput::make('user_usage_limit')
                                            ->label('User Usage Limit')
                                            ->numeric()
                                            ->default(1)
                                            ->placeholder('1')
                                            ->helperText('Maximum times a user can use this voucher'),
                                    ]),

                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->required()
                                    ->placeholder('Enter voucher description...')
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('games')
                                    ->label('Applicable Games')
                                    ->options(Game::where('is_active', true)->pluck('name', 'id'))
                                    ->multiple()
                                    ->searchable()
                                    ->placeholder('Select applicable games')
                                    ->helperText('Select games where this voucher can be used')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(8),

                        // Right Column - Status (4 columns)
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Section::make('Status')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Active')
                                            ->default(true)
                                            ->helperText('This voucher will be hidden if inactive.'),

                                        Forms\Components\Placeholder::make('usage_info')
                                            ->label('Usage Information')
                                            ->content(function ($record) {
                                                if (!$record) return 'No usage data available.';
                                                
                                                $totalUsage = $record->usages()->count();
                                                $totalLimit = $record->total_usage_limit;
                                                $remaining = $totalLimit - $totalUsage;
                                                
                                                return "Used: {$totalUsage}/{$totalLimit} ({$remaining} remaining)";
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

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('discount_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'info',
                        'flat' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('discount_value')
                    ->label('Value')
                    ->formatStateUsing(function ($record) {
                        if ($record->discount_type === 'percentage') {
                            return $record->discount_value . '%';
                        }
                        return 'Rp ' . number_format($record->discount_value, 0, ',', '.');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('min_purchase')
                    ->label('Min Purchase')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_usage_limit')
                    ->label('Usage Limit')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('usages_count')
                    ->label('Used')
                    ->counts('usages')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
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

                Tables\Filters\SelectFilter::make('discount_type')
                    ->label('Discount Type')
                    ->options([
                        'percentage' => 'Percentage',
                        'flat' => 'Flat Amount',
                    ]),

                Tables\Filters\Filter::make('expired')
                    ->label('Expired Vouchers')
                    ->query(fn ($query) => $query->where('end_date', '<', now())),

                Tables\Filters\Filter::make('active_period')
                    ->label('Currently Active')
                    ->query(fn ($query) => $query->where('start_date', '<=', now())->where('end_date', '>=', now())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
} 