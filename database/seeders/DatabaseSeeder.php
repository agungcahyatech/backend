<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the RoleSeeder first
        $this->call([
            RoleSeeder::class,
        ]);

        // Get the admin role
        $adminRole = Role::where('name', 'admin')->first();

        // Create admin user (or get existing one)
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'password' => bcrypt('password'),
                'role_id' => $adminRole->id,
                'balance' => 10000.00,
                'no_handphone' => '081234567890',
                'api_key' => \Illuminate\Support\Str::random(32),
            ]
        );

        // Create test user (or get existing one)
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'username' => 'testuser',
                'password' => bcrypt('password'),
                'role_id' => $adminRole->id,
                'balance' => 5000.00,
                'no_handphone' => '081234567891',
                'api_key' => \Illuminate\Support\Str::random(32),
            ]
        );
    }
}
