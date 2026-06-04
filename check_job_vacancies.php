<?php

include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== INITIALIZING TENANCY ===\n";
$tenant = \App\Models\Tenant::first();
if ($tenant) {
    echo "Found tenant: {$tenant->id}\n";
    tenancy()->initialize($tenant);
}

$candidates = \Modules\HRMS\App\Models\JobVacancy::all();
echo "Candidates count in tenant: " . $candidates->count() . "\n";
foreach ($candidates as $c) {
    echo " - ID: {$c->id} | Name: {$c->name} | Email: {$c->email} | Status: {$c->status} | Tenant ID: {$c->tenant_id}\n";
}
