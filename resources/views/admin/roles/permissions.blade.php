@extends('layouts.admin')

@section('title', 'Manage Role Permissions')

@section('content')
    <div class="container-fluid" x-data="permissionManager(@js($role->permissions->pluck('id')))">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Manage Permissions: {{ $role->name }}</h2>
                    <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Role
                    </a>
                </div>

                @if ($role->is_system)
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This is a system role. Be careful when modifying permissions.
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.roles.update-permissions', $role) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="permissions-container">
                                @foreach ($permissions as $module => $modulePermissions)
                                    @php
                                        $modulePermissionIds = $modulePermissions->pluck('id')->toArray();
                                    @endphp
                                    <div class="permission-module mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="module-title mb-0">{{ $module }}</h6>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    @change="toggleModule(@js($modulePermissionIds))"
                                                    :checked="isModuleSelected(@js($modulePermissionIds))"
                                                    :indeterminate="isModuleIndeterminate(@js($modulePermissionIds))">
                                                <label class="form-check-label">
                                                    <small>Select All</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            @foreach ($modulePermissions as $permission)
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]"
                                                            value="{{ $permission->id }}"
                                                            id="permission_{{ $permission->id }}" x-model="selected">
                                                        <label class="form-check-label"
                                                            for="permission_{{ $permission->id }}">
                                                            <strong>{{ $permission->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $permission->description }}</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <div>
                                    <button type="button" class="btn btn-outline-primary"
                                        @click="selectAll(@js($permissions->flatten()->pluck('id')))">
                                        <i class="bi bi-check-square me-2"></i>Select All
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" @click="deselectAll()">
                                        <i class="bi bi-square me-2"></i>Deselect All
                                    </button>
                                </div>
                                <div>
                                    <a href="{{ route('admin.roles.show', $role) }}"
                                        class="btn btn-secondary me-2">Cancel</a>
                                    <x-admin.button type="submit" color="save" icon="bi bi-check-circle">
                                        Update Permissions
                                    </x-admin.button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('permissionManager', (initialSelected) => ({
                selected: initialSelected.map(String),

                toggleModule(moduleIds) {
                    const allSelected = this.isModuleSelected(moduleIds);
                    if (allSelected) {
                        // Deselect all in module
                        this.selected = this.selected.filter(id => !moduleIds.includes(parseInt(id)) &&
                            !moduleIds.includes(String(id)));
                    } else {
                        // Select all in module
                        const newIds = moduleIds.map(String).filter(id => !this.selected.includes(id));
                        this.selected = [...this.selected, ...newIds];
                    }
                },

                isModuleSelected(moduleIds) {
                    return moduleIds.every(id => this.selected.includes(String(id)));
                },

                isModuleIndeterminate(moduleIds) {
                    const selectedCount = moduleIds.filter(id => this.selected.includes(String(id)))
                        .length;
                    return selectedCount > 0 && selectedCount < moduleIds.length;
                },

                selectAll(allIds) {
                    this.selected = allIds.map(String);
                },

                deselectAll() {
                    this.selected = [];
                }
            }));
        });
    </script>
@endsection
