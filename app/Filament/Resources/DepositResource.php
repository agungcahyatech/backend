<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositResource\Pages;
use App\Filament\Clusters\UserCluster;
use App\Models\Deposit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Deposits';

    protected static ?int $navigationSort = 3;

    protected static ?string $cluster = UserCluster::class;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(12)
                    ->schema([
                        // Left Column - Main Deposit Information (8 columns)
                        Forms\Components\Section::make('Deposit Information')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('user_id')
                                            ->label('User')
                                            ->options(User::pluck('name', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->placeholder('Select user'),

                                        Forms\Components\TextInput::make('invoice_id')
                                            ->label('Invoice ID')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->placeholder('e.g., INV-2025-001'),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('payment_method_name')
                                            ->label('Payment Method')
                                            ->required()
                                            ->placeholder('e.g., BCA Virtual Account'),

                                        Forms\Components\Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'pending' => 'Pending',
                                                'success' => 'Success',
                                                'failed' => 'Failed',
                                                'approved' => 'Approved',
                                            ])
                                            ->required()
                                            ->default('pending')
                                            ->placeholder('Select status'),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('amount')
                                            ->label('Requested Amount')
                                            ->numeric()
                                            ->required()
                                            ->prefix('Rp')
                                            ->placeholder('0.00')
                                            ->helperText('Amount requested by user'),

                                        Forms\Components\TextInput::make('final_amount')
                                            ->label('Final Amount')
                                            ->numeric()
                                            ->required()
                                            ->prefix('Rp')
                                            ->placeholder('0.00')
                                            ->helperText('Amount including fees'),
                                    ]),

                                Forms\Components\Textarea::make('payment_url')
                                    ->label('Payment URL')
                                    ->placeholder('https://payment-gateway.com/pay/...')
                                    ->helperText('Payment gateway URL for user to complete payment')
                                    ->columnSpanFull(),

                                Forms\Components\DateTimePicker::make('expired_at')
                                    ->label('Expired At')
                                    ->placeholder('Select expiration date')
                                    ->helperText('When this deposit request expires')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(8),

                        // Right Column - Status and Summary (4 columns)
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Section::make('Status')
                                    ->schema([
                                        Forms\Components\Placeholder::make('status_info')
                                            ->label('Status Information')
                                            ->content(function ($record) {
                                                if (!$record) return 'No deposit data available.';
                                                
                                                $status = $record->status;
                                                $amount = number_format($record->amount, 0, ',', '.');
                                                $finalAmount = number_format($record->final_amount, 0, ',', '.');
                                                
                                                return "Status: {$status}\nRequested: Rp {$amount}\nFinal: Rp {$finalAmount}";
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
                Tables\Columns\TextColumn::make('invoice_id')
                    ->label('Invoice ID')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Requested Amount')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('final_amount')
                    ->label('Final Amount')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method_name')
                    ->label('Payment Method')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                        'approved' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('expired_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'approved' => 'Approved',
                    ]),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('User')
                    ->options(User::pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\Filter::make('expired')
                    ->label('Expired Deposits')
                    ->query(fn ($query) => $query->where('expired_at', '<', now())),

                Tables\Filters\Filter::make('active')
                    ->label('Active Deposits')
                    ->query(fn ($query) => $query->where('expired_at', '>', now())->orWhereNull('expired_at')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Deposit')
                    ->modalSubmitActionLabel('Update Deposit')
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
            'index' => Pages\ListDeposits::route('/'),
        ];
    }
} 