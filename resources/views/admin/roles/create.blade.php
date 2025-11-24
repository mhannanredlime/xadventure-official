@extends('layouts.admin')

@section('title', 'Create Role')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Create New Role</h2>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Roles
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.roles.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <input type="text" class="form-control @error('description') is-invalid @enderror" 
                                           id="description" name="description" value="{{ old('description') }}">
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Permissions <span class="text-danger">*</span></label>
                            <div class="permissions-container">
                                @foreach($permissions as $module => $modulePermissions)
                                <div class="permission-module mb-4">
                                    <h6 class="module-title">{{ $module }}</h6>
                                    <div class="row">
                                        @foreach($modulePermissions as $permission)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="permissions[]" value="{{ $permission->id }}" 
                                                       id="permission_{{ $permission->id }}"
                                                       {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
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
                            @error('permissions')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Create Role
                            </button>
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
    padding: 1rem;
    background-color: #f8f9fa;
}

.module-title {
    color: #495057;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #007bff;
}

.form-check {
    margin-bottom: 0.5rem;
}

.form-check-label {
    cursor: pointer;
}
</style>
@endsection
