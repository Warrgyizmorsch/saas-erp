<?php

namespace Modules\CRM\Database\Seeders;

use Modules\CRM\App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // Call all the seeders here in the correct order
        $this->call([
            RouteSeeder::class,
            RolesTableSeeder::class,
            UsersTableSeeder::class,
            MenuSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}

