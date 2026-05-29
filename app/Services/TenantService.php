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
        $host = app()->bound('request') ? request()->getHost() : 'localhost';
        $suffix = filter_var($host, FILTER_VALIDATE_IP) ? '.' . $host . '.nip.io' : '.' . $host;
        $tenant->domains()->create([
            'domain' => $tenantId . $suffix,
        ]);

        // Create database automatically
        tenancy()->initialize($tenant);

        return $tenant;
    }
}