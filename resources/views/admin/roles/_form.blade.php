@csrf
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                value="{{ old('name', $role->name ?? '') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <input type="text" class="form-control @error('description') is-invalid @enderror" id="description"
                name="description" value="{{ old('description', $role->description ?? '') }}">
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="mb-4">
    <label class="form-label">Permissions <span class="text-danger">*</span></label>
    <div class="permissions-container">
        @foreach ($permissions as $module => $modulePermissions)
            <div class="permission-module mb-4">
                <h6 class="module-title">{{ $module }}</h6>
                <div class="row">
                    @foreach ($modulePermissions as $permission)
                        <div class="col-md-6 col-lg-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                    value="{{ $permission->id }}" id="permission_{{ $permission->id }}"
                                    {{ in_array($permission->id, old('permissions', isset($role) ? $role->permissions->pluck('id')->toArray() : [])) ? 'checked' : '' }}>
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
    <x-admin.button-link href="{{ route('admin.roles.index') }}" class="btn btn-secondary" text="Cancel"
        icon="bi-x-lg" />
    <x-admin.button type="submit" color="save" icon="bi bi-check-circle">
        {{ isset($role) ? 'Update Role' : 'Create Role' }}
    </x-admin.button>
</div>
