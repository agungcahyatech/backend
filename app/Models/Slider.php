<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Slider extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'image_path',
        'link_url',
        'display_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'image_url',
    ];

    /**
     * Get the full URL for the slider image.
     *
     * @return string
     */
    public function getImageUrlAttribute(): string
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
        return Storage::url($this->image_path);
    }
}