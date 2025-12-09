<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Check if user is already authenticated
        if (Auth::check()) {
            // Check if user is admin (new system)
            if (Auth::user()->user_type === 'admin') {
                return redirect()->route('admin.reservations.index')
                    ->with('info', 'You are already logged in.');
            }
            // Fallback: Check old is_admin field
            if (Auth::user()->is_admin) {
                return redirect()->route('admin.reservations.index')
                    ->with('info', 'You are already logged in.');
            }
        }
        
        return view('auth.login');
    }

    public function store(Request $request)
    {
        try {
            // Validate input
            $credentials = $request->validate([
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'min:6'],
            ], [
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.max' => 'Email address is too long.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 6 characters.',
            ]);

            // Check if user exists
            $user = User::where('email', $credentials['email'])->first();
            if (!$user) {
                return back()->withErrors([
                    'email' => 'No account found with this email address.',
                ])->withInput($request->only('email'));
            }

            // Check if user is admin (new system)
            if ($user->user_type !== 'admin') {
                // Fallback: Check old is_admin field
                if (!$user->is_admin) {
                    return back()->withErrors([
                        'email' => 'Access denied. Admin privileges required.',
                    ])->withInput($request->only('email'));
                }
            }
            
            // Attempt authentication
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                
                // Log successful login
                Log::info('Admin login successful', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);

                $redirectUrl = route('admin.reservations.index');
                Log::info('Redirecting admin to: ' . $redirectUrl);

                return redirect()->intended($redirectUrl)->with('success', 'Welcome back, ' . $user->name . '!');
            }

            // Failed authentication
            Log::warning('Failed admin login attempt', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->withErrors([
                'password' => 'Invalid password. Please try again.',
            ])->withInput($request->only('email'));

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors are handled automatically by Laravel
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login error', [
                'message' => $e->getMessage(),
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'email' => 'An error occurred during login. Please try again.',
            ])->withInput($request->only('email'));
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            
            if ($user) {
                // Log logout activity
                Log::info('Admin logout', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                ]);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect('/')->with('success', 'You have been successfully logged out.');
            
        } catch (\Exception $e) {
            Log::error('Logout error', [
                'message' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            // Force logout even if there's an error
            Auth::logout();
            $request->session()->invalidate();
            
            return redirect('/')->with('error', 'An error occurred during logout.');
        }
    }
}


