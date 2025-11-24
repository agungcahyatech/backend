<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Clusters\ProductCluster;
use App\Models\Product;
use App\Models\Game;
use App\Models\ProductCategory;
use App\Filament\Forms\Components\CloudinaryFileUpload;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationLabel = 'Products';

    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = ProductCluster::class;

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
                        // Left Column - Main Product Information (8 columns)
                        Forms\Components\Section::make('Product Information')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Product Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('e.g., 100 Diamonds'),

                                        Forms\Components\TextInput::make('provider_sku')
                                            ->label('Provider SKU')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('e.g., ML100'),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('provider')
                                            ->label('Provider')
                                            ->options(Product::getAvailableProviders())
                                            ->required()
                                            ->searchable()
                                            ->placeholder('Select a provider')
                                            ->helperText('Choose the provider for this product. MANUAL will be processed by admin.'),

                                        Forms\Components\TextInput::make('base_price')
                                            ->label('Base Price')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required()
                                            ->placeholder('0.00'),
                                    ]),

                                Forms\Components\RichEditor::make('description')
                                    ->label('Description')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'strike',
                                        'link',
                                        'bulletList',
                                        'orderedList',
                                        'blockquote',
                                        'codeBlock',
                                        'h2',
                                        'h3',
                                        'h4',
                                    ])
                                    ->placeholder('Enter product description...')
                                    ->columnSpanFull(),

                                CloudinaryFileUpload::make('icon_path')
                                    ->label('Product Icon')
                                    ->image()
                                    ->imageEditor()
                                    ->imageCropAspectRatio('1:1')
                                    ->imageResizeTargetWidth('64')
                                    ->imageResizeTargetHeight('64')
                                    ->directory('products/icons')
                                    ->helperText('Upload icon for the product. Recommended size: 64x64px.'),
                            ])
                            ->columnSpan(8),

                        // Right Column - Status and Associations (4 columns)
                        Forms\Components\Grid::make(1)
                            ->schema([
                                // Status Card
                                Forms\Components\Section::make('Status')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Active')
                                            ->default(true)
                                            ->helperText('This product will be hidden if inactive.'),

                                        Forms\Components\TextInput::make('display_order')
                                            ->label('Display Order')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Order for displaying products. Lower numbers appear first.'),
                                    ]),

                                // Associations Card
                                Forms\Components\Section::make('Associations')
                                    ->schema([
                                        Forms\Components\Select::make('game_id')
                                            ->label('Game')
                                            ->options(Game::where('is_active', true)->pluck('name', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->placeholder('Select a game')
                                            ->live()
                                            ->afterStateUpdated(fn (callable $set) => $set('product_category_id', null)),

                                        Forms\Components\Select::make('product_category_id')
                                            ->label('Product Category')
                                            ->options(function (callable $get) {
                                                $gameId = $get('game_id');
                                                if (!$gameId) {
                                                    return [];
                                                }
                                                return ProductCategory::where('game_id', $gameId)
                                                    ->where('is_active', true)
                                                    ->pluck('name', 'id');
                                            })
                                            ->searchable()
                                            ->required()
                                            ->placeholder('Select a category')
                                            ->disabled(fn (callable $get) => !$get('game_id')),
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
                Tables\Columns\ImageColumn::make('icon_path')
                    ->label('Image')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('game.name')
                    ->label('Game')
                    ->sortable()
                    ->searchable()
                    ->limit(30),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Visibility')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('base_price')
                    ->label('Price')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('provider_sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable()
                    ->limit(20),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Visibility')
                    ->placeholder('All Products')
                    ->trueLabel('Visible Products')
                    ->falseLabel('Hidden Products'),

                Tables\Filters\SelectFilter::make('game_id')
                    ->label('Game')
                    ->options(function () {
                        return cache()->remember('filament.product_filter.games', 600, function () {
                            return Game::where('is_active', true)->pluck('name', 'id');
                        });
                    })
                    ->searchable(),

                Tables\Filters\SelectFilter::make('provider')
                    ->label('Provider')
                    ->options(Product::getAvailableProviders()),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
} 