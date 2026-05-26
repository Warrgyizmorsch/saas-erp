<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    // 1️⃣ List all units
    public function index(Request $request)
    {
        $query = Unit::query();

        if ($request->filled('name')) {
            $query->where('name', $request->name);
        }

        if ($request->filled('status')) {
            $query->where('is_deleted', $request->status);
        }

        $units = $query->orderBy('id', 'desc')->get();
        return view('inventory::units.index', compact('units'));
    }

    // 2️⃣ Show form to create new unit (optional if you want separate page)
    public function create()
    {
        return view('inventory::units.create');
    }

    // 3️⃣ Store new unit
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:units,name',
        ], [
            'name.required' => 'Unit name is required.',
            'name.unique'   => 'This Unit name already exists.',
        ]);

        Unit::create([
            'name' => $request->name,
            'is_deleted' => 0
        ]);

        return redirect()->back()->with('success', 'Unit added successfully!');
    }

    // 4️⃣ Soft delete unit
    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->is_deleted = 1;
        $unit->save();

        return redirect()->back()->with('success', 'Unit deleted successfully!');
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        $units = Unit::where('is_deleted', 0)->get(); // for table
        return view('inventory::units.index', compact('unit', 'units'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:units,name,' . $id,
        ]);

        $unit = Unit::findOrFail($id);
        $unit->update([
            'name' => $request->name
        ]);

        return redirect()->route('units.index')->with('success', 'Unit updated successfully!');
    }

    public function toggle($id)
    {
        $unit = Unit::findOrFail($id);

        // Toggle is_deleted: if 0 => 1 (soft delete), if 1 => 0 (recover)
        $unit->is_deleted = $unit->is_deleted == 0 ? 1 : 0;
        $unit->save();

        $message = $unit->is_deleted == 0 ? 'Unit recovered successfully!' : 'Unit deleted successfully!';
        return redirect()->back()->with('success', $message);
    }
}
