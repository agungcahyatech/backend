<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use App\Filament\Forms\Components\CloudinaryFileUpload;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Manajemen Website';

    protected static ?string $navigationLabel = 'Payment Methods';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                CloudinaryFileUpload::make('image_path')
                    ->label('Payment Method Logo')
                    ->image()
                    ->imageEditor()
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('200')
                    ->imageResizeTargetHeight('200')
                    ->helperText('Upload the payment method logo. Recommended size: 200x200px.'),

                Forms\Components\TextInput::make('name')
                    ->label('Display Name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('The name that will be displayed to users (e.g., "QRIS", "BCA Virtual Account")'),

                Forms\Components\Select::make('provider')
                    ->label('Provider')
                    ->required()
                    ->options(function () {
                        $providers = [];
                        
                        // Add configured providers from settings
                        if (!empty(\App\Models\Setting::getValue('tokopay_merchant_id')) && !empty(\App\Models\Setting::getValue('tokopay_secret_key'))) {
                            $providers['tokopay'] = 'Tokopay';
                        }
                        if (!empty(\App\Models\Setting::getValue('duitku_merchant_id')) && !empty(\App\Models\Setting::getValue('duitku_api_key'))) {
                            $providers['duitku'] = 'Duitku';
                        }

                        // Add manual option
                        $providers['manual'] = 'Manual (Bank Transfer)';
                        
                        return $providers;
                    })
                    ->helperText('Select payment gateway provider. Only configured providers are shown.'),

                Forms\Components\TextInput::make('code')
                    ->label('Provider Code')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Unique code from the provider (e.g., "QRIS", "BCAVA")'),

                Forms\Components\Select::make('group')
                    ->label('Payment Group')
                    ->required()
                    ->options([
                        'QRIS' => 'QRIS',
                        'E-Wallet' => 'E-Wallet',
                        'Virtual Account' => 'Virtual Account',
                        'Convenience Store' => 'Convenience Store',
                        'Pulsa' => 'Pulsa',
                        'Bank Transfer' => 'Bank Transfer',
                        'Credit Card' => 'Credit Card',
                        'Other' => 'Other',
                    ])
                    ->helperText('Group for frontend organization'),

                Forms\Components\Select::make('type')
                    ->label('Payment Type')
                    ->required()
                    ->options([
                        'qris' => 'QRIS',
                        'e-wallet' => 'E-Wallet',
                        'va' => 'Virtual Account',
                        'convenience_store' => 'Convenience Store',
                        'pulsa' => 'Pulsa',
                        'bank_transfer' => 'Bank Transfer',
                        'credit_card' => 'Credit Card',
                        'other' => 'Other',
                    ])
                    ->helperText('Type of payment method'),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('fee_flat')
                            ->label('Flat Fee')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->helperText('Fixed fee amount'),

                        Forms\Components\TextInput::make('fee_percent')
                            ->label('Percentage Fee')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->helperText('Percentage fee'),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('min_amount')
                            ->label('Minimum Amount')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->helperText('Minimum transaction amount'),

                        Forms\Components\TextInput::make('max_amount')
                            ->label('Maximum Amount')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->helperText('Maximum transaction amount (0 = unlimited)'),
                    ]),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Enable to make this payment method available'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Logo')
                    ->circular()
                    ->size(50),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('provider')
                    ->label('Provider')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('group')
                    ->label('Group')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fee_flat')
                    ->label('Flat Fee')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('fee_percent')
                    ->label('Fee %')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('min_amount')
                    ->label('Min Amount')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('max_amount')
                    ->label('Max Amount')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All Payment Methods')
                    ->trueLabel('Active Methods')
                    ->falseLabel('Inactive Methods'),

                Tables\Filters\SelectFilter::make('group')
                    ->label('Payment Group')
                    ->options([
                        'QRIS' => 'QRIS',
                        'E-Wallet' => 'E-Wallet',
                        'Virtual Account' => 'Virtual Account',
                        'Convenience Store' => 'Convenience Store',
                        'Pulsa' => 'Pulsa',
                        'Bank Transfer' => 'Bank Transfer',
                        'Credit Card' => 'Credit Card',
                        'Other' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Payment Type')
                    ->options([
                        'qris' => 'QRIS',
                        'e-wallet' => 'E-Wallet',
                        'va' => 'Virtual Account',
                        'convenience_store' => 'Convenience Store',
                        'pulsa' => 'Pulsa',
                        'bank_transfer' => 'Bank Transfer',
                        'credit_card' => 'Credit Card',
                        'other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Payment Method')
                    ->modalDescription('Update payment method information and settings.')
                    ->modalSubmitActionLabel('Update Payment Method'),
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
            'index' => Pages\ListPaymentMethods::route('/'),
        ];
    }
} 