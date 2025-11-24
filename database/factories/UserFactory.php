<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'no_handphone' => fake()->phoneNumber(),
            'balance' => fake()->randomFloat(2, 0, 10000),
            'role_id' => null, // Don't create role automatically
            'api_key' => Str::random(32),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user has no role.
     */
    public function withoutRole(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => null,
        ]);
    }

    /**
     * Indicate that the user has a specific role.
     */
    public function withRole(Role $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => $role->id,
        ]);
    }

    /**
     * Indicate that the user has a Gold Member role.
     */
    public function goldMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('name', 'Gold Member')->first()?->id,
        ]);
    }

    /**
     * Indicate that the user has a Silver Member role.
     */
    public function silverMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('name', 'Silver Member')->first()?->id,
        ]);
    }

    /**
     * Indicate that the user has a Bronze Member role.
     */
    public function bronzeMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('name', 'Bronze Member')->first()?->id,
        ]);
    }
}
