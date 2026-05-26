<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('is_delete','0')->get();
        $editCategory = null;
        if ($request->edit) {
            $editCategory = Category::findOrFail($request->edit);
        }

        return view('inventory::categories.index', compact('categories', 'editCategory'));
    }

    public function store(Request $request)
    {
        $request->validate(
     [
            'name' => 'required|string|max:255|unique:inventory_categories,name',
        ],[
            'name.required' => 'Category name is required.',
            'name.unique'   => 'This Category name already exists.',
        ]
            );
        Category::create(['name' => $request->name]);
        return redirect()->route('categories.index')
            ->with('success', 'Category added successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required']);
        $category = Category::findOrFail($id);
        $category->update(['name' => $request->name]);
        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully');
    }

   public function destroy($id)
{
    Category::where('id', $id)->update([
        'is_delete' => 1,
        'deleted_at' => now(),
    ]);

    return redirect()->route('categories.index')
        ->with('success', 'Category deleted successfully');
}

}
