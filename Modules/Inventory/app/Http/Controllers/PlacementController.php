<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Placement;
use Illuminate\Http\Request;

class PlacementController extends Controller
{
    public function index(Request $request)
    {
        $query = Placement::select('id', 'name')
            ->orderBy('id');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $placements = $query->get();
        return view('inventory::placement.index', compact('placements'));
    }


    public function create()
    {

        return view('inventory::placement.index');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'name'      => 'required|string|max:255|unique:placements,name',
            ],
            [
                'name.required' => 'Placement name is required.',
                'name.unique'   => 'This Placement name already exists.',
            ]
        );

        Placement::create([
            'name' => $request->name,
        ]);

        return redirect()->route('placement.index')
            ->with('success', 'placement created successfully');
    }

    public function edit($id)
    {
        $placement = Placement::findOrFail($id);
        $placements = Placement::select('id', 'name')->orderBy('id')->get();
        return view('inventory::placement.index', compact('placements', 'placement'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(
            [
                'name'      => 'required|string',
            ],
            [
                'name.required' => 'Placement name is required.',

            ]
        );
        $placement = Placement::findOrFail($id);
        $placement->update([
            'name' => $request->name,
        ]);

        return redirect()->route('placement.index')
            ->with('success', 'placement updated successfully');
    }
    public function destroy($id)
    {
        $placement = Placement::findOrFail($id);

        $placement->delete();

        return redirect()
            ->route('placement.index')
            ->with('success', 'placement deleted successfully');
    }
}
