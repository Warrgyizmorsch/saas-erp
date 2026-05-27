<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Modules\Shared\App\Http\Controllers\UserController;
use Modules\Shared\App\Http\Controllers\RouteController;
use Modules\Shared\App\Http\Controllers\RoleController;
use Modules\Shared\App\Http\Controllers\MenuController;
use Modules\Shared\App\Http\Controllers\RolePermissionController;
use Modules\Shared\App\Http\Controllers\UserPermissionController;
use Modules\Shared\App\Http\Controllers\ProfileController;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    'auth',
    'verified',
    'check.permission'
])->group(function () {

    Route::get('/dashboard', function () {
        $tenant = tenant();
        $domain = optional($tenant->domains->first())->domain ?? request()->getHost();
        $isCrmEnabled = tenant_module_enabled('CRM');
        $isInventoryEnabled = tenant_module_enabled('Inventory');
        $isHrmsEnabled = tenant_module_enabled('HRMS');

        $totalUsers = \Modules\Shared\App\Models\User::count();
        $activeUsersCount = \Modules\Shared\App\Models\User::where('is_deleted', 0)->count();

        // Total active sessions
        $activeSessions = \DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->where('users.tenant_id', tenant('id'))
            ->count();

        $crmLeadsCount = 0;
        if ($isCrmEnabled && class_exists('\Modules\CRM\App\Models\Leads')) {
            $crmLeadsCount = \Modules\CRM\App\Models\Leads::whereHas('owner')->count();
        }

        // Recent login history with user info
        $recentLogins = \Modules\Shared\App\Models\LoginHistory::whereHas('user')
            ->with('user')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        // Recent active users
        $recentUsers = \Modules\Shared\App\Models\User::with('role')
            ->where('is_deleted', 0)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();


        return view('shared::dashboard', compact(
            'tenant',
            'domain',
            'isCrmEnabled',
            'isInventoryEnabled',
            'isHrmsEnabled',
            'totalUsers',
            'activeUsersCount',
            'activeSessions',
            'crmLeadsCount',
            'recentLogins',
            'recentUsers'
        ));
    })->name('dashboard');

    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/store', [UserController::class, 'store'])->name('store');
        Route::get('/edit/{user}', [UserController::class, 'edit'])->name('edit');
        Route::put('/update/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/destroy/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::get('/session', [UserController::class, 'indexLog'])->name('session');
        Route::post('/{user}/logout', [UserController::class, 'forceLogout'])->name('logout');
        Route::get('/{user}/history', [UserController::class, 'userHistory'])->name('history');
        Route::get('/{user}/lead-history', [UserController::class, 'leadHistory'])->name('leadHistory');
    });

    // Routes management
    Route::resource('routes', RouteController::class);

    // Roles
    Route::resource('roles', RoleController::class);

    // Menus
    Route::resource('menus', MenuController::class);

    // Role permissions
    Route::resource('role-permissions', RolePermissionController::class);
    Route::post('roles/{role}/permissions', [RolePermissionController::class, 'updatePermissions'])
        ->name('role-permissions-id.update');

    // User permissions
    Route::resource('user-permissions', UserPermissionController::class);
    Route::post('users/{user}/permissions', [UserPermissionController::class, 'updatePermissions'])
        ->name('user-permissions-id.update');

    Route::post('/save-work-time', [UserController::class, 'saveWorkTime'])->name('save.work.time');
    Route::patch('/user/{id}/status', [UserController::class, 'updateStatus'])->name('users.userUpdateStatus');

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::post('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });
});