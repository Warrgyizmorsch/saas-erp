<?php

include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "--- Registered Attendance & Payroll Routes in DB ---\n";
$routes = DB::table('routes')
    ->where('route_name', 'like', '%attendance%')
    ->orWhere('route_name', 'like', '%payroll%')
    ->orWhere('name', 'like', '%attendance%')
    ->orWhere('name', 'like', '%payroll%')
    ->get();

foreach ($routes as $route) {
    echo "ID: {$route->id} | Name: {$route->name} | Route Name: {$route->route_name} | Method: {$route->method}\n";
    $permissions = DB::table('role_permissions')
        ->where('route_id', $route->id)
        ->get();
    foreach ($permissions as $p) {
        echo "   Role ID: {$p->role_id} | Allowed: {$p->is_allowed}\n";
    }
}
