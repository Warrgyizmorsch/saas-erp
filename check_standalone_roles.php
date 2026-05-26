<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "--- STANDALONE HRM DB ROLES ---\n";
    $roles = DB::connection('central')->table('warr-hrms.roles_master')->get();
    foreach ($roles as $role) {
        echo "ID: {$role->id}, Name: {$role->name}, Slug: {$role->slug}, Active: {$role->is_active}\n";
    }
} catch (\Exception $e) {
    // If warr-hrms is on a different connection configuration, let's try direct query or PDO
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;dbname=warr-hrms", "root", "");
        $stmt = $pdo->query("SELECT * FROM roles_master");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID: {$row['id']}, Name: {$row['name']}, Slug: {$row['slug']}, Active: {$row['is_active']}\n";
        }
    } catch (\Exception $ex) {
        echo "Error: " . $ex->getMessage() . "\n";
    }
}
