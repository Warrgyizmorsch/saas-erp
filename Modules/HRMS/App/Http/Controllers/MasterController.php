<?php

namespace Modules\HRMS\App\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\HRMS\App\Models\Department;
use Modules\HRMS\App\Models\Designation;
use Modules\Shared\App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MasterController extends Controller
{
    public function departments()
    {
        $departments = Department::orderBy('name')->paginate(10);
        return view('hrms::master.departments', compact('departments'));
    }

    public function designations()
    {
        $designations = Designation::orderBy('name')->paginate(10);
        return view('hrms::master.designations', compact('designations'));
    }

    public function roles()
    {
        $roles = Role::orderBy('name')->paginate(10);
        return view('hrms::master.roles', compact('roles'));
    }

    // DEPARTMENT
    public function storeDepartment(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Department::create($request->only('name', 'short_name'));
        return redirect()->route('master.departments')->with('success', 'Department added successfully!');
    }

    public function updateDepartment(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $dept = Department::findOrFail($id);
        $dept->update($request->only('name', 'short_name', 'is_active'));
        return redirect()->route('master.departments')->with('success', 'Department updated successfully!');
    }

    public function destroyDepartment($id)
    {
        Department::findOrFail($id)->delete();
        return redirect()->route('master.departments')->with('success', 'Department deleted successfully!');
    }

    // DESIGNATION
    public function storeDesignation(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Designation::create($request->only('name', 'short_name'));
        return redirect()->route('master.designations')->with('success', 'Designation added successfully!');
    }

    public function updateDesignation(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $desg = Designation::findOrFail($id);
        $desg->update($request->only('name', 'short_name'));
        return redirect()->route('master.designations')->with('success', 'Designation updated successfully!');
    }

    public function destroyDesignation($id)
    {
        Designation::findOrFail($id)->delete();
        return redirect()->route('master.designations')->with('success', 'Designation deleted successfully!');
    }

    // ROLE
    public function storeRole(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Role::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name, '_'),
        ]);
        return redirect()->route('master.roles')->with('success', 'Role added successfully!');
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $role = Role::findOrFail($id);
        $role->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name, '_'),
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);
        return redirect()->route('master.roles')->with('success', 'Role updated successfully!');
    }

    public function destroyRole($id)
    {
        Role::findOrFail($id)->delete();
        return redirect()->route('master.roles')->with('success', 'Role deleted successfully!');
    }
}
