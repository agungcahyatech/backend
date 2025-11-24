<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function products(): HasMany
    {
        // Pastikan model Product Anda ada dan siap dihubungkan
        return $this->hasMany(Product::class, 'category_id');
    }
}
