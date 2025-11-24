<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'developer', 'brand', 'allowed_region',
        'image_thumbnail_path', 'image_banner_path',
        'description', 'long_description', 'faq',
        'is_popular', 'is_active', 'display_order', 'category_id', 'game_configuration_id',
    ];

    protected $casts = [
        'faq' => 'array',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    protected $appends = [
        'image_thumbnail_url',
        'image_banner_url',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getImageThumbnailUrlAttribute(): ?string
    {
        // Jika image_thumbnail_path sudah berupa URL Cloudinary, kembalikan langsung
        if (filter_var($this->image_thumbnail_path, FILTER_VALIDATE_URL)) {
            return $this->image_thumbnail_path;
        }

        // Jika image_thumbnail_path adalah URL Cloudinary yang dimulai dengan https://res.cloudinary.com
        if (str_starts_with($this->image_thumbnail_path, 'https://res.cloudinary.com')) {
            return $this->image_thumbnail_path;
        }

        // Jika masih berupa path lokal, kembalikan URL storage
        return $this->image_thumbnail_path ? Storage::url($this->image_thumbnail_path) : null;
    }

    public function getImageBannerUrlAttribute(): ?string
    {
        // Jika image_banner_path sudah berupa URL Cloudinary, kembalikan langsung
        if (filter_var($this->image_banner_path, FILTER_VALIDATE_URL)) {
            return $this->image_banner_path;
        }

        // Jika image_banner_path adalah URL Cloudinary yang dimulai dengan https://res.cloudinary.com
        if (str_starts_with($this->image_banner_path, 'https://res.cloudinary.com')) {
            return $this->image_banner_path;
        }

        // Jika masih berupa path lokal, kembalikan URL storage
        return $this->image_banner_path ? Storage::url($this->image_banner_path) : null;
    }

    public function gameConfiguration(): BelongsTo
    {
        return $this->belongsTo(GameConfiguration::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function vouchers(): BelongsToMany 
    {
        return $this->belongsToMany(Voucher::class);
    }

}