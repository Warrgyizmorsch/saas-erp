<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Shared\App\Models\Role;

class UpdateRoleAuthorityLevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            1 => 100, // Super Admin
            2 => 90,  // Admin
            6 => 80,  // HOD
            8 => 75,  // HR
            7 => 70,  // Supervisor
            10 => 65, // Purchase Admin
            5 => 60,  // Store Admin
            3 => 55,  // Account
            9 => 50,  // Sales
            4 => 45,  // Purchase
            11 => 10, // Employee
        ];

        foreach ($levels as $roleId => $level) {
            Role::where('id', $roleId)->update(['authority_level' => $level]);
        }

        // Set any other roles default to 0
        Role::whereNotIn('id', array_keys($levels))->whereNull('authority_level')->update(['authority_level' => 0]);
    }
}
