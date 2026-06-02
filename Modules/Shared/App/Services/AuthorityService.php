<?php

namespace Modules\Shared\App\Services;

use Modules\Shared\App\Models\Role;
use Modules\Shared\App\Models\User;
use Modules\HRMS\App\Models\Employee;

class AuthorityService
{
    /**
     * Determine if a role can manage another role.
     */
    public function canManageRole($currentRole, $targetRole): bool
    {
        if (!$currentRole) {
            return false;
        }

        // Get Role IDs or Roles
        $currentRoleId = $currentRole instanceof Role ? $currentRole->id : (is_numeric($currentRole) ? (int)$currentRole : null);
        $targetRoleId = $targetRole instanceof Role ? $targetRole->id : (is_numeric($targetRole) ? (int)$targetRole : null);

        // If Super Admin (Role ID = 1), bypass all checks
        if ($currentRoleId === 1) {
            return true;
        }

        // Super Admin cannot be managed by anyone except another Super Admin
        if ($targetRoleId === 1) {
            return false;
        }

        // Resolve models to get authority levels
        $currentModel = $currentRole instanceof Role ? $currentRole : Role::find($currentRoleId);
        $targetModel = $targetRole instanceof Role ? $targetRole : Role::find($targetRoleId);

        if (!$currentModel || !$targetModel) {
            return false;
        }

        return $currentModel->authority_level > $targetModel->authority_level;
    }

    /**
     * Determine if a user can manage another user.
     */
    public function canManageUser($currentUser, $targetUser): bool
    {
        if (!$currentUser || !$targetUser) {
            return false;
        }

        // Super Admin can manage anyone
        if ($currentUser->role_id === 1) {
            return true;
        }

        // Self-management exception: a user can view/edit their own profile
        if ($currentUser->id === $targetUser->id) {
            return true;
        }

        // A user cannot manage Super Admin (Role ID = 1)
        if ($targetUser->role_id === 1) {
            return false;
        }

        // Resolve roles
        $currentRole = $currentUser->role ?? Role::find($currentUser->role_id);
        $targetRole = $targetUser->role ?? Role::find($targetUser->role_id);

        if (!$currentRole || !$targetRole) {
            return false;
        }

        return $currentRole->authority_level > $targetRole->authority_level;
    }

    /**
     * Determine if a user can manage an employee.
     */
    public function canManageEmployee($currentUser, $targetEmployee): bool
    {
        if (!$currentUser || !$targetEmployee) {
            return false;
        }

        // Super Admin can manage anyone
        if ($currentUser->role_id === 1) {
            return true;
        }

        // Resolve user associated with the employee
        $targetUser = $targetEmployee->user ?? null;
        if (!$targetUser) {
            // Find user by email fallback
            $targetUser = User::where('email', $targetEmployee->email)->first();
        }

        if ($targetUser) {
            return $this->canManageUser($currentUser, $targetUser);
        }

        // Check against role slug from Employee table directly if it exists
        $employeeRoleSlug = $targetEmployee->role;
        if (!$employeeRoleSlug) {
            $employeeRoleSlug = 'employee';
        }

        // Search for matching Role in roles table by name/slug
        $targetRole = Role::all()->first(function($r) use ($employeeRoleSlug) {
            return strtolower(str_replace(' ', '_', $r->name)) === strtolower(str_replace(' ', '_', $employeeRoleSlug))
                || strtolower($r->name) === strtolower($employeeRoleSlug)
                || (isset($r->slug) && $r->slug === $employeeRoleSlug);
        });

        if ($targetRole) {
            return $this->canManageRole($currentUser->role ?? Role::find($currentUser->role_id), $targetRole);
        }

        // Default: Compare current user's authority level to base Employee level (10)
        $currentUserRole = $currentUser->role ?? Role::find($currentUser->role_id);
        if ($currentUserRole) {
            return $currentUserRole->authority_level > 10;
        }

        return false;
    }
}
