<?php

namespace Modules\Shared\App\Http\Controllers;

use Modules\Shared\App\Http\Controllers\Controller;
use Modules\Shared\App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $roles = $query->paginate(10);

        return view('shared::shared.roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'authority_level' => 'nullable|integer|min:0|max:100',
        ]);

        $loggedInUser = auth()->user();
        $loggedInRole = $loggedInUser->role ?? Role::find($loggedInUser->role_id);
        $loggedInLevel = $loggedInRole ? $loggedInRole->authority_level : 0;

        $authorityLevel = $request->input('authority_level', 0);

        if ($loggedInUser->role_id !== 1 && $authorityLevel >= $loggedInLevel) {
            return redirect()->back()->withErrors(['authority_level' => 'You cannot set an authority level equal to or greater than your own role level.'])->withInput();
        }

        Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'authority_level' => $authorityLevel,
        ]);

        return redirect()->route('roles.index')->with('success', 'Role created successfully!');
    }

    public function edit($id)
    {
        $editRole = Role::findOrFail($id);

        $loggedInRole = auth()->user()->role ?? Role::find(auth()->user()->role_id);
        if (!canManageRole($loggedInRole, $editRole)) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::paginate(10);

        return view('shared::shared.roles.index', compact('roles', 'editRole'));
    }

    public function update(Request $request, Role $role)
    {
        $loggedInRole = auth()->user()->role ?? Role::find(auth()->user()->role_id);
        if (!canManageRole($loggedInRole, $role)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'authority_level' => 'nullable|integer|min:0|max:100',
        ]);

        $loggedInUser = auth()->user();
        $loggedInLevel = $loggedInRole ? $loggedInRole->authority_level : 0;

        $authorityLevel = $request->input('authority_level', 0);

        if ($loggedInUser->role_id !== 1 && $authorityLevel >= $loggedInLevel) {
            return redirect()->back()->withErrors(['authority_level' => 'You cannot set an authority level equal to or greater than your own role level.'])->withInput();
        }

        $role->update([
            'name' => $request->name,
            'authority_level' => $authorityLevel,
        ]);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully!');
    }

    public function destroy(Role $role)
    {
        $loggedInRole = auth()->user()->role ?? Role::find(auth()->user()->role_id);
        if (!canManageRole($loggedInRole, $role)) {
            abort(403, 'Unauthorized action.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully!');
    }
}
