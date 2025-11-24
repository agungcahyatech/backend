<?php

namespace App\Filament\Resources\FlashSaleResource\Relations;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Product;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->options(Product::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->placeholder('Select product'),

                Forms\Components\TextInput::make('discounted_price')
                    ->label('Discounted Price')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->placeholder('0.00')
                    ->helperText('Price after discount'),

                Forms\Components\TextInput::make('stock')
                    ->label('Limited Stock')
                    ->numeric()
                    ->placeholder('Leave empty for unlimited')
                    ->helperText('Optional: Set limited stock for this sale'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('base_price')
                    ->label('Original Price')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.discounted_price')
                    ->label('Discounted Price')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.stock')
                    ->label('Sale Stock')
                    ->numeric()
                    ->sortable()
                    ->placeholder('Unlimited'),

                Tables\Columns\TextColumn::make('provider_sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable()
                    ->limit(20),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Product Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Product Status')
                    ->placeholder('All Products')
                    ->trueLabel('Active Products')
                    ->falseLabel('Inactive Products'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Product')
                            ->options(Product::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->placeholder('Select product'),

                        Forms\Components\TextInput::make('discounted_price')
                            ->label('Discounted Price')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->placeholder('0.00')
                            ->helperText('Price after discount'),

                        Forms\Components\TextInput::make('stock')
                            ->label('Limited Stock')
                            ->numeric()
                            ->placeholder('Leave empty for unlimited')
                            ->helperText('Optional: Set limited stock for this sale'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form(fn (Tables\Actions\EditAction $action): array => [
                        Forms\Components\TextInput::make('discounted_price')
                            ->label('Discounted Price')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->placeholder('0.00')
                            ->helperText('Price after discount'),

                        Forms\Components\TextInput::make('stock')
                            ->label('Limited Stock')
                            ->numeric()
                            ->placeholder('Leave empty for unlimited')
                            ->helperText('Optional: Set limited stock for this sale'),
                    ]),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
} 