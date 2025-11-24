@extends('layouts.admin')

@section('title', 'Role Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Role Management</h2>
                @can('roles.create')
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add New Role
                </a>
                @endcan
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Permissions</th>
                                    <th>Users</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="role-icon me-3">
                                                <i class="bi bi-shield-check"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $role->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $role->slug }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ Str::limit($role->description, 50) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $role->permissions->count() }} permissions</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $role->users->count() }} users</span>
                                    </td>
                                    <td>
                                        @if($role->is_system)
                                            <span class="badge bg-warning">System</span>
                                        @else
                                            <span class="badge bg-success">Custom</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if(auth()->user()->hasPermission('roles.view') || auth()->user()->hasRole('master-admin'))
                                            <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @endif
                                            @if(auth()->user()->hasPermission('roles.edit') || auth()->user()->hasRole('master-admin'))
                                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @endif
                                            @if(auth()->user()->hasPermission('roles.edit') || auth()->user()->hasRole('master-admin'))
                                            <a href="{{ route('admin.roles.permissions', $role) }}" class="btn btn-sm btn-outline-warning" title="Manage Permissions">
                                                <i class="bi bi-gear"></i>
                                            </a>
                                            @endif
                                            @if(auth()->user()->hasPermission('roles.delete') || auth()->user()->hasRole('master-admin'))
                                            @if(!$role->is_system)
                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
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
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-shield-x fs-1 d-block mb-2"></i>
                                        No roles found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($roles->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $roles->links('pagination::bootstrap-4') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.role-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
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
