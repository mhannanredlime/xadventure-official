@csrf

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="vehicle_type_id" class="form-label">Vehicle Type <span class="text-danger">*</span></label>
            <select class="form-select @error('vehicle_type_id') is-invalid @enderror" id="vehicle_type_id"
                name="vehicle_type_id" required>
                <option value="">Select Vehicle Type</option>
                @foreach ($vehicleTypes as $type)
                    <option value="{{ $type->id }}" data-images="{{ $type->images->toJson() }}"
                        data-display-image="{{ $type->display_image_url }}"
                        {{ old('vehicle_type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
            @error('vehicle_type_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="name" class="form-label">Vehicle Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="details" class="form-label">Details</label>
            <input type="text" class="form-control @error('details') is-invalid @enderror" id="details"
                name="details" value="{{ old('details') }}">
            @error('details')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="op_start_date" class="form-label">Operation Start Date</label>
            <input type="date" class="form-control @error('op_start_date') is-invalid @enderror" id="op_start_date"
                name="op_start_date" value="{{ old('op_start_date') }}">
            @error('op_start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- ---------- Image Upload Section ---------- --}}
<div class="row">
    <h5 class="card-title"><i class="bi bi-images me-2"></i>Vehicle Images</h5>
    <div class="col-12">
        <label class="form-label">Upload Vehicle Images (Max 2 images)</label>
        <x-images-uploader modelType="Vehicle" :modelId="$vehicle->id ?? null" uploadUrl="{{ route('admin.vehicles.store') }}"
            :updateUrl="isset($vehicle) ? route('admin.vehicles.update', $vehicle) : null"
            imagesUrl="{{ route('admin.images.get', ['model_type' => 'Vehicle', 'model_id' => $vehicle->id ?? '']) }}"
            primaryUrl="{{ url('admin/images') }}/:id/primary" reorderUrl="{{ route('admin.images.reorder') }}"
            altTextUrl="{{ url('admin/images') }}/:id/alt-text" deleteUrl="{{ url('admin/images') }}/:id"
            :existingImages="isset($vehicle) ? $vehicle->images->toJson() : '[]'" maxFiles="2" :maxFileSize="5 * 1024 * 1024" />

        <input type="file" id="vehicle_images_input" name="images[]" multiple accept="image/*" style="display:none;">
    </div>
</div>

<div class="row">
    <div class="form-group">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                {{ old('is_active', true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">
                Active Status
            </label>
        </div>
    </div>
</div>
<div class="row">

<div class="form-group">
    <button type="submit" class="btn btn-primary">Save Vehicle</button>
    <a href="{{ route('admin.vehical-management') }}" class="btn btn-secondary">Cancel</a>
</div>
</div>
