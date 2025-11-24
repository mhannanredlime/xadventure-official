<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Filter by user type
        if ($request->filled('user_type')) {
            if ($request->user_type !== 'all') {
                $query->where('user_type', $request->user_type);
            }
            // If 'all' is selected, don't add any user_type filter
        } else {
            // Default to admin users if no filter is applied
            $query->where('user_type', 'admin');
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('slug', $request->role);
            });
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();
        
        // Get filter options
        $userTypes = ['admin' => 'Admin Only', 'customer' => 'Customer Only', 'all' => 'All Types'];
        $roles = Role::all()->pluck('name', 'slug')->toArray();
        
        return view('admin.users.index', compact('users', 'userTypes', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'bio' => 'nullable|string|max:1000',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'admin', // Fixed as admin
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'bio' => $validated['bio'],
        ]);

        // Assign single role
        $user->roles()->sync([$validated['role_id']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('roles');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load('roles');
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'bio' => 'nullable|string|max:1000',
            'role_id' => 'required|exists:roles,id',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'user_type' => 'admin', // Fixed as admin
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'bio' => $validated['bio'],
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        // Assign single role
        $user->roles()->sync([$validated['role_id']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Mark a customer as admin.
     */
    public function markAsAdmin(User $user)
    {
        if ($user->user_type !== 'customer') {
            return redirect()->route('admin.users.index')
                ->with('error', 'User is not a customer.');
        }

        $user->update(['user_type' => 'admin']);
        
        // Assign default admin role if they don't have any roles
        if ($user->roles->isEmpty()) {
            $defaultAdminRole = Role::where('slug', 'admin')->first();
            if ($defaultAdminRole) {
                $user->roles()->sync([$defaultAdminRole->id]);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User has been marked as admin successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deletion of the current user
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user status (if you want to add active/inactive functionality).
     */
    public function toggleStatus(User $user)
    {
        // This would require adding an 'is_active' column to users table
        // For now, we'll just return a message
        return redirect()->route('admin.users.index')
            ->with('info', 'User status toggle functionality not implemented yet.');
    }
}
