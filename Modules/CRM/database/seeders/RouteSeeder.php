<?php

namespace Modules\CRM\Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Modules\CRM\App\Models\Route; // your Route model

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert dashboard route into routes table
        Route::updateOrCreate(
            ['name' => 'Dashboard'], // unique identifier
            [
                'route_name' => 'dashboard',
                'method' => 'get',
                'is_deleted' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}

