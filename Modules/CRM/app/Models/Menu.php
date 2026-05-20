<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'icon',
        'parent_id',
        'route_id',
        'sort_order',
        'is_deleted',
    ];

    /**
     * Get allowed menus for a specific user (role + user override)
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Support\Collection
     */
    public static function getMenusForUser($user)
    {
        $roleId = $user->role_id;

        // Load top-level menus with children + route
        $menus = self::with(['childrenRecursive', 'route'])
            ->whereNull('parent_id')
            ->where('is_deleted', false)
            ->orderBy('sort_order')
            ->get();

        // Recursive filter (declare type as Closure)
        $filterMenu = function ($menu) use ($user, $roleId, &$filterMenu) {
            // 🔹 Check role permission
            $rolePermission = $menu->rolePermissions()
                ->where('role_id', $roleId)
                ->where('is_allowed', 1)
                ->exists();

            // 🔹 Check user override
            $userPermission = $menu->userPermissions()
                ->where('user_id', $user->id)
                ->first();

            $isAllowed = $userPermission
                ? (bool) $userPermission->is_allowed
                : $rolePermission;

            // 🔹 Recurse into children
            $filteredChildren = collect();
            foreach ($menu->childrenRecursive as $child) {
                $keep = $filterMenu($child); // ✅ now always callable
                if ($keep) {
                    $filteredChildren->push($keep);
                }
            }

            // Attach filtered children
            $menu->setRelation('children', $filteredChildren);
            $menu->setRelation('childrenRecursive', $filteredChildren);

            // 🔹 Keep this menu if allowed OR has allowed children
            if ($isAllowed || $filteredChildren->isNotEmpty()) {
                if (!$isAllowed) {
                    $menu->setRelation('route', null);
                }
                return $menu;
            }

            return null;
        };

        // Apply to roots
        return $menus
            ->map(fn($m) => $filterMenu($m))
            ->filter()
            ->values();
    }


    /**
     * Parent Menu
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id')
            ->where('is_deleted', 0);
    }

    /**
     * Child Menus
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')
            ->where('is_deleted', 0);
    }

    // Recursive relation
    public function childrenRecursive()
    {
        return $this->hasMany(Menu::class, 'parent_id')
            ->where('is_deleted', 0)
            ->orderBy('sort_order', 'asc') // 👈 add ordering here
            ->with('childrenRecursive');
    }

    // Menu belongs to Route
    public function route()
    {
        return $this->belongsTo(Route::class)
            ->where('is_deleted', 0);
    }

    // In Menu.php model
    public function routesForPermission()
    {
        return $this->hasMany(Route::class, 'menu_id', 'id')
            ->where('is_deleted', 0);
    }


    // Menu has many RolePermissions
    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class);
    }

    // Menu has many UserPermissions
    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class);
    }
}
