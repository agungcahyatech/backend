<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class GameConfiguration extends Model
{
    protected $fillable = ['name', 'guide_text', 'guide_image_path', 'validation_provider', 'is_active'];
    protected $appends = ['guide_image_url'];

    public function fields(): HasMany
    {
        return $this->hasMany(GameConfigurationField::class);
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function getGuideImageUrlAttribute(): ?string
    {
        // Jika guide_image_path sudah berupa URL Cloudinary, kembalikan langsung
        if (filter_var($this->guide_image_path, FILTER_VALIDATE_URL)) {
            return $this->guide_image_path;
        }

        // Jika guide_image_path adalah URL Cloudinary yang dimulai dengan https://res.cloudinary.com
        if (str_starts_with($this->guide_image_path, 'https://res.cloudinary.com')) {
            return $this->guide_image_path;
        }

        // Jika masih berupa path lokal, kembalikan URL storage
        return $this->guide_image_path ? Storage::url($this->guide_image_path) : null;
    }
}