<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DepositVoucherUsage;

class DepositVoucher extends Model
{
    protected $fillable = ['code', 'amount', 'usage_limit', 'expired_at', 'is_active'];
    public function usages(): HasMany
    {
        return $this->hasMany(DepositVoucherUsage::class);
    }
}
