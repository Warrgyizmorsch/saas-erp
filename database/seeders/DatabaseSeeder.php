<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed default packages
        \DB::table('packages')->insert([
            [
                'name' => 'Starter Plan',
                'code' => 'starter',
                'max_users' => 4,
                'price' => 29.00,
                'description' => 'Ideal for small startups and teams.',
                'features' => json_encode(['Access to CRM', 'Access to HRMS', 'Up to 4 Users', 'Basic Support']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grower Plan',
                'code' => 'grower',
                'max_users' => 10,
                'price' => 89.00,
                'description' => 'Perfect for growing businesses.',
                'features' => json_encode(['Access to CRM', 'Access to HRMS', 'Access to Inventory', 'Up to 10 Users', 'Priority Support']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Enterprise Plan',
                'code' => 'enterprise',
                'max_users' => 999999, // Unlimited
                'price' => 199.00,
                'description' => 'Complete suite for large scale enterprises.',
                'features' => json_encode(['All Modules Enabled', 'Unlimited Users', 'Dedicated Database', '24/7 Phone Support']),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Seed central test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
