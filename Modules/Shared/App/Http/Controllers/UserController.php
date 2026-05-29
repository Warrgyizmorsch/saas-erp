<?php

namespace Modules\Shared\App\Http\Controllers;

use Modules\Shared\App\Models\Role;
use Modules\Shared\App\Models\User;
use Modules\Shared\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Shared\App\Models\LoginHistory;
use Modules\CRM\App\Models\LeadHistory;
use Modules\Shared\App\Models\UserWorkLog;
use DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('tenant_id', tenant('id'))->where('is_deleted', 0);

        // Search across name, email, contact_no
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('contact_no', 'LIKE', "%{$search}%");
            });
        }

        // Filter by role if provided
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        $users = $query->paginate(10);

        $roles = Role::get();

        $todayLog = UserWorkLog::where('user_id', auth()->id())
            ->where('date', now('Asia/Kolkata')->toDateString())
            ->first();

        $existingSeconds = $todayLog ? $todayLog->active_seconds : 0;

        return view('shared::shared.users.index', compact('users', 'roles', 'existingSeconds'));
    }

    public function create()
    {
        // Enforce subscription package user limits
        $maxUsers = tenant('max_users');
        if ($maxUsers) {
            $currentUsersCount = User::where('tenant_id', tenant('id'))->where('is_deleted', 0)->count();
            if ($currentUsersCount >= $maxUsers) {
                return redirect()->route('users.index')->with('error', 'You have reached the maximum user limit of ' . $maxUsers . ' for your current package. Please upgrade your plan to add more users.');
            }
        }

        $roles = Role::get();

        return view('shared::shared.users.store', compact('roles'));
    }

    public function store(Request $request)
    {
        // Enforce subscription package user limits on form submit
        $maxUsers = tenant('max_users');
        if ($maxUsers) {
            $currentUsersCount = User::where('tenant_id', tenant('id'))->where('is_deleted', 0)->count();
            if ($currentUsersCount >= $maxUsers) {
                return redirect()->route('users.index')->with('error', 'Action blocked: You have reached the maximum user limit of ' . $maxUsers . ' for your current package.');
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,NULL,id,tenant_id,' . tenant('id'),
            'role_id' => 'required|exists:roles,id',
            'country_code' => 'nullable|string|max:5',
            'contact_no' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif|max:2048',
            'city' => 'required|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('users', 'public');
            $validated['image'] = $path;
        }

        $validated['password'] = Hash::make('user@123');
        $validated['tenant_id'] = tenant('id');

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        if ($user->tenant_id !== tenant('id')) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::get();
        return view('shared::shared.users.store', compact('roles', 'user'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->tenant_id !== tenant('id')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id . ',id,tenant_id,' . tenant('id'),
            'country_code' => 'required',

            'contact_no' => 'required',
            'role_id' => 'required|exists:roles,id',
            'image' => 'nullable|image|max:2048',
            'city' => 'required|string|max:255',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $data = $request->only(['name', 'email', 'country_code', 'contact_no', 'role_id', 'city']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('users', 'public');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        if ($user->tenant_id !== tenant('id')) {
            abort(403, 'Unauthorized action.');
        }

        $user->update(['is_deleted' => 1]);

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }

    public function indexLog(Request $request)
    {
        // Step 1: Get all session user_ids (currently logged-in users)
        $loggedInUserIds = DB::table('sessions')->pluck('user_id')->unique()->filter();

        $userRole = Role::get();

        // Step 2: Build user query with loginHistories (scoped by tenant)
        $query = User::where('tenant_id', tenant('id'))->with('loginHistories');

        // Conditionally apply role filter
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id); // allow role_id = 2 if requested
        } else {
            $query->where('role_id', '!=', 2); // default case, exclude role_id = 2
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        // Date filter
        if ($request->filled('from') && $request->filled('to')) {
            $from = \Carbon\Carbon::parse($request->from)->startOfDay();
            $to = \Carbon\Carbon::parse($request->to)->endOfDay();
            $query->whereHas('loginHistories', fn($q) => $q->whereBetween('created_at', [$from, $to]));
        } elseif ($request->filled('from')) {
            $date = \Carbon\Carbon::parse($request->from)->toDateString();
            $query->whereHas('loginHistories', fn($q) => $q->whereDate('created_at', $date));
        }

        // Step 3: Apply ordering (push logged-in users to top, then latest users)
        $users = $query
            ->orderByRaw("FIELD(id, " . ($loggedInUserIds->isNotEmpty() ? $loggedInUserIds->implode(',') : '0') . ") DESC")
            ->latest()
            ->paginate(10)
            ->appends($request->query()); // keep filters in pagination links

        // Step 4: Fetch sessions for paginated users only
        $userIds = $users->pluck('id');
        $sessions = DB::table('sessions')->whereIn('user_id', $userIds)->get()->keyBy('user_id');

        $activityLogs = UserWorkLog::with('user')
            ->when($request->filled('from') && $request->filled('to'), function ($q) use ($request) {
                $q->whereBetween('date', [$request->from, $request->to]);
            })
            ->latest()
            ->paginate(10, ['*'], 'activity_page');

        return view('shared::shared.users.loginHistory', compact('users', 'sessions', 'userRole', 'activityLogs'));
    }

    public function forceLogout(User $user)
    {
        // Update logout_at first
        LoginHistory::where('user_id', $user->id)
            ->whereNull('logout_at')
            ->latest('created_at')
            ->first()
                ?->update([
                'logout_at' => now(),
                'user_agent' => request()->userAgent()
            ]);

        // Now delete all sessions for this user
        DB::table('sessions')->where('user_id', $user->id)->delete();

        return back()->with('success', 'User has been logged out.');
    }

    public function userHistory($userId)
    {
        $user = User::with([
            'loginHistories' => function ($query) {
                $query->orderBy('id', 'desc');
            }
        ])->findOrFail($userId);

        $sessions = DB::table('sessions')->get()->keyBy('user_id');

        return view('shared::shared.users.history', compact('user', 'sessions'));
    }

    public function filterLoginHistory(Request $request)
    {
        $loggedInUserIds = DB::table('sessions')->pluck('user_id')->unique()->filter();

        $query = User::where('tenant_id', tenant('id'))->with('loginHistories');
        $userRole = Role::get();

        // Conditionally apply role filter
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id); // allow role_id = 2 here
        } else {
            $query->where('role_id', '!=', 2); // default case, exclude role_id = 2
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        // Date filter
        if ($request->filled('from') && $request->filled('to')) {
            $from = \Carbon\Carbon::parse($request->from)->startOfDay();
            $to = \Carbon\Carbon::parse($request->to)->endOfDay();
            $query->whereHas('loginHistories', fn($q) => $q->whereBetween('created_at', [$from, $to]));
        } elseif ($request->filled('from')) {
            $date = \Carbon\Carbon::parse($request->from)->toDateString();
            $query->whereHas('loginHistories', fn($q) => $q->whereDate('created_at', $date));
        }

        $users = $query
            ->orderByRaw("FIELD(id, " . ($loggedInUserIds->isNotEmpty() ? $loggedInUserIds->implode(',') : '0') . ") DESC")
            ->latest()
            ->paginate(20)
            ->appends(request()->query());

        $userIds = $users->pluck('id');
        $sessions = DB::table('sessions')->whereIn('user_id', $userIds)->get()->keyBy('user_id');

        return view('shared::shared.users.userList', compact('users', 'sessions', 'userRole'))->render();
    }

    public function leadHistory(Request $request, $userId)
    {
        $user = User::where('tenant_id', tenant('id'))->findOrFail($userId);

        $date = $request->get('date', now()->toDateString());

        // get login sessions for the selected date
        $sessions = LoginHistory::where('user_id', $user->id)
            ->whereDate('created_at', $date) // created_at is login time
            ->orderBy('created_at', 'asc')
            ->get();

        // get lead changes for the selected date
        $leadHistories = LeadHistory::where('user_id', $user->id)
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('shared::shared.users.leadHistory', compact('user', 'sessions', 'leadHistories', 'date'));
    }

    public function saveWorkTime(Request $request)
    {
        try {
            $request->validate([
                'active_time_seconds' => 'required|integer|min:0'
            ]);

            $userId = auth()->id();
            if (!$userId) {
                return response()->json(['status' => 'error', 'message' => 'User not logged in'], 401);
            }

            $nowIST = now('Asia/Kolkata');
            $currentHour = $nowIST->hour;

            // Check 10 AM to 7:05 PM in IST
            if ($currentHour < 10 || ($currentHour >= 19 && $nowIST->minute > 5)) {
                return response()->json(['status' => 'ignored', 'message' => "Outside working hours"], 200);
            }

            $todayLog = UserWorkLog::firstOrCreate(
                ['user_id' => $userId, 'date' => $nowIST->toDateString()],
                ['active_seconds' => 0]
            );

            if ($request->active_time_seconds > $todayLog->active_seconds) {
                $todayLog->update(['active_seconds' => $request->active_time_seconds]);
                return response()->json(['status' => 'success', 'time' => $request->active_time_seconds]);
            }

            return response()->json(['status' => 'ignored', 'message' => 'Older time received, ignored.']);
        } catch (\Exception $e) {
            \Log::error('WorkTimer DB Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server error'], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|in:0,1'
        ]);

        $user = User::where('tenant_id', tenant('id'))->findOrFail($id);
        $user->is_active = $request->is_active;
        $user->save();

        return back()->with('success', 'User status updated!');
    }
}
