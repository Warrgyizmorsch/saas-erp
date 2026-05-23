<?php

use Illuminate\Support\Facades\Route;

// Public tenant profile page
Route::get('/', function () {

    return view('tenantprofile', [
        'tenant' => tenant()
    ]);

});

// Protected routes
Route::middleware([
    'auth',
    'verified'
])->group(function () {

    Route::get('/dashboard', function () {
        $tenant = tenant();
        $domain = optional($tenant->domains->first())->domain ?? request()->getHost();
        
        $isCrmEnabled = function_exists('tenant_module_enabled') ? tenant_module_enabled('CRM') : false;
        $isInventoryEnabled = function_exists('tenant_module_enabled') ? tenant_module_enabled('Inventory') : false;
        $isHrmsEnabled = function_exists('tenant_module_enabled') ? tenant_module_enabled('HRMS') : false;

        $totalUsers = class_exists('\Modules\Shared\App\Models\User') ? \Modules\Shared\App\Models\User::count() : 0;
        $activeUsersCount = class_exists('\Modules\Shared\App\Models\User') ? \Modules\Shared\App\Models\User::where('is_deleted', 0)->count() : 0;
        
        $activeSessions = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->where('users.tenant_id', tenant('id'))
            ->count();

        $crmLeadsCount = 0;
        if ($isCrmEnabled && class_exists('\Modules\CRM\App\Models\Leads')) {
            $crmLeadsCount = \Modules\CRM\App\Models\Leads::whereHas('owner')->count();
        }

        $recentLogins = class_exists('\Modules\Shared\App\Models\LoginHistory') 
            ? \Modules\Shared\App\Models\LoginHistory::whereHas('user')->with('user')->orderBy('id', 'desc')->limit(5)->get() 
            : collect();

        $recentUsers = class_exists('\Modules\Shared\App\Models\User') 
            ? \Modules\Shared\App\Models\User::with('role')->where('is_deleted', 0)->orderBy('id', 'desc')->limit(5)->get() 
            : collect();


        return view('dashboard', compact(
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

});