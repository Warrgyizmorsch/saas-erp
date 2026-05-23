<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        if (tenant('id')) {
            $maxUsers = tenant('max_users');
            if ($maxUsers) {
                $currentUsersCount = User::where('is_deleted', 0)->count();
                if ($currentUsersCount >= $maxUsers) {
                    abort(403, 'Registration is currently blocked because this tenant has reached the maximum allowed user limit.');
                }
            }
        }
        $roles = (tenant('id') && class_exists('\Modules\Shared\App\Models\Role')) ? \Modules\Shared\App\Models\Role::all() : collect();
        return view('auth.register', compact('roles'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (tenant('id')) {
            $maxUsers = tenant('max_users');
            if ($maxUsers) {
                $currentUsersCount = User::where('is_deleted', 0)->count();
                if ($currentUsersCount >= $maxUsers) {
                    throw ValidationException::withMessages([
                        'email' => 'Registration is currently blocked because this tenant has reached the maximum allowed user limit.',
                    ]);
                }
            }
        }

        $emailRules = ['required', 'string', 'lowercase', 'email', 'max:255'];
        if (tenant('id')) {
            $emailRules[] = 'unique:users,email,NULL,id,tenant_id,' . tenant('id');
        } else {
            $emailRules[] = 'unique:users,email';
        }

        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        if (tenant('id')) {
            $validationRules['role_id'] = ['required', 'exists:roles,id'];
            $validationRules['country_code'] = ['required', 'string', 'max:5'];
            $validationRules['contact_no'] = ['required', 'string', 'max:20'];
        }

        $request->validate($validationRules);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        if (tenant('id')) {
            $userData['role_id'] = $request->role_id;
            $userData['country_code'] = $request->country_code;
            $userData['contact_no'] = $request->contact_no;
        }

        $user = User::create($userData);


        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}

