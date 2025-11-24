<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = [
        'code', 'description', 'discount_type', 'discount_value', 
        'start_date', 'end_date', 'min_purchase', 
        'total_usage_limit', 'user_usage_limit', 'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // Relasi ke game mana saja voucher ini berlaku
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class);
    }

    // Relasi untuk melihat siapa saja yang sudah menggunakan
    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }
}