<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    protected $fillable = ['user_id', 'invoice_id', 'payment_method_name', 'amount', 'final_amount', 'status', 'payment_url', 'expired_at'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
