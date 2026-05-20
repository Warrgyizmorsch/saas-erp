<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Models\User;
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
        $users = User::select('id','name','contact_no')->get();

        return view('profile.index', compact('user', 'users'));
    }

     public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'   => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            // 'address'=> 'nullable|string',
            'image'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Image upload

         if ($request->hasFile('image')) {

            // Delete old image (from storage)
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            // Store in same location as user module
            $user->image = $request->file('image')->store('users', 'public');
        }

        $user->name    = $request->name;
        $user->contact_no  = $request->mobile;
        // $user->address = $request->address;
        $user->save();

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully');
    }
    
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
                'user_id' => ['required', 'exists:users,id'], // ensure user exists
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            // Find the user by ID from dropdown
            $user = User::findOrFail($validated['user_id']);

            // Update the password
            $user->password = Hash::make($validated['password']);
            $user->save();

            return back()->with('status', 'password-updated');
    }
 

}

