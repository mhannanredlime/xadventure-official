@csrf

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="vehicle_type_id" class="form-label">Vehicle Type <span class="text-danger">*</span></label>
            <select class="form-select @error('vehicle_type_id') is-invalid @enderror" id="vehicle_type_id"
                name="vehicle_type_id" required x-model="vehicleTypeId">
                <option value="">Select Vehicle Type</option>
                @foreach ($vehicleTypes as $type)
                    <option value="{{ $type->id }}" data-images="{{ $type->images->toJson() }}"
                        data-display-image="{{ $type->display_image_url }}">
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
                value="{{ old('name', $vehicle->name ?? '') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- Alpine JS controlled Preview Section --}}
<div class="row mb-4" x-show="selectedTypeData">
    <div class="col-12">
        <label class="form-label">Vehicle Type Images Preview</label>
        <div id="vehicle-type-images" class="vehicle-type-images-container">

            <template x-if="selectedTypeData && selectedTypeData.images && selectedTypeData.images.length > 0">
                <div class="vehicle-type-images-grid">
                    <template x-for="image in selectedTypeData.images" :key="image.id">
                        <div class="vehicle-type-image-item" :class="image.is_primary ? 'border-primary' : ''">
                            <img :src="image.url" :alt="image.alt_text || selectedTypeData.text"
                                class="img-fluid rounded" style="max-height: 200px; width: 100%; object-fit: cover;">
                            <template x-if="image.is_primary">
                                <span class="badge bg-primary position-absolute top-0 end-0 m-1">Primary</span>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <template
                x-if="selectedTypeData && (!selectedTypeData.images || selectedTypeData.images.length === 0) && selectedTypeData.displayImage">
                <div class="vehicle-type-images-grid">
                    <div class="vehicle-type-image-item border-primary">
                        <img :src="selectedTypeData.displayImage" :alt="selectedTypeData.text" class="img-fluid rounded"
                            style="max-height: 200px; width: 100%; object-fit: cover;">
                    </div>
                </div>
            </template>

            <template
                x-if="selectedTypeData && (!selectedTypeData.images || selectedTypeData.images.length === 0) && !selectedTypeData.displayImage">
                <div class="text-center text-muted py-4">
                    <i class="bi bi-image fa-3x mb-3"></i>
                    <p x-text="`No images available for ${selectedTypeData.text}`"></p>
                </div>
            </template>
            <template x-if="selectedTypeData && selectedTypeData.error">
                <div class="text-center text-muted py-4">
                    <i class="bi bi-exclamation-triangle fa-3x mb-3"></i>
                    <p x-text="`Error loading images for ${selectedTypeData.text}`"></p>
                </div>
            </template>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="details" class="form-label">Details</label>
            <input type="text" class="form-control @error('details') is-invalid @enderror" id="details"
                name="details" value="{{ old('details', $vehicle->details ?? '') }}">
            @error('details')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="op_start_date" class="form-label">Operation Start Date</label>
            <input type="date" class="form-control @error('op_start_date') is-invalid @enderror" id="op_start_date"
                name="op_start_date" value="{{ old('op_start_date', $vehicle->op_start_date ?? '') }}">
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

<div class="row mt-4">
    <div class="form-group">
        <div class="form-check form-switch ps-0">
            <label class="form-check-label ms-5" for="is_active">
                Active Status
            </label>
            <input class="form-check-input ms-0" type="checkbox" id="is_active" name="is_active" value="1"
                {{ old('is_active', $vehicle->is_active ?? true) ? 'checked' : '' }}>

        </div>
    </div>
</div>
<div class="row">

    <div class="form-group">
        <button type="submit" class="btn btn-save">Save Vehicle</button>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</div>
