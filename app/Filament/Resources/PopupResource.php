<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PopupResource\Pages;
use App\Models\Popup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PopupResource extends Resource
{
    protected static ?string $model = Popup::class;

    protected static ?string $navigationIcon = 'heroicon-o-window';

    protected static ?string $navigationGroup = 'Manajemen Website';

    protected static ?string $navigationLabel = 'Popups';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Popup Title')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Enter the title for this popup'),

                Forms\Components\FileUpload::make('image_path')
                    ->label('Popup Image')
                    ->image()
                    ->imageEditor()
                    ->imageCropAspectRatio('4:3')
                    ->imageResizeTargetWidth('800')
                    ->imageResizeTargetHeight('600')
                    ->directory('popups')
                    ->required()
                    ->helperText('Upload popup image. Recommended size: 800x600px')
                    ->columnSpanFull(),

                Forms\Components\RichEditor::make('content')
                    ->label('Popup Content')
                    ->required()
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'link',
                        'bulletList',
                        'orderedList',
                        'h2',
                        'h3',
                        'blockquote',
                    ])
                    ->helperText('Write the content/description for this popup'),

                Forms\Components\TextInput::make('link_url')
                    ->label('Link URL')
                    ->url()
                    ->placeholder('https://example.com')
                    ->helperText('Optional: URL to navigate when popup is clicked'),

                Forms\Components\DatePicker::make('start_date')
                    ->label('Start Date')
                    ->required()
                    ->helperText('When this popup should start appearing'),

                Forms\Components\DatePicker::make('end_date')
                    ->label('End Date')
                    ->helperText('Optional: When this popup should stop appearing'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Enable or disable this popup'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->size(60)
                    ->height(45),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('content')
                    ->label('Content Preview')
                    ->limit(50)
                    ->html()
                    ->searchable(),

                Tables\Columns\TextColumn::make('link_url')
                    ->label('Link URL')
                    ->limit(30)
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date()
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

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All Popups')
                    ->trueLabel('Active Popups')
                    ->falseLabel('Inactive Popups'),
                
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('start_date_filter')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('end_date_filter')
                            ->label('To Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['start_date_filter'],
                                fn ($query, $date) => $query->where('start_date', '>=', $date)
                            )
                            ->when(
                                $data['end_date_filter'],
                                fn ($query, $date) => $query->where('end_date', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['start_date_filter'] ?? null) {
                            $indicators['start_date_filter'] = 'From: ' . $data['start_date_filter'];
                        }
                        if ($data['end_date_filter'] ?? null) {
                            $indicators['end_date_filter'] = 'To: ' . $data['end_date_filter'];
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Popup')
                    ->modalDescription('Update popup information, image, and settings.')
                    ->modalSubmitActionLabel('Update Popup'),
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
            'index' => Pages\ListPopups::route('/'),
        ];
    }
} 