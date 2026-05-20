<?php

namespace Modules\CRM\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run()
    {
        // 1. Dashboard (No children)
        $dashboardId = DB::table('menus')->insertGetId([
            'parent_id' => null,
            'title' => 'Dashboard',
            'icon' => 'fas fa-home',
            'route_id' => 1, // assign actual route_id later
            'sort_order' => 1,
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Management (Parent)
        $managementId = DB::table('menus')->insertGetId([
            'parent_id' => null,
            'title' => 'Management',
            'icon' => 'fas fa-cogs',
            'route_id' => null,
            'sort_order' => 2,
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Management -> Submenus
        DB::table('menus')->insert([
            [
                'parent_id' => $managementId,
                'title' => 'Roles',
                'icon' => 'fas fa-user-shield',
                'route_id' => null,
                'sort_order' => 1,
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parent_id' => $managementId,
                'title' => 'Routes',
                'icon' => 'fas fa-route',
                'route_id' => null,
                'sort_order' => 2,
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parent_id' => $managementId,
                'title' => 'Menus',
                'icon' => 'fas fa-bars',
                'route_id' => null,
                'sort_order' => 3,
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 3. Users (Parent)
        $usersId = DB::table('menus')->insertGetId([
            'parent_id' => null,
            'title' => 'Users',
            'icon' => 'fas fa-users',
            'route_id' => null,
            'sort_order' => 3,
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Users -> Submenus
        DB::table('menus')->insert([
            [
                'parent_id' => $usersId,
                'title' => 'Add User',
                'icon' => 'fas fa-user-plus',
                'route_id' => null,
                'sort_order' => 1,
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parent_id' => $usersId,
                'title' => 'List Users',
                'icon' => 'fas fa-list',
                'route_id' => null,
                'sort_order' => 2,
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 4. Permissions (Parent)
        $permissionsId = DB::table('menus')->insertGetId([
            'parent_id' => null,
            'title' => 'Permissions',
            'icon' => 'fas fa-lock',
            'route_id' => null,
            'sort_order' => 4,
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

