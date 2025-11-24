<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'name',
        'slug',
        'icon_path',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    protected $appends = [
        'icon_url',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getIconUrlAttribute(): ?string
    {
        if (!$this->icon_path) {
            return null;
        }

        // Check if it's already a Cloudinary URL
        if (filter_var($this->icon_path, FILTER_VALIDATE_URL) ||
            str_starts_with($this->icon_path, 'https://res.cloudinary.com')) {
            return $this->icon_path;
        }

        // Return local storage URL
        return Storage::url($this->icon_path);
    }
}