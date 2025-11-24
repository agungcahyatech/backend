<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    // Optimasi query dengan eager loading untuk relasi yang sering digunakan
    protected function getTableQuery(): Builder
    {
        return Transaction::query()
            ->with([
                'user:id,username,email',
                'product:id,name',
                'payment:id,transaction_id,payment_method,payment_status'
            ])
            ->select(['id', 'user_id', 'product_id', 'quantity', 'base_price', 'status', 'created_at', 'updated_at']);
    }

    // Pagination untuk performa yang lebih baik pada data yang banyak
    public function getTableRecordsPerPage(): int|string|null
    {
        return 25; // Kurangi dari default untuk loading yang lebih cepat
    }
} 