<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameConfigurationResource\Pages;
use App\Filament\Clusters\GameCluster;
use App\Filament\Forms\Components\CloudinaryFileUpload;
use App\Models\GameConfiguration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GameConfigurationResource extends Resource
{
    protected static ?string $model = GameConfiguration::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Game Configurations';

    protected static ?int $navigationSort = 2;

    protected static ?string $cluster = GameCluster::class;

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
                Forms\Components\Section::make('General Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Configuration Name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Internal name for this game configuration, e.g., "Mobile Legends Config"'),

                        Forms\Components\Select::make('validation_provider')
                            ->label('Validation Provider')
                            ->options(function () {
                                $jsonPath = public_path('storage/game_validation.json');
                                if (!file_exists($jsonPath)) {
                                    return [];
                                }
                                
                                $jsonData = json_decode(file_get_contents($jsonPath), true);
                                $options = [];
                                
                                if (isset($jsonData['data']) && is_array($jsonData['data'])) {
                                    foreach ($jsonData['data'] as $game) {
                                        // Check if both slug and name exist
                                        if (isset($game['slug']) && isset($game['name'])) {
                                            $options[$game['slug']] = $game['name'];
                                        } elseif (isset($game['slug'])) {
                                            // If name doesn't exist, use slug as both key and value
                                            $options[$game['slug']] = $game['slug'];
                                        }
                                    }
                                }
                                
                                return $options;
                            })
                            ->searchable()
                            ->helperText('Select the game validation provider from the available options'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Enable to make this configuration available for games'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Guide Information')
                    ->schema([
                        Forms\Components\Textarea::make('guide_text')
                            ->label('Guide Text')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('Text guide for users on how to use this configuration'),

                        CloudinaryFileUpload::make('guide_image_path')
                            ->label('Guide Image')
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('450')
                            ->directory('game-configurations/guides')
                            ->helperText('Upload guide image to Cloudinary. Recommended size: 800x450px.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuration Fields')
                    ->schema([
                        Forms\Components\Repeater::make('fields')
                            ->label('Configuration Fields')
                            ->relationship('fields')
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->label('Field Label')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Display label for this field, e.g., "User ID"'),

                                Forms\Components\TextInput::make('input_name')
                                    ->label('Field Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Internal field name, e.g., "user_id"'),

                                Forms\Components\Select::make('type')
                                    ->label('Field Type')
                                    ->options([
                                        'text' => 'Text Input',
                                        'number' => 'Number Input',
                                        'select' => 'Select Dropdown',
                                        'password' => 'Password Input',
                                    ])
                                    ->required()
                                    ->default('text')
                                    ->live(),

                                Forms\Components\Repeater::make('options')
                                    ->label('Select Options')
                                    ->schema([
                                        Forms\Components\TextInput::make('value')
                                            ->label('Option Value')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        Forms\Components\TextInput::make('label')
                                            ->label('Option Label')
                                            ->required()
                                            ->maxLength(255)
                                            ->helperText('Display text for this option'),
                                    ])
                                    ->defaultItems(0)
                                    ->reorderableWithButtons()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? $state['value'] ?? null)
                                    ->visible(fn (callable $get) => $get('type') === 'select')
                                    ->helperText('Add options for the select dropdown field'),

                                Forms\Components\TextInput::make('placeholder')
                                    ->label('Placeholder Text')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->helperText('Placeholder text to show in the field (optional)'),

                                Forms\Components\TextInput::make('validation_rules')
                                    ->label('Validation Rules')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->helperText('Laravel validation rules, e.g., "required|min:3|max:50" (optional)'),

                                Forms\Components\Toggle::make('is_required')
                                    ->label('Required Field')
                                    ->default(true),

                                Forms\Components\TextInput::make('display_order')
                                    ->label('Display Order')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Order for displaying fields. Lower numbers appear first.'),
                            ])
                            ->defaultItems(0)
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ->columnSpanFull()
                            ->helperText('Configure the fields that will be shown to users for this game configuration'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('validation_provider')
                    ->label('Provider')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('fields_count')
                    ->label('Fields')
                    ->counts('fields')
                    ->badge(),

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
                    ->placeholder('All Configurations')
                    ->trueLabel('Active Configurations')
                    ->falseLabel('Inactive Configurations'),
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
            'index' => Pages\ListGameConfigurations::route('/'),
            'create' => Pages\CreateGameConfiguration::route('/create'),
            'edit' => Pages\EditGameConfiguration::route('/{record}/edit'),
        ];
    }
} 