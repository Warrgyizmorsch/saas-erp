<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Shared\App\Models\Route as AppRoute;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admin (Role ID = 1) has complete access to everything
        if ($user->role_id == 1) {
            return $next($request);
        }

        $routeName = $request->route() ? $request->route()->getName() : null;
        $path = '/' . ltrim($request->getPathInfo(), '/');

        // Look for the route in the database
        $routeMatch = AppRoute::where('is_deleted', 0)
            ->where(function ($query) use ($routeName, $path) {
                if ($routeName) {
                    $query->where('route_name', $routeName);
                }
                $query->orWhere('route_name', $path)
                      ->orWhere('route_name', rtrim($path, '/'));
            })
            ->first();

        // If the route is not registered, allow access by default (public/unrestricted routes)
        if (!$routeMatch) {
            return $next($request);
        }

        // 1. Check User Override Permission
        $userPermission = DB::table('user_permissions')
            ->where('user_id', $user->id)
            ->where('route_id', $routeMatch->id)
            ->first();

        if ($userPermission) {
            $isAllowed = (bool) $userPermission->is_allowed;
        } else {
            // 2. Check Role-based Permission
            $rolePermission = DB::table('role_permissions')
                ->where('role_id', $user->role_id)
                ->where('route_id', $routeMatch->id)
                ->first();

            $isAllowed = $rolePermission ? (bool) $rolePermission->is_allowed : false;
        }

        if (!$isAllowed) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
