<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'image_path',
        'content',
        'is_published',
        'publish_date',
        // 'view_count' tidak dimasukkan karena di-update secara otomatis
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'publish_date' => 'datetime',
        'view_count' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (filter_var($this->image_path, FILTER_VALIDATE_URL) || str_starts_with($this->image_path, 'https://res.cloudinary.com')) {
            return $this->image_path;
        }
        return $this->image_path ? Storage::url($this->image_path) : null;
    }
}