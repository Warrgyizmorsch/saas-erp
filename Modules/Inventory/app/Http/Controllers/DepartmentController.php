<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    // Display all departments with Add form
    public function index()
    {
        $departments = Department::all(); // List for table
        // echo '<pre>';
        // print_r($departments->toArray());
        // exit;
        return view('inventory::departments.index', compact('departments'));
    }

    // Store new department
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_name' => 'required|string|max:255|unique:inventory_departments,department_name',
        ], [
            'department_name.required' => 'Department name is required.',
            'department_name.unique'   => 'This department name already exists.',

        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('departments.index')
                ->withErrors($validator)
                ->withInput()
                ->with('show_add_form', true); // 🔥 IMPORTANT
        }

        Department::create([
            'department_name' => $request->department_name,
            'status' => $request->status ?? 1,
        ]);

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department added successfully!');

        if ($validator->fails()) {

            // first validation error message
            $firstError = $validator->errors()->first();

            return redirect()
                ->route('departments.index')
                ->withErrors($validator)
                ->withInput()
                ->with('error', $firstError)   // 👈 place message ke liye
                ->with('show_add_form', true);
        }

    }

    // Edit a department (same Blade file)
    public function edit($id)
    {
        $department = Department::findOrFail($id); // Department to edit
        $departments = Department::all(); // List for table

        return view('inventory::departments.index', compact('department', 'departments'));
    }

    // Update department
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'department_name' => 'required|string|max:255',
        ], [
            'department_name.required' => 'Department name is required.',
        ]);

        $department->update([
            'department_name' => $request->department_name,
            'status' => $request->status ?? 1,
        ]);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully!');
    }

    // Delete department
    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully!');
    }
}
