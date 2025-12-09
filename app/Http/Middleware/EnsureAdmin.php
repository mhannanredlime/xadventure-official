<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            Log::warning('EnsureAdmin: User not authenticated');
            abort(401, 'Unauthenticated');
        }

        $user = $request->user();
        Log::info('EnsureAdmin checking user', ['id' => $user->id, 'type' => $user->user_type, 'is_admin' => $user->is_admin]);

        // Check if user is admin by user_type field (new system)
        if ($user->user_type === 'admin') {
            Log::info('EnsureAdmin: Passed (user_type=admin)');
            return $next($request);
        }

        // Fallback: Check if user has any admin role (Master Admin, Admin, or Manager)
        if ($user->hasAnyRole(['master-admin', 'admin', 'manager'])) {
            Log::info('EnsureAdmin: Passed (hasAnyRole)');
            return $next($request);
        }

        // Legacy fallback: Check old is_admin field
        if ($user->is_admin == 1) {
            Log::info('EnsureAdmin: Passed (is_admin=1)');
            return $next($request);
        }

        // Fallback: Check if user has any admin role (Master Admin, Admin, or Manager)
        // This might trigger lazy loading, so we ensure it's last or we eagerly load roles if needed
        if ($user->hasAnyRole(['master-admin', 'admin', 'manager'])) {
             Log::info('EnsureAdmin: Passed (hasAnyRole - retry)');
            return $next($request);
        }

        Log::warning('EnsureAdmin: Access denied', ['id' => $user->id]);
        abort(403, 'Access denied. Admin privileges required.');
    }
}


