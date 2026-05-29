<?php

namespace Modules\Shared\App\Http\Controllers;

use Modules\Shared\App\Http\Controllers\Controller;
use Modules\Shared\App\Models\Route;
use Modules\Shared\App\Models\Menu;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    // Generate flattened menus helper
    private function getFlattenMenus()
    {
        $menus = Menu::with('childrenRecursive')
            ->where('is_deleted', 0)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $flattenMenus = collect();

        $traverse = function ($menus, $prefix = '') use (&$traverse, $flattenMenus) {
            foreach ($menus as $menu) {
                $menu->display_title = $prefix . $menu->title;
                $flattenMenus->push($menu);

                if ($menu->childrenRecursive->isNotEmpty()) {
                    $traverse($menu->childrenRecursive, $prefix . '-- ');
                }
            }
        };

        $traverse($menus);

        return $flattenMenus;
    }

    public function index(Request $request)
    {
        $query = Route::where('is_deleted', 0);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('route_name')) {
            $query->where('route_name', 'like', '%' . $request->route_name . '%');
        }

        if ($request->filled('menu_id')) {
            $query->where('menu_id', $request->menu_id);
        }

        $routes = $query->paginate(10);
        $flattenMenus = $this->getFlattenMenus();

        return view('shared::shared.routes.index', compact('routes', 'flattenMenus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'route_name' => 'required|string|max:255|unique:routes,route_name',
            'method' => 'required|in:GET,POST,PUT,DELETE,PATCH',
            'menu_id' => 'nullable|exists:menus,id',
        ]);
        Route::create([
            'name' => $validated['name'],
            'route_name' => $validated['route_name'],
            'method' => $validated['method'],
            'menu_id' => $validated['menu_id'] ?? null,
            'is_deleted' => 0,
        ]);

        return redirect()->route('routes.index')->with('success', 'Route created successfully!');
    }

    public function edit($id)
    {
        $editRoute = Route::findOrFail($id);
        $routes = Route::where('is_deleted', 0)->paginate(10);
        $flattenMenus = $this->getFlattenMenus();

        return view('shared::shared.routes.index', compact('routes', 'editRoute', 'flattenMenus'));
    }

    public function update(Request $request, Route $route)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'route_name' => 'required|string|max:255|unique:routes,route_name,' . $route->id,
            'method' => 'required|in:GET,POST,PUT,DELETE,PATCH',
            'menu_id' => 'nullable|exists:menus,id',
        ]);

        $route->update($request->only(['name', 'route_name', 'method', 'menu_id']));

        return redirect()->route('routes.index')->with('success', 'Route updated successfully!');
    }

    public function destroy(Route $route)
    {
        $route->update([
            'is_deleted' => 1,
            'updated_at' => now()
        ]);

        return redirect()->route('routes.index')->with('success', 'Route deleted successfully!');
    }
}
