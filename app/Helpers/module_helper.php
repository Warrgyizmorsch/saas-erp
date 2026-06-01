<?php

use App\Models\TenantModule;
use Modules\Shared\App\Services\AuthorityService;

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

if (!function_exists('canManageRole')) {
    function canManageRole($currentRole, $targetRole): bool
    {
        return app(AuthorityService::class)->canManageRole($currentRole, $targetRole);
    }
}

if (!function_exists('canManageUser')) {
    function canManageUser($currentUser, $targetUser): bool
    {
        return app(AuthorityService::class)->canManageUser($currentUser, $targetUser);
    }
}

if (!function_exists('canManageEmployee')) {
    function canManageEmployee($currentUser, $targetEmployee): bool
    {
        return app(AuthorityService::class)->canManageEmployee($currentUser, $targetEmployee);
    }
}