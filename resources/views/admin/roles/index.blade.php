@extends('layouts.admin')

@section('title', 'Role Management')

@section('content')
    <div class="container-fluid">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1>Role Management</h1>
            <div class="header-actions">
                @can('roles.create')
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-add-new">
                        <i class="bi bi-plus-circle me-2"></i>Add New Role
                    </a>
                @endcan
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table responsive-stacked">
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
                                        <td data-label="Name">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3 text-orange">
                                                    <i class="bi bi-shield-check fs-4"></i>
                                                </div>
                                                <div>
                                                    <strong class="d-block">{{ $role->name }}</strong>
                                                    <small class="text-muted">{{ $role->slug }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Description">
                                            <span class="text-muted">{{ Str::limit($role->description, 50) }}</span>
                                        </td>
                                        <td data-label="Permissions">
                                            <span class="badge badge-info">{{ $role->permissions_count }} permissions</span>
                                        </td>
                                        <td data-label="Users">
                                            <span class="badge badge-secondary">{{ $role->users_count }} users</span>
                                        </td>
                                        <td data-label="Type">
                                            @if ($role->is_system)
                                                <span class="status-badge badge-warning">System</span>
                                            @else
                                                <span class="status-badge badge-success">Custom</span>
                                            @endif
                                        </td>
                                        <td data-label="Actions">
                                            <div class="action-icons">
                                                @if (auth()->user()->hasPermission('roles.view') || auth()->user()->hasRole('master-admin'))
                                                    <a href="{{ route('admin.roles.show', $role) }}"
                                                        class="btn-icon text-info" title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endif

                                                @if (auth()->user()->hasPermission('roles.edit') || auth()->user()->hasRole('master-admin'))
                                                    <a href="{{ route('admin.roles.edit', $role) }}"
                                                        class="btn-icon text-primary" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <a href="{{ route('admin.roles.permissions', $role) }}"
                                                        class="btn-icon text-warning" title="Manage Permissions">
                                                        <i class="bi bi-gear"></i>
                                                    </a>
                                                @endif

                                                @if (auth()->user()->hasPermission('roles.delete') || auth()->user()->hasRole('master-admin'))
                                                    @if (!$role->is_system)
                                                        <form action="{{ route('admin.roles.destroy', $role) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-icon text-danger"
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
                                        <td colspan="6" class="text-center py-4">
                                            <i class="bi bi-shield-x fs-1 d-block mb-3 text-muted"></i>
                                            <h5 class="text-muted">No roles found</h5>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($roles->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $roles->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
