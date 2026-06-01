<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyBySession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Skip tenancy initialization for central onboarding / company creation routes
        if ($request->is('create-company*')) {
            return $next($request);
        }

        // 2. Allow clearing the tenant context via URL parameter (e.g. ?tenant=clear)
        if ($request->query('tenant') === 'clear') {
            if ($request->hasSession()) {
                $request->session()->forget('tenant_id');
            }
            if (tenant()) {
                tenancy()->end();
            }
            return $next($request);
        }

        // 3. Check if a query parameter 'tenant' is passed to switch or set the tenant context
        if ($request->has('tenant')) {
            $tenantId = $request->query('tenant');
            $tenant = \App\Models\Tenant::find($tenantId);
            if ($tenant) {
                if ($request->hasSession()) {
                    $request->session()->put('tenant_id', $tenant->id);
                }
            }
        }

        // 4. Initialize tenancy if the tenant_id is stored in the session
        if ($request->hasSession() && $request->session()->has('tenant_id')) {
            $tenantId = $request->session()->get('tenant_id');
            $tenant = \App\Models\Tenant::find($tenantId);
            if ($tenant) {
                if (!tenant() || tenant('id') !== $tenantId) {
                    tenancy()->initialize($tenant);
                }
            }
        }

        // 5. Fallback: Initialize tenancy by domain if not already initialized (e.g. when accessing via subdomain directly)
        if (!tenant()) {
            $host = $request->getHost();
            $domainRecord = \DB::table('domains')->where('domain', $host)->first();
            if ($domainRecord) {
                $tenant = \App\Models\Tenant::find($domainRecord->tenant_id);
                if ($tenant) {
                    tenancy()->initialize($tenant);
                    if ($request->hasSession()) {
                        $request->session()->put('tenant_id', $tenant->id);
                    }
                }
            }
        }

        return $next($request);
    }
}
