<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlashSaleResource\Pages;
use App\Filament\Resources\FlashSaleResource\Relations;
use App\Filament\Clusters\PromoCluster;
use App\Models\FlashSale;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FlashSaleResource extends Resource
{
    protected static ?string $model = FlashSale::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationLabel = 'Flash Sales';

    protected static ?int $navigationSort = 2;

    protected static ?string $cluster = PromoCluster::class;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(12)
                    ->schema([
                        // Left Column - Main Flash Sale Information (8 columns)
                        Forms\Components\Section::make('Flash Sale Information')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Sale Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., Flash Sale Gajian'),

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
                                            ->helperText('This flash sale will be hidden if inactive.'),

                                        Forms\Components\Placeholder::make('sale_info')
                                            ->label('Sale Information')
                                            ->content(function ($record) {
                                                if (!$record) return 'No sale data available.';
                                                
                                                $productCount = $record->products()->count();
                                                $isActive = $record->is_active ? 'Active' : 'Inactive';
                                                $status = now()->between($record->start_date, $record->end_date) ? 'Running' : 'Not Running';
                                                
                                                return "Products: {$productCount}\nStatus: {$isActive}\nPeriod: {$status}";
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Sale Name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration')
                    ->formatStateUsing(function ($record) {
                        if (!$record->start_date || !$record->end_date) return 'N/A';
                        
                        $start = $record->start_date;
                        $end = $record->end_date;
                        $diff = $start->diffInHours($end);
                        
                        if ($diff < 24) {
                            return $diff . ' hours';
                        } else {
                            $days = $start->diffInDays($end);
                            return $days . ' days';
                        }
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function ($record) {
                        if (!$record->is_active) return 'Inactive';
                        
                        $now = now();
                        if ($now < $record->start_date) return 'Upcoming';
                        if ($now > $record->end_date) return 'Ended';
                        return 'Running';
                    })
                    ->badge()
                    ->color(function ($record) {
                        if (!$record->is_active) return 'danger';
                        
                        $now = now();
                        if ($now < $record->start_date) return 'info';
                        if ($now > $record->end_date) return 'gray';
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
                    ->placeholder('All Flash Sales')
                    ->trueLabel('Active Flash Sales')
                    ->falseLabel('Inactive Flash Sales'),

                Tables\Filters\Filter::make('upcoming')
                    ->label('Upcoming Sales')
                    ->query(fn ($query) => $query->where('start_date', '>', now())),

                Tables\Filters\Filter::make('running')
                    ->label('Currently Running')
                    ->query(fn ($query) => $query->where('start_date', '<=', now())->where('end_date', '>=', now())),

                Tables\Filters\Filter::make('ended')
                    ->label('Ended Sales')
                    ->query(fn ($query) => $query->where('end_date', '<', now())),
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
            Relations\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlashSales::route('/'),
            'create' => Pages\CreateFlashSale::route('/create'),
            'edit' => Pages\EditFlashSale::route('/{record}/edit'),
        ];
    }
} 