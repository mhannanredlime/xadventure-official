<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            abort(401, 'Unauthenticated');
        }

        $user = $request->user();

        // Check if user is admin by user_type field (new system)
        if ($user->user_type === 'admin') {
            return $next($request);
        }

        // Fallback: Check if user has any admin role (Master Admin, Admin, or Manager)
        if ($user->hasAnyRole(['master-admin', 'admin', 'manager'])) {
            return $next($request);
        }

        // Legacy fallback: Check old is_admin field
        if ($user->is_admin == 1) {
            return $next($request);
        }

        // Fallback: Check if user has any admin role (Master Admin, Admin, or Manager)
        // This might trigger lazy loading, so we ensure it's last or we eagerly load roles if needed
        if ($user->hasAnyRole(['master-admin', 'admin', 'manager'])) {
            return $next($request);
        }

        abort(403, 'Access denied. Admin privileges required.');
    }
}


