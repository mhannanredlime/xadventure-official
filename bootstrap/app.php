<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'customer' => \App\Http\Middleware\EnsureCustomer::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Configure custom error pages
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return response()->view('errors.404', [], 404);
        });
        
        $exceptions->renderable(function (\Illuminate\Database\QueryException $e) {
            return response()->view('errors.500', [], 500);
        });
        
        $exceptions->renderable(function (\Throwable $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 600) {
                return response()->view('errors.generic', ['exception' => $e], $e->getCode());
            }
        });
    })->create();
