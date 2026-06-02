<?php

namespace Modules\Shared\App\Http\Controllers;

use Modules\Shared\App\Http\Controllers\Controller;
use Modules\Shared\App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $roles = $query->paginate(10);

        return view('shared::shared.roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'authority_level' => 'nullable|integer|min:0|max:100',
        ]);

        // Check if role name already exists in the active tenant context
        if (Role::where('name', $validated['name'])->exists()) {
            return redirect()->back()->withErrors(['name' => 'The role name has already been taken.'])->withInput();
        }

        $loggedInUser = auth()->user();
        $loggedInRole = $loggedInUser->role ?? Role::find($loggedInUser->role_id);
        $loggedInLevel = $loggedInRole ? $loggedInRole->authority_level : 0;

        $authorityLevel = $request->input('authority_level', 0);

        if ($loggedInUser->role_id !== 1 && $authorityLevel >= $loggedInLevel) {
            return redirect()->back()->withErrors(['authority_level' => 'You cannot set an authority level equal to or greater than your own role level.'])->withInput();
        }

        // Duplicate-prevention: check if role exists globally
        $existingRole = Role::withoutGlobalScope('tenant_json')
            ->where('name', $validated['name'])
            ->first();

        if ($existingRole) {
            $tenantId = tenant('id');
            if ($tenantId) {
                $tenantIds = $existingRole->tenant_id ?? [];
                if (!in_array($tenantId, $tenantIds)) {
                    $tenantIds[] = $tenantId;
                    $existingRole->tenant_id = $tenantIds;
                    $existingRole->save();
                }
            }
            return redirect()->route('roles.index')->with('success', 'Role created successfully!');
        }

        Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'authority_level' => $authorityLevel,
        ]);

        return redirect()->route('roles.index')->with('success', 'Role created successfully!');
    }

    public function edit($id)
    {
        $editRole = Role::findOrFail($id);

        $loggedInRole = auth()->user()->role ?? Role::find(auth()->user()->role_id);
        if (!canManageRole($loggedInRole, $editRole)) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::paginate(10);

        return view('shared::shared.roles.index', compact('roles', 'editRole'));
    }

    public function update(Request $request, Role $role)
    {
        $loggedInRole = auth()->user()->role ?? Role::find(auth()->user()->role_id);
        if (!canManageRole($loggedInRole, $role)) {
            abort(403, 'Unauthorized action.');
        }

        // Restrict tenants from modifying global system roles
        if (tenant('id') && empty($role->tenant_id)) {
            abort(403, 'You cannot update a global system role.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'authority_level' => 'nullable|integer|min:0|max:100',
        ]);

        // Check if role name already exists in the active tenant context
        if (Role::where('name', $request->name)->where('id', '!=', $role->id)->exists()) {
            return redirect()->back()->withErrors(['name' => 'The role name has already been taken.'])->withInput();
        }

        $loggedInUser = auth()->user();
        $loggedInLevel = $loggedInRole ? $loggedInRole->authority_level : 0;

        $authorityLevel = $request->input('authority_level', 0);

        if ($loggedInUser->role_id !== 1 && $authorityLevel >= $loggedInLevel) {
            return redirect()->back()->withErrors(['authority_level' => 'You cannot set an authority level equal to or greater than your own role level.'])->withInput();
        }

        $role->update([
            'name' => $request->name,
            'authority_level' => $authorityLevel,
        ]);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully!');
    }

    public function destroy(Role $role)
    {
        $loggedInRole = auth()->user()->role ?? Role::find(auth()->user()->role_id);
        if (!canManageRole($loggedInRole, $role)) {
            abort(403, 'Unauthorized action.');
        }

        $tenantId = tenant('id');
        if ($tenantId) {
            $tenantIds = $role->tenant_id ?? [];
            if (empty($tenantIds)) {
                // Global system role
                abort(403, 'You cannot delete a global system role.');
            }

            if (in_array($tenantId, $tenantIds)) {
                $tenantIds = array_diff($tenantIds, [$tenantId]);
                if (empty($tenantIds)) {
                    $role->delete();
                } else {
                    $role->tenant_id = array_values($tenantIds);
                    $role->save();
                }
            }
        } else {
            $role->delete();
        }

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully!');
    }
}
