<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    // Gunakan guarded agar lebih fleksibel, atau definisikan $fillable sesuai kolom di atas
    protected $guarded = [];
    protected $casts = ['log' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function product(): BelongsTo
    {
        // onDelete('set null') di migrasi akan mencegah error jika produk dihapus
        return $this->belongsTo(Product::class);
    }
}