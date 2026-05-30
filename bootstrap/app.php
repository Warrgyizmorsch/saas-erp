<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Determine central domains dynamically
            $host = request()->getHost();
            $centralDomains = config('tenancy.central_domains', ['127.0.0.1', 'localhost']);
            
            // Treat host as a central domain if it is in central domains or is a bare IP address
            $isCentralDomain = in_array($host, $centralDomains) || filter_var($host, FILTER_VALIDATE_IP) !== false;

            if ($isCentralDomain) {
                // Central Domain - load standard web and auth routes
                Route::middleware('web')
                    ->group(base_path('routes/web.php'));
            } else {
                // Tenant Subdomain - load tenant routes and auth routes wrapped in InitializeTenancyByDomain
                Route::middleware([
                    'web',
                    \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
                ])->group(function () {
                    if (file_exists(base_path('routes/tenant.php'))) {
                        require base_path('routes/tenant.php');
                    }
                    if (file_exists(base_path('routes/auth.php'))) {
                        require base_path('routes/auth.php');
                    }
                });
            }
        },
    )

    ->withMiddleware(function ($middleware) {
        $middleware->alias([
            'module.enabled' => \App\Http\Middleware\ModuleEnabled::class,
            'check.permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
