<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\App\Models\User;
use Modules\CRM\App\Models\Menu;
use Modules\CRM\App\Models\Route as AppRoute;
use Modules\CRM\App\Models\UserPermission;
use Modules\CRM\App\Models\RolePermission;

class UserPermissionController extends Controller
{
    public function index(Request $request)
    {
        $users = collect();
        $selectedUser = null;
        $allMenus = collect();
        $menuPermissions = [];
        $routePermissions = [];
        $extraRoutes = collect();
        $rolePermissions = ['menus' => [], 'routes' => []];

        if ($request->filled('search')) {
            $users = User::where('email', 'like', '%' . $request->search . '%')->get();
        }

        if ($request->has('user_id')) {
            $selectedUser = User::findOrFail($request->user_id);

            $allMenus = Menu::where('is_deleted', 0)
                ->whereNull('parent_id')
                ->with('childrenRecursive')
                ->orderBy('sort_order')
                ->get();

            $menuPermissions = $selectedUser->userPermissions
                ->whereNotNull('menu_id')
                ->pluck('is_allowed', 'menu_id')
                ->toArray();

            $routePermissions = $selectedUser->userPermissions
                ->whereNotNull('route_id')
                ->pluck('is_allowed', 'route_id')
                ->toArray();

            if ($selectedUser->role) {
                $rolePermissions['menus'] = $selectedUser->role->rolePermissions
                    ->whereNotNull('menu_id')
                    ->pluck('is_allowed', 'menu_id')
                    ->toArray();

                $rolePermissions['routes'] = $selectedUser->role->rolePermissions
                    ->whereNotNull('route_id')
                    ->pluck('is_allowed', 'route_id')
                    ->toArray();
            }

            $extraRoutes = AppRoute::where('is_deleted', 0)
                ->whereNotIn('id', function ($q) {
                    $q->select('route_id')->from('menus')->whereNotNull('route_id');
                })
                ->orderBy('id')
                ->get()
                ->groupBy('menu_id');
        }

        return view('crm.user_permissions.index', compact(
            'users',
            'selectedUser',
            'allMenus',
            'menuPermissions',
            'routePermissions',
            'extraRoutes',
            'rolePermissions'
        ));
    }

    public function updatePermissions(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // --- Role defaults ---
        $roleMenuPermissions = RolePermission::where('role_id', $user->role_id)
            ->whereNotNull('menu_id')
            ->pluck('is_allowed', 'menu_id')
            ->map(fn($v) => (int) $v)
            ->toArray();

        $roleRoutePermissions = RolePermission::where('role_id', $user->role_id)
            ->whereNotNull('route_id')
            ->pluck('is_allowed', 'route_id')
            ->map(fn($v) => (int) $v)
            ->toArray();

        // --- User submitted (only checked appear) ---
        $submittedMenus = $request->input('permissions', []);
        $submittedRoutes = $request->input('permissions_route', []);

        // --- Clear old overrides ---
        UserPermission::where('user_id', $userId)->delete();

        $overrides = [];

        // --- MENUS ---
        $allMenus = Menu::where('is_deleted', 0)->get();
        foreach ($allMenus as $menu) {
            $menuId = $menu->id;
            $userChecked = isset($submittedMenus[$menuId]) ? 1 : 0;
            $roleDefault = $roleMenuPermissions[$menuId] ?? 0; // default deny if no role entry

            // ✅ Only create override if user choice differs from role default
            if ($userChecked != $roleDefault) {
                $overrides[] = [
                    'user_id' => $userId,
                    'menu_id' => $menuId,
                    // ✅ include route_id if the menu has one
                    'route_id' => $menu->route_id ?? null,
                    'is_allowed' => $userChecked,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // --- ROUTES (standalone, not linked to menus) ---
        $allRouteIds = AppRoute::where('is_deleted', 0)
            ->whereNotIn('id', function ($q) {
                $q->select('route_id')->from('menus')->whereNotNull('route_id');
            })
            ->pluck('id')
            ->toArray();

        foreach ($allRouteIds as $routeId) {
            $userChecked = isset($submittedRoutes[$routeId]) ? 1 : 0;
            $roleDefault = $roleRoutePermissions[$routeId] ?? 0;

            if ($userChecked != $roleDefault) {
                $overrides[] = [
                    'user_id' => $userId,
                    'menu_id' => null,
                    'route_id' => $routeId,
                    'is_allowed' => $userChecked,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // --- Save overrides ---
        if ($overrides) {
            UserPermission::insert($overrides);
        }

        return back()->with('success', 'Permissions updated successfully.');
    }

}


