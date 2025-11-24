<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'profit_percentage' => 0.00, // Admin tidak mendapat profit
            ],
            [
                'name' => 'silver',
                'profit_percentage' => 5.00, // 5% profit untuk silver
            ],
            [
                'name' => 'gold',
                'profit_percentage' => 10.00, // 10% profit untuk gold
            ],
            [
                'name' => 'pro',
                'profit_percentage' => 15.00, // 15% profit untuk pro
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
