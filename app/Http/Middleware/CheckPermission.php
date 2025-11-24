<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

        if (!$request->user()->hasPermission($permission)) {
            abort(403, 'Insufficient permissions');
        }

        return $next($request);
    }
}
