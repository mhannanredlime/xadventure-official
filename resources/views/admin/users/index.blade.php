@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">User Management</li>
                        @if (request('user_type') == 'customer')
                            <li class="breadcrumb-item active">Customers</li>
                        @elseif(request('user_type') == 'all')
                            <li class="breadcrumb-item active">All Users</li>
                        @else
                            <li class="breadcrumb-item active">Admins</li>
                        @endif
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        @if (request('user_type') == 'customer')
                            <h2 class="mb-0">Customer Management</h2>
                            <p class="text-muted mb-0">Manage customer users</p>
                        @elseif(request('user_type') == 'all')
                            <h2 class="mb-0">All Users Management</h2>
                            <p class="text-muted mb-0">Manage all users (admin and customer)</p>
                        @else
                            <h2 class="mb-0">Admin User Management</h2>
                            <p class="text-muted mb-0">Manage admin users and their roles/permissions</p>
                        @endif
                    </div>
                    @can('users.create')
                        @if (request('user_type') !== 'customer')
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Add New Admin
                            </a>
                        @endif
                    @endcan
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Name or email...">
                            </div>
                            <div class="col-md-3">
                                <label for="user_type" class="form-label">User Type</label>
                                <select class="form-select" id="user_type" name="user_type">
                                    <option value="admin"
                                        {{ request('user_type') == 'admin' || !request('user_type') ? 'selected' : '' }}>
                                        Admin Only</option>
                                    <option value="customer" {{ request('user_type') == 'customer' ? 'selected' : '' }}>
                                        Customer Only</option>
                                    <option value="all" {{ request('user_type') == 'all' ? 'selected' : '' }}>All Types
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="">All Roles</option>
                                    @foreach ($roles as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ request('role') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-search me-1"></i>Filter
                                </button>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>



                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Type</th>
                                        <th>Roles</th>
                                        <th>Phone</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-3">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <strong>{{ $user->name }}</strong>
                                                        @if ($user->id === auth()->id())
                                                            <span class="badge bg-info ms-2">You</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                <span class="badge {{ $user->getUserTypeBadgeClass() }}">
                                                    {{ $user->getUserTypeDisplayName() }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($user->isAdminUser())
                                                    @forelse($user->roles as $role)
                                                        <span class="badge bg-secondary me-1">{{ $role->name }}</span>
                                                    @empty
                                                        <span class="text-muted">No roles assigned</span>
                                                    @endforelse
                                                @else
                                                    <span class="text-muted">No role-based permissions</span>
                                                @endif
                                            </td>
                                            <td>{{ $user->phone ?? 'N/A' }}</td>
                                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if (auth()->user()->hasPermission('users.view') || auth()->user()->hasRole('master-admin'))
                                                        <a href="{{ route('admin.users.show', $user) }}"
                                                            class="btn btn-sm btn-outline-info" title="View">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    @endif
                                                    @if (auth()->user()->hasPermission('users.edit') || auth()->user()->hasRole('master-admin'))
                                                        <a href="{{ route('admin.users.edit', $user) }}"
                                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endif
                                                    @if ($user->user_type === 'customer')
                                                        <form action="{{ route('admin.users.mark-as-admin', $user) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success"
                                                                title="Mark as Admin"
                                                                onclick="return confirm('Are you sure you want to mark this customer as admin?')">
                                                                <i class="bi bi-person-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if (auth()->user()->hasPermission('users.delete') || auth()->user()->hasRole('master-admin'))
                                                        @if ($user->id !== auth()->id())
                                                            <form action="{{ route('admin.users.destroy', $user) }}"
                                                                method="POST" class="d-inline"
                                                                onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                    title="Delete">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                                No users found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($users->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $users->links('pagination::bootstrap-4') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }

        .btn-group .btn {
            margin-right: 2px;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        .table td {
            vertical-align: middle;
        }

        .table .btn-group {
            white-space: nowrap;
        }
    </style>
@endsection
