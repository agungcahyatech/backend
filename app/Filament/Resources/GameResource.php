<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameResource\Pages;
use App\Filament\Clusters\GameCluster;
use App\Models\Game;
use App\Models\Category;
use App\Models\GameConfiguration;
use App\Filament\Forms\Components\CloudinaryFileUpload;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Games';

    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = GameCluster::class;

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
                        // Left Column - Main Game Information (8 columns)
                        Forms\Components\Section::make('Game Information')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Game Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $state, callable $set) {
                                                $set('slug', Str::slug($state));
                                            })
                                            ->placeholder('e.g., Mobile Legends'),

                                        Forms\Components\TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->placeholder('e.g., mobile-legends'),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('category_id')
                                            ->label('Category')
                                            ->options(Category::where('is_active', true)->pluck('name', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->placeholder('Select a category'),

                                        Forms\Components\Select::make('game_configuration_id')
                                            ->label('Game Configuration')
                                            ->options(GameConfiguration::where('is_active', true)->pluck('name', 'id'))
                                            ->searchable()
                                            ->placeholder('Select a game configuration')
                                            ->helperText('Choose the configuration template for this game'),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('developer')
                                            ->label('Developer')
                                            ->maxLength(255)
                                            ->placeholder('e.g., Moonton'),

                                        Forms\Components\TextInput::make('brand')
                                            ->label('Brand')
                                            ->maxLength(255)
                                            ->placeholder('e.g., Moonton'),
                                    ]),

                                Forms\Components\TextInput::make('allowed_region')
                                    ->label('Allowed Region')
                                    ->maxLength(255)
                                    ->placeholder('e.g., Global, Indonesia, Asia')
                                    ->helperText('Specify allowed regions for this game'),

                                Forms\Components\RichEditor::make('description')
                                    ->label('Description')
                                    ->required()
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'strike',
                                        'link',
                                        'bulletList',
                                        'orderedList',
                                        'blockquote',
                                        'codeBlock',
                                    ])
                                    ->placeholder('Enter game description...')
                                    ->columnSpanFull(),

                                CloudinaryFileUpload::make('image_thumbnail_path')
                                    ->label('Thumbnail Image')
                                    ->image()
                                    ->imageEditor()
                                    ->imageCropAspectRatio('1:1')
                                    ->imageResizeTargetWidth('300')
                                    ->imageResizeTargetHeight('300')
                                    ->directory('games/thumbnails')
                                    ->required()
                                    ->helperText('Upload thumbnail image for the game. Recommended size: 300x300px.'),

                                CloudinaryFileUpload::make('image_banner_path')
                                    ->label('Banner Image')
                                    ->image()
                                    ->imageEditor()
                                    ->imageCropAspectRatio('16:9')
                                    ->imageResizeTargetWidth('1200')
                                    ->imageResizeTargetHeight('675')
                                    ->directory('games/banners')
                                    ->required()
                                    ->helperText('Upload banner image for the game. Recommended size: 1200x675px.'),

                                Forms\Components\RichEditor::make('long_description')
                                    ->label('Long Description')
                                    ->required()
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
                                    ->placeholder('Enter detailed game description...')
                                    ->columnSpanFull(),

                                Forms\Components\Repeater::make('faq')
                                    ->label('Frequently Asked Questions')
                                    ->schema([
                                        Forms\Components\TextInput::make('question')
                                            ->label('Question')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\RichEditor::make('answer')
                                            ->label('Answer')
                                            ->required()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'strike',
                                                'link',
                                                'bulletList',
                                                'orderedList',
                                                'blockquote',
                                            ])
                                            ->columnSpanFull(),
                                    ])
                                    ->defaultItems(0)
                                    ->reorderableWithButtons()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(8),

                        // Right Column - Status and Settings (4 columns)
                        Forms\Components\Grid::make(1)
                            ->schema([
                                // Status Card
                                Forms\Components\Section::make('Status')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Visible')
                                            ->default(true)
                                            ->helperText('This game will be hidden from all sales channels.'),

                                        Forms\Components\Toggle::make('is_popular')
                                            ->label('Popular')
                                            ->default(false)
                                            ->helperText('Mark this game as popular to highlight it.'),

                                        Forms\Components\TextInput::make('display_order')
                                            ->label('Display Order')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Order for displaying games. Lower numbers appear first.'),
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
                Tables\Columns\ImageColumn::make('image_thumbnail_path')
                    ->label('Thumbnail')
                    ->circular()
                    ->size(50),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('gameConfiguration.name')
                    ->label('Configuration')
                    ->sortable()
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('developer')
                    ->label('Developer')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('is_popular')
                    ->label('Popular')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('display_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable()
                    ->badge(),

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
                    ->placeholder('All Games')
                    ->trueLabel('Active Games')
                    ->falseLabel('Inactive Games'),

                Tables\Filters\TernaryFilter::make('is_popular')
                    ->label('Popular Status')
                    ->placeholder('All Games')
                    ->trueLabel('Popular Games')
                    ->falseLabel('Regular Games'),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(Category::where('is_active', true)->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->reorderable('display_order')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('display_order', 'asc');
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
            'index' => Pages\ListGames::route('/'),
            'create' => Pages\CreateGame::route('/create'),
            'edit' => Pages\EditGame::route('/{record}/edit'),
        ];
    }
} 