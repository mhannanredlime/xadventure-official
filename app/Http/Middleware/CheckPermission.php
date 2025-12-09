<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user()) {
            abort(401, 'Unauthenticated');
        }

        $user = $request->user();
        
        if (!$user->hasPermission($permission)) {
            Log::warning('CheckPermission: Denied', ['user_id' => $user->id, 'permission' => $permission]);
            // dd($permission);
            abort(403, 'Insufficient permissions');
        }

        Log::info('CheckPermission: Granted', ['user_id' => $user->id, 'permission' => $permission]);
        return $next($request);
    }
}
