<?php

include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tenants = \App\Models\Tenant::all();
echo "Found " . $tenants->count() . " tenants total.\n";

foreach ($tenants as $tenant) {
    try {
        tenancy()->initialize($tenant);
        $candidates = \Modules\HRMS\App\Models\JobVacancy::all();
        if ($candidates->count() > 0) {
            echo "Tenant: {$tenant->id} has {$candidates->count()} candidates:\n";
            foreach ($candidates as $c) {
                echo " - ID: {$c->id} | Name: {$c->name} | Email: {$c->email} | Status: {$c->status} | Tenant ID: {$c->tenant_id}\n";
            }
        }
        tenancy()->end();
    } catch (\Exception $e) {
        echo "Error in tenant {$tenant->id}: " . $e->getMessage() . "\n";
    }
}
