<?php

namespace Modules\Shared\App\Http\Controllers;

use Modules\Shared\App\Models\User;
use Modules\Shared\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        // Logged-in user data
        $user = Auth::user();
        
        // Fetch all active users if the logged-in user is an Admin (role_id = 1)
        $users = [];
        if ($user && $user->role_id === 1) {
            $users = User::where('is_deleted', 0)->select('id', 'name', 'contact_no', 'email')->get();
        }

        return view('shared::shared.profile.index', compact('user', 'users'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'name'   => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'image'  => 'nullable|image|mimes:jpg,jpeg,png,webp,avif|max:2048',
        ]);

        // Avatar Image Upload
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            // Store new avatar in 'users' folder on public disk
            $user->image = $request->file('image')->store('users', 'public');
        }

        $user->name = $request->name;
        $user->contact_no = $request->mobile;
        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully');
    }
    
    public function updatePassword(Request $request)
    {
        $loggedInUser = Auth::user();
        if (!$loggedInUser) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'user_id'  => ['required', 'exists:users,id'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $targetUser = User::findOrFail($validated['user_id']);

        if (!$loggedInUser->canManageUser($targetUser)) {
            return back()->withErrors(['user_id' => 'You do not have permission to change this user\'s password.']);
        }

        $targetUser->password = Hash::make($validated['password']);
        $targetUser->save();

        return back()->with('success', 'Password updated successfully!');
    }
}
