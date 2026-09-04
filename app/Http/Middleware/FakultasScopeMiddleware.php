<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FakultasScopeMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * This middleware ensures that faculty-level admins can only access
     * data within their assigned faculty.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Superadmin bypasses all restrictions
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // For admin_fakultas, check if they're trying to access resources
        // outside their faculty
        if ($user->role === 'admin_fakultas' && $user->fakultas_id) {
            // Store the fakultas constraint in the request for controllers to use
            $request->merge([
                'fakultas_scope' => $user->fakultas_id,
                'fakultas_scoped' => true,
            ]);
        }

        return $next($request);
    }
}
