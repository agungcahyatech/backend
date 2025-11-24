<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Gold Member', 'Silver Member', 'Bronze Member', 'Premium', 'Basic']),
            'profit_percentage' => fake()->randomFloat(2, 0, 50),
        ];
    }

    /**
     * Indicate that the role is a Gold Member.
     */
    public function goldMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Gold Member',
            'profit_percentage' => 25.00,
        ]);
    }

    /**
     * Indicate that the role is a Silver Member.
     */
    public function silverMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Silver Member',
            'profit_percentage' => 15.00,
        ]);
    }

    /**
     * Indicate that the role is a Bronze Member.
     */
    public function bronzeMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Bronze Member',
            'profit_percentage' => 5.00,
        ]);
    }
} 