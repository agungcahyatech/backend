<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $game = Game::factory()->create();
        $category = ProductCategory::factory()->create(['game_id' => $game->id]);
        
        $providers = array_keys(Product::getAvailableProviders());
        if (empty($providers)) {
            $providers = ['MANUAL'];
        }
        $productNames = ['100 Diamonds', '500 Diamonds', '1000 Diamonds', '2000 Diamonds', '5000 Diamonds'];
        
        return [
            'name' => $this->faker->randomElement($productNames),
            'icon_path' => null,
            'description' => $this->faker->sentence(),
            'base_price' => $this->faker->randomFloat(2, 10000, 1000000),
            'provider_sku' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{3}'),
            'provider' => $this->faker->randomElement($providers),
            'product_category_id' => $category->id,
            'game_id' => $game->id,
            'display_order' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(80),
        ];
    }

    /**
     * Indicate that the product is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
} 