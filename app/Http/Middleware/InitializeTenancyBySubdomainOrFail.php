<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Exceptions\NotASubdomainException;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyBySubdomainOrFail
{
    protected InitializeTenancyBySubdomain $middleware;

    public function __construct()
    {
        $this->middleware = new InitializeTenancyBySubdomain();
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $this->middleware->handle($request, $next);
        } catch (NotASubdomainException $e) {
            // If there's no subdomain, skip tenancy initialization
            // and let the request continue without a tenant context
            abort(404);
        }
    }
}
