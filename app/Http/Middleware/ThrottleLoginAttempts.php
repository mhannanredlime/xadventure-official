<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLoginAttempts
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts())) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->withInput($request->only('email'));
        }

        RateLimiter::hit($key, $this->decayMinutes() * 60);

        $response = $next($request);

        if ($response->getStatusCode() === 302 && $request->session()->has('errors')) {
            // Login failed, increment the rate limiter
            RateLimiter::hit($key, $this->decayMinutes() * 60);
        } else {
            // Login successful, clear the rate limiter
            RateLimiter::clear($key);
        }

        return $response;
    }

    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
    }

    protected function maxAttempts(): int
    {
        return 5; // Maximum 5 attempts
    }

    protected function decayMinutes(): int
    {
        return 15; // Lock for 15 minutes
    }
}
