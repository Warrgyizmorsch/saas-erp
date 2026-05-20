<?php

namespace App\Services;

use App\Models\Tenant;

class TenantService
{
    public function createTenant(string $tenantId)
    {
        // Create tenant
        $tenant = Tenant::create([
            'id' => $tenantId,
        ]);

        // Create domain
        $tenant->domains()->create([
            'domain' => "{$tenantId}.localhost",
        ]);

        // Create database automatically
        tenancy()->initialize($tenant);

        return $tenant;
    }
}