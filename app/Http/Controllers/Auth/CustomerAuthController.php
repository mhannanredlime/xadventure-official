<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class CustomerAuthController extends Controller
{
    public function showLoginForm()
    {
        // Check if customer is already authenticated
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard')
                ->with('info', 'You are already logged in.');
        }
        
        return view('auth.customer.login');
    }

    public function showRegistrationForm()
    {
        // Check if customer is already authenticated
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard')
                ->with('info', 'You are already logged in.');
        }
        
        return view('auth.customer.register');
    }

    public function login(Request $request)
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

            // Check if customer exists
            $customer = Customer::where('email', $credentials['email'])->first();
            
            if (!$customer) {
                return back()->withErrors([
                    'email' => 'No account found with this email address.',
                ])->withInput($request->only('email'));
            }

            // Check if customer has a password (registered account)
            if (!$customer->password) {
                return back()->withErrors([
                    'email' => 'This email is not registered. Please register first.',
                ])->withInput($request->only('email'));
            }

            // Attempt authentication
            if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                
                // Log successful login
                Log::info('Customer login successful', [
                    'customer_id' => $customer->id,
                    'email' => $customer->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return redirect()->intended(route('customer.dashboard'))
                    ->with('success', 'Welcome back, ' . $customer->name . '!');
            }

            // Failed authentication
            Log::warning('Failed customer login attempt', [
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
            Log::error('Customer login error', [
                'message' => $e->getMessage(),
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'email' => 'An error occurred during login. Please try again.',
            ])->withInput($request->only('email'));
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:customers'],
                'phone' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:500'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ], [
                'name.required' => 'Full name is required.',
                'name.max' => 'Name is too long.',
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email address is already registered.',
                'phone.max' => 'Phone number is too long.',
                'address.max' => 'Address is too long.',
                'password.required' => 'Password is required.',
                'password.confirmed' => 'Password confirmation does not match.',
            ]);

            // Create customer
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(), // Auto-verify for simplicity
            ]);

            // Log in the customer
            Auth::guard('customer')->login($customer);

            // Log successful registration
            Log::info('Customer registration successful', [
                'customer_id' => $customer->id,
                'email' => $customer->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('customer.dashboard')
                ->with('success', 'Welcome to ATV/UTV Adventures, ' . $customer->name . '! Your account has been created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors are handled automatically by Laravel
            throw $e;
        } catch (\Exception $e) {
            Log::error('Customer registration error', [
                'message' => $e->getMessage(),
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'email' => 'An error occurred during registration. Please try again.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }
    }

    public function logout(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        if ($customer) {
            Log::info('Customer logout', [
                'customer_id' => $customer->id,
                'email' => $customer->email,
                'ip' => $request->ip(),
            ]);
        }

        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'You have been successfully logged out.');
    }
}
