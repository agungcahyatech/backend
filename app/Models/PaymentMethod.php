<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_path',
        'name',
        'provider',
        'code',
        'group',
        'type',
        'fee_flat',
        'fee_percent',
        'min_amount',
        'max_amount',
        'is_active',
    ];

    protected $casts = [
        'fee_flat' => 'float',
        'fee_percent' => 'float',
        'min_amount' => 'integer',
        'max_amount' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        // Jika image_path sudah berupa URL Cloudinary, kembalikan langsung
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        // Jika image_path adalah URL Cloudinary yang dimulai dengan https://res.cloudinary.com
        if (str_starts_with($this->image_path, 'https://res.cloudinary.com')) {
            return $this->image_path;
        }

        // Jika masih berupa path lokal, kembalikan URL storage
        return $this->image_path ? Storage::url($this->image_path) : null;
    }
}