@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Admin User Details: {{ $user->name }}</h2>
                <div>
                    @can('users.edit')
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary me-2">
                        <i class="bi bi-pencil me-2"></i>Edit User
                    </a>
                    @endcan
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Users
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">User Information</h5>
                        </div>
                        <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Full Name</label>
                                    <p class="form-control-plaintext">{{ $user->name }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email Address</label>
                                    <p class="form-control-plaintext">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">User Type</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge {{ $user->getUserTypeBadgeClass() }}">
                                            {{ $user->getUserTypeDisplayName() }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Phone Number</label>
                                        <p class="form-control-plaintext">{{ $user->phone ?? 'Not provided' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Address</label>
                                        <p class="form-control-plaintext">{{ $user->address ?? 'Not provided' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Bio</label>
                                <p class="form-control-plaintext">{{ $user->bio ?? 'No bio provided' }}</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Created At</label>
                                        <p class="form-control-plaintext">{{ $user->created_at->format('F d, Y \a\t g:i A') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Last Updated</label>
                                        <p class="form-control-plaintext">{{ $user->updated_at->format('F d, Y \a\t g:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Roles & Permissions</h5>
                        </div>
                        <div class="card-body">
                            @if($user->isAdminUser())
                            <div class="mb-3">
                                <label class="form-label fw-bold">Assigned Roles</label>
                                @forelse($user->roles as $role)
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-primary me-2">{{ $role->name }}</span>
                                        @if($role->is_system)
                                            <span class="badge bg-info">System</span>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mb-2">{{ $role->description }}</small>
                                @empty
                                    <p class="text-muted">No roles assigned</p>
                                @endforelse
                            </div>
                            @else
                            <div class="mb-3">
                                <label class="form-label fw-bold">Permissions</label>
                                <p class="text-muted">Customer users have no role-based permissions</p>
                            </div>
                            @endif

                            @if($user->isAdminUser())
                            <div class="mb-3">
                                <label class="form-label fw-bold">Permissions</label>
                                @php
                                    $permissions = $user->permissions()->groupBy('module');
                                @endphp
                                @forelse($permissions as $module => $modulePermissions)
                                    <div class="mb-2">
                                        <strong class="text-primary">{{ $module }}</strong>
                                        <div class="ms-3">
                                            @foreach($modulePermissions as $permission)
                                                <span class="badge bg-secondary me-1 mb-1">{{ $permission->name }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted">No permissions assigned</p>
                                @endforelse
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($user->id !== auth()->id())
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0 text-danger">Danger Zone</h5>
                        </div>
                        <div class="card-body">
                            @can('permission', 'users.delete')
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash me-2"></i>Delete User
                                </button>
                            </form>
                            @else
                            <p class="text-muted small">You don't have permission to delete users.</p>
                            @endcan
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
