<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Clusters\TransactionCluster;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Transactions';

    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = TransactionCluster::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Transaksi')
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable(),
                        TextInput::make('game_user_id')->label('Game User ID')->required(),
                        TextInput::make('game_zone_id')->label('Game Zone ID'),
                        TextInput::make('nickname')->label('Nickname'),
                        Select::make('product_id')
                            ->label('Product')
                            ->options(Product::all()->pluck('name', 'id'))
                            ->searchable(),
                        TextInput::make('product_name')->label('Product Name')->required(),
                        TextInput::make('provider_sku')->label('Provider SKU')->required(),
                        TextInput::make('quantity')->label('Quantity')->numeric()->default(1),
                        TextInput::make('base_price')->label('Base Price')->numeric()->required(),
                        TextInput::make('provider_name')->label('Provider Name'),
                        TextInput::make('provider_order_id')->label('Provider Order ID'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'success' => 'Success',
                                'failed' => 'Failed',
                                'canceled' => 'Canceled',
                            ])->required(),
                        Textarea::make('serial_number')->label('Serial Number'),
                        Textarea::make('log')->label('Log')->json(),
                        TextInput::make('transaction_type')->label('Transaction Type')->required(),
                        TextInput::make('ref_id')->label('Ref ID'),
                        Toggle::make('success_report_sent')->label('Success Report Sent'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.name')->label('User')->searchable(),
                TextColumn::make('game_user_id')->label('Game User ID'),
                TextColumn::make('product_name')->label('Product'),
                TextColumn::make('provider_name')->label('Provider'),
                TextColumn::make('status')->badge(),
                TextColumn::make('base_price')->label('Base Price')->money('IDR'),
                TextColumn::make('transaction_type')->label('Type'),
                TextColumn::make('created_at')->dateTime('d M Y H:i'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
} 