@extends('layouts.admin')

@section('title', 'Role Details')
@section('content')
    <div class="container-fluid mb-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Role Details: {{ $role->name }}</h2>
                    <div>
                        <x-admin.button-link href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary me-2"
                            icon="bi-pencil" text="Edit Role" can="roles.edit" />
                        <x-admin.button-link href="{{ route('admin.roles.permissions', $role) }}"
                            class="btn btn-warning me-2" icon="bi-gear" text="Manage Permissions" can="roles.edit" />
                        <x-admin.button-link href="{{ route('admin.roles.index') }}" class="btn btn-secondary"
                            icon="bi-arrow-left" text="Back to Roles" />
                    </div>
                </div>

                @if ($role->is_system)
                    <div class="alert alert-info mb-4" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        This is a system role and cannot be deleted.
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Role Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Role Name</label>
                                    <p class="form-control-plaintext">{{ $role->name }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Slug</label>
                                    <p class="form-control-plaintext"><code>{{ $role->slug }}</code></p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <p class="form-control-plaintext">{{ $role->description ?? 'No description provided' }}
                            </p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Type</label>
                                    <p class="form-control-plaintext">
                                        @if ($role->is_system)
                                            <span class="badge bg-warning">System Role</span>
                                        @else
                                            <span class="badge bg-success">Custom Role</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Created At</label>
                                    <p class="form-control-plaintext">
                                        {{ $role->created_at->format('F d, Y \a\t g:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Total Permissions</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-info fs-6">{{ $role->permissions->count() }}</span>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Users with this Role</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-secondary fs-6">{{ $role->users->count() }}</span>
                            </p>
                        </div>

                        @if ($role->users->count() > 0)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Users</label>
                                <div class="list-group">
                                    @foreach ($role->users as $user)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $user->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                            @if ($user->id === auth()->id())
                                                <span class="badge bg-info">You</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if (!$role->is_system)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0 text-danger">Danger Zone</h5>
                        </div>
                        <div class="card-body">
                            @can('permission', 'roles.delete')
                                @if ($role->users->count() === 0)
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this role? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash me-2"></i>Delete Role
                                        </button>
                                    </form>
                                @else
                                    <p class="text-muted small">Cannot delete role that is assigned to users. Remove all
                                        users from this role first.</p>
                                @endif
                            @else
                                <p class="text-muted small">You don't have permission to delete roles.</p>
                            @endcan
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Assigned Permissions</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $permissions = $role->permissions->groupBy('module');
                        @endphp
                        @forelse($permissions as $module => $modulePermissions)
                            <div class="permission-module mb-4">
                                <h6 class="module-title">{{ $module }}</h6>
                                <div class="row">
                                    @foreach ($modulePermissions as $permission)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="permission-item">
                                                <i class="bi bi-check-circle text-success me-2"></i>
                                                <strong>{{ $permission->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $permission->description }}</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No permissions assigned to this role.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
