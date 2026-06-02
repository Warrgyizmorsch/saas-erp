<?php

namespace Modules\Shared\App\Http\Controllers;

use Modules\Shared\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Shared\App\Models\Role;
use Modules\Shared\App\Models\Menu;
use Modules\Shared\App\Models\Route as AppRoute;
use Modules\Shared\App\Models\RolePermission;

class RolePermissionController extends Controller
{
    public function index(Request $request)
    {
        $loggedInUser = auth()->user();
        $loggedInRole = $loggedInUser->role ?? Role::find($loggedInUser->role_id);
        $loggedInLevel = $loggedInRole ? $loggedInRole->authority_level : 0;

        if ($loggedInUser->role_id === 1) {
            $roles = Role::get();
        } else {
            $roles = Role::where('authority_level', '<', $loggedInLevel)->get();
        }

        $selectedRole = null;
        $allMenus = collect();
        $menuPermissions = [];
        $routePermissions = [];
        $extraRoutes = collect();

        if ($request->has('role_id')) {
            $selectedRole = Role::findOrFail($request->role_id);

            if (!canManageRole($loggedInRole, $selectedRole)) {
                abort(403, 'Unauthorized action.');
            }

            // ✅ Get full recursive menu tree
            $allMenus = Menu::where('is_deleted', 0)
                ->whereNull('parent_id')
                ->with('childrenRecursive')
                ->orderBy('sort_order')
                ->get();

            // Menu permissions (keyed by menu_id)
            $menuPermissions = $selectedRole->rolePermissions
                ->whereNotNull('menu_id')
                ->pluck('is_allowed', 'menu_id')
                ->toArray();

            // Route permissions (keyed by route_id)
            $routePermissions = $selectedRole->rolePermissions
                ->whereNotNull('route_id')
                ->pluck('is_allowed', 'route_id')
                ->toArray();

            // ✅ Extra routes (not directly tied to menus.route_id)
            $extraRoutes = AppRoute::where('is_deleted', 0)
                ->whereNotIn('id', function ($q) {
                    $q->select('route_id')->from('menus')->whereNotNull('route_id');
                })
                ->orderBy('id')
                ->get()
                ->groupBy('menu_id');
        }

        return view('shared::shared.role_permissions.index', compact('roles', 'selectedRole', 'allMenus', 'menuPermissions', 'routePermissions', 'extraRoutes'));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $loggedInRole = auth()->user()->role ?? Role::find(auth()->user()->role_id);
        if (!canManageRole($loggedInRole, $role)) {
            abort(403, 'Unauthorized action.');
        }

        $submittedMenus = $request->input('permissions', []);
        $submittedRoutes = $request->input('permissions_route', []);

        // --- Menus ---
        foreach ($submittedMenus as $menuId => $value) {
            $menu = Menu::find($menuId);
            $permission = RolePermission::firstOrNew([
                'role_id' => $role->id,
                'menu_id' => $menuId
            ]);
            $permission->is_allowed = 1;

            if (!empty($menu->route_id)) {
                $permission->route_id = $menu->route_id;
            }

            $permission->save();
        }

        // Unchecked menus
        $allMenuIds = Menu::pluck('id')->toArray();
        $uncheckedMenus = array_diff($allMenuIds, array_keys($submittedMenus));
        if (!empty($uncheckedMenus)) {
            RolePermission::where('role_id', $role->id)
                ->whereIn('menu_id', $uncheckedMenus)
                ->update(['is_allowed' => 0]);
        }

        // --- Routes (independent of menus) ---
        foreach ($submittedRoutes as $routeId => $value) {
            $permission = RolePermission::firstOrNew([
                'role_id' => $role->id,
                'route_id' => $routeId,
                'menu_id' => null
            ]);
            $permission->is_allowed = 1;
            $permission->save();
        }

        // Unchecked routes
        $allRouteIds = AppRoute::whereNotIn('id', function ($q) {
            $q->select('route_id')->from('menus')->whereNotNull('route_id');
        })->pluck('id')->toArray();

        $uncheckedRoutes = array_diff($allRouteIds, array_keys($submittedRoutes));
        if (!empty($uncheckedRoutes)) {
            RolePermission::where('role_id', $role->id)
                ->whereIn('route_id', $uncheckedRoutes)
                ->whereNull('menu_id')
                ->update(['is_allowed' => 0]);
        }

        return redirect()->route('role-permissions.index', ['role_id' => $role->id])
            ->with('success', 'Permissions updated successfully!');
    }
}
