<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $guarded = [];
    protected $casts = ['expired_at' => 'datetime'];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}