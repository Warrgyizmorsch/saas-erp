<?php

use App\Models\TenantModule;

if (!function_exists('tenant_module_enabled')) {

    function tenant_module_enabled($module)
    {
        if (!auth()->check()) {
            return false;
        }

        $tenant = tenant();

        if (!$tenant) {
            return false;
        }

        return TenantModule::where('tenant_id', $tenant->id)
            ->where('module', $module)
            ->where('enabled', true)
            ->exists();
    }
}