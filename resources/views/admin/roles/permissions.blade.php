@extends('layouts.admin')

@section('title', 'Manage Role Permissions')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Manage Permissions: {{ $role->name }}</h2>
                <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Role
                </a>
            </div>

            @if($role->is_system)
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
                            @foreach($permissions as $module => $modulePermissions)
                            <div class="permission-module mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="module-title mb-0">{{ $module }}</h6>
                                    <div class="form-check">
                                        <input class="form-check-input module-select-all" type="checkbox" 
                                               data-module="{{ $module }}">
                                        <label class="form-check-label">
                                            <small>Select All</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    @foreach($modulePermissions as $permission)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" type="checkbox" 
                                                   name="permissions[]" value="{{ $permission->id }}" 
                                                   id="permission_{{ $permission->id }}"
                                                   data-module="{{ $module }}"
                                                   {{ in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="permission_{{ $permission->id }}">
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
                                <button type="button" class="btn btn-outline-primary" id="select-all-permissions">
                                    <i class="bi bi-check-square me-2"></i>Select All
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="deselect-all-permissions">
                                    <i class="bi bi-square me-2"></i>Deselect All
                                </button>
                            </div>
                            <div>
                                <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Update Permissions
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.permission-module {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1.5rem;
    background-color: #f8f9fa;
}

.module-title {
    color: #495057;
    font-weight: 600;
    margin-bottom: 0;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #007bff;
}

.form-check {
    margin-bottom: 0.75rem;
}

.form-check-label {
    cursor: pointer;
}

.permission-checkbox:checked + .form-check-label {
    color: #198754;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Module select all functionality
    const moduleSelectAllCheckboxes = document.querySelectorAll('.module-select-all');
    moduleSelectAllCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const module = this.dataset.module;
            const moduleCheckboxes = document.querySelectorAll(`input[data-module="${module}"]`);
            moduleCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });
    });

    // Global select all functionality
    document.getElementById('select-all-permissions').addEventListener('click', function() {
        const allCheckboxes = document.querySelectorAll('.permission-checkbox');
        allCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        // Also check module select all checkboxes
        moduleSelectAllCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
    });

    // Global deselect all functionality
    document.getElementById('deselect-all-permissions').addEventListener('click', function() {
        const allCheckboxes = document.querySelectorAll('.permission-checkbox');
        allCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        // Also uncheck module select all checkboxes
        moduleSelectAllCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    });

    // Update module select all checkboxes when individual permissions change
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    permissionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const module = this.dataset.module;
            const moduleCheckboxes = document.querySelectorAll(`input[data-module="${module}"]`);
            const checkedCount = Array.from(moduleCheckboxes).filter(cb => cb.checked).length;
            const moduleSelectAll = document.querySelector(`input[data-module="${module}"].module-select-all`);
            
            if (checkedCount === 0) {
                moduleSelectAll.checked = false;
                moduleSelectAll.indeterminate = false;
            } else if (checkedCount === moduleCheckboxes.length) {
                moduleSelectAll.checked = true;
                moduleSelectAll.indeterminate = false;
            } else {
                moduleSelectAll.checked = false;
                moduleSelectAll.indeterminate = true;
            }
        });
    });

    // Initialize module select all checkboxes state
    moduleSelectAllCheckboxes.forEach(checkbox => {
        const module = checkbox.dataset.module;
        const moduleCheckboxes = document.querySelectorAll(`input[data-module="${module}"]`);
        const checkedCount = Array.from(moduleCheckboxes).filter(cb => cb.checked).length;
        
        if (checkedCount === 0) {
            checkbox.checked = false;
            checkbox.indeterminate = false;
        } else if (checkedCount === moduleCheckboxes.length) {
            checkbox.checked = true;
            checkbox.indeterminate = false;
        } else {
            checkbox.checked = false;
            checkbox.indeterminate = true;
        }
    });
});
</script>
@endsection
