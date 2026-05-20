<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\App\Models\Role;
use Modules\CRM\App\Models\Menu;
use Modules\CRM\App\Models\Route as AppRoute;
use Modules\CRM\App\Models\RolePermission;

class RolePermissionController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::where('is_deleted',0)->get();
        $selectedRole = null;
        $allMenus = collect();
        $menuPermissions = [];
        $routePermissions = [];
        $extraRoutes = collect();


        if ($request->has('role_id')) {
            $selectedRole = Role::findOrFail($request->role_id);

            // ✅ Get full recursive menu tree (like MenuController)
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

        return view('crm.role_permissions.index', compact('roles', 'selectedRole', 'allMenus', 'menuPermissions', 'routePermissions', 'extraRoutes'));
    }

    // public function updatePermissions(Request $request, Role $role)
    // {
    //     // dd($request, $role);
    //     $submitted = $request->input('permissions', []); // checked menus
    //     // dd($submitted);

    //     // --- Allow checked menus (create if missing) ---
    //     foreach ($submitted as $menuId => $value) {
    //         $menu = Menu::find($menuId);
    //         $permission = RolePermission::firstOrNew([
    //             'role_id' => $role->id,
    //             'menu_id' => $menuId
    //         ]);

    //         $permission->is_allowed = isset($submitted[$menuId]) ? 1 : 0;
    //         // if menu has route_id, also allow it
    //         if (!empty($menu->route_id)) {
    //             $permission->route_id = $menu->route_id;
    //         }
    //         $permission->save();
    //     }

    //     // --- Disable unchecked menus (keep row if exists) ---
    //     $allMenuIds = Menu::pluck('id')->toArray();
    //     $unchecked = array_diff($allMenuIds, array_keys($submitted));

    //     if (!empty($unchecked)) {
    //         RolePermission::where('role_id', $role->id)
    //             ->whereIn('menu_id', $unchecked)
    //             ->update(['is_allowed' => 0]); // unchecked → disallow
    //     }

    //     return redirect()->route('role-permissions.index', ['role_id' => $role->id])
    //         ->with('success', 'Permissions updated successfully!');
    // }

    public function updatePermissions(Request $request, Role $role)
    {
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


