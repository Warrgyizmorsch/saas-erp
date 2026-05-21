<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->paginate(10);

        return view('crm::crm.category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required'
        ]);

        Category::create([
            'category_name' => $request->category_name,
            'is_active' => 1
        ]);

        return redirect()->route('category.index')
            ->with('success', 'Category added successfully');
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);

        $categories = Category::latest()->paginate(10);

        return view('crm::crm.category.index', compact('category', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_name' => 'required'
        ]);

        $category = Category::findOrFail($id);

        $category->update([
            'category_name' => $request->category_name
        ]);

        return redirect()->route('category.index')
            ->with('success', 'Category updated successfully');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        $category->update([
            'is_active' => 0
        ]);

        return redirect()->back()
            ->with('success', 'Category inactive successfully');
    }

    public function recover($id)
    {
        $category = Category::findOrFail($id);

        $category->update([
            'is_active' => 1
        ]);

        return redirect()->back()
            ->with('success', 'Category active successfully');
    }
}

