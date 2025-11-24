<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'icon_path', 'description', 'base_price', 'provider_sku', 'provider',
        'product_category_id', 'game_id', 'display_order', 'is_active',
    ];

    protected $casts = [
        'base_price' => 'float',
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = ['icon_url'];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function getIconUrlAttribute(): ?string
    {
        // Jika icon_path sudah berupa URL Cloudinary, kembalikan langsung
        if (filter_var($this->icon_path, FILTER_VALIDATE_URL)) {
            return $this->icon_path;
        }

        // Jika icon_path adalah URL Cloudinary yang dimulai dengan https://res.cloudinary.com
        if (str_starts_with($this->icon_path, 'https://res.cloudinary.com')) {
            return $this->icon_path;
        }

        // Jika masih berupa path lokal, kembalikan URL storage
        return $this->icon_path ? Storage::url($this->icon_path) : null;
    }

    public static function getAvailableProviders(): array
    {
        // Cache provider options untuk mengurangi query ke database
        return cache()->remember('product.available_providers', 600, function () {
            $providers = [
                'MANUAL' => 'MANUAL (Manual Processing)',
            ];

            // Check for configured providers from settings
            if (!empty(\App\Models\Setting::getValue('digiflazz_username'))) {
                $providers['digiflazz'] = 'Digiflazz';
            }
            if (!empty(\App\Models\Setting::getValue('apigames_merchant'))) {
                $providers['apigames'] = 'Apigames';
            }
            if (!empty(\App\Models\Setting::getValue('bangjeff_username'))) {
                $providers['bangjeff'] = 'Bangjeff';
            }

            return $providers;
        });
    }

    public function flashSales(): BelongsToMany
    {
        return $this->belongsToMany(FlashSale::class)
                    ->withPivot('discounted_price', 'stock')
                    ->withTimestamps();
    }
}