<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FlashSale extends Model
{
    protected $fillable = ['name', 'start_date', 'end_date', 'is_active'];
    protected $casts = ['start_date' => 'datetime', 'end_date' => 'datetime'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
                    // Penting untuk bisa akses kolom di tabel pivot
                    ->withPivot('discounted_price', 'stock')
                    ->withTimestamps();
    }
}