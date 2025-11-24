<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        
        return [
            'game_id' => Game::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'icon_path' => null,
            'display_order' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(80),
        ];
    }

    /**
     * Indicate that the product category is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the product category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
} 