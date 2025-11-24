<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'profit_percentage',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'profit_percentage' => 'decimal:2',
        ];
    }

    /**
     * Get the users for the role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if this role has a specific name.
     */
    public function hasName(string $roleName): bool
    {
        return $this->name === $roleName;
    }

    /**
     * Get the profit percentage as a formatted string.
     */
    public function getFormattedProfitPercentage(): string
    {
        return number_format($this->profit_percentage, 2) . '%';
    }
} 