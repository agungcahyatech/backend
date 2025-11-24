<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositVoucherUsage extends Model
{
    protected $fillable = [
        'deposit_voucher_id',
        'user_id',
    ];

    public function depositVoucher(): BelongsTo
    {
        return $this->belongsTo(DepositVoucher::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
