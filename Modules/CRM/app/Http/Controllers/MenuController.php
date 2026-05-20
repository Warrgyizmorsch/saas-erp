<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Http\Controllers\Controller;
use Modules\CRM\App\Models\Menu;
use Modules\CRM\App\Models\Route;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        // Filtered and paginated menus for table/list view
        $query = Menu::where('is_deleted', 0)->whereNull('parent_id');        

        $menus = $query->with('children.route')->orderBy('sort_order')->paginate(10);

        // Full menu tree for dropdown & display
        $allMenus = Menu::where('is_deleted', 0)
                ->whereNull('parent_id')
                ->with('childrenRecursive') // 🔁 recursive children
                ->orderBy('sort_order')
                ->get();


        return view('crm.menus.index', compact('menus', 'allMenus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'icon'      => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'route_id'  => 'nullable|exists:routes,id',
            'sort_order'=> 'nullable|integer',
        ]);

        Menu::create(array_merge($validated, ['is_deleted' => 0]));

        return redirect()->route('menus.index')->with('success', 'Menu created successfully!');
    }

    public function edit($id)
    {
        $editMenu = Menu::findOrFail($id);

        $menus = Menu::where('is_deleted', 0)
                     ->whereNull('parent_id')
                     ->with('children.route')
                     ->orderBy('sort_order')
                     ->paginate(10);

        $allMenus = Menu::where('is_deleted', 0)
                        ->whereNull('parent_id')
                        ->with('children.route')
                        ->orderBy('sort_order')
                        ->get();

        return view('crm.menus.index', compact('menus', 'allMenus', 'editMenu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'icon'      => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'route_id'  => 'nullable|exists:routes,id',
            'sort_order'=> 'nullable|integer',
        ]);

        $menu->update($validated);

        return redirect()->route('menus.index')->with('success', 'Menu updated successfully!');
    }

    public function destroy(Menu $menu)
    {
        $menu->update(['is_deleted' => 1, 'updated_at' => now()]);

        return redirect()->route('menus.index')->with('success', 'Menu deleted successfully!');
    }
}


