<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetTenantUrlDefaults
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant('id')) {
            URL::defaults([
                'tenant' => tenant('id'),
            ]);
        } elseif (auth()->check() && auth()->user()->tenant_id) {
            URL::defaults([
                'tenant' => auth()->user()->tenant_id,
            ]);
        }

        return $next($request);
    }
}
