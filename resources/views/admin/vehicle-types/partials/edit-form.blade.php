<form id="editVehicleTypeForm" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="edit_name" class="form-label">Vehicle Type Name *</label>
                    <input type="text" class="form-control" id="edit_name" name="name" value="{{ $vehicleType->name }}" required>
                    <div class="invalid-feedback" id="edit_name_error"></div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="edit_subtitle" class="form-label">Subtitle</label>
                    <input type="text" class="form-control" id="edit_subtitle" name="subtitle" value="{{ $vehicleType->subtitle }}" placeholder="e.g., 2 Seater ATV">
                    <div class="invalid-feedback" id="edit_subtitle_error"></div>
                </div>
            </div>
        </div>
        
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="edit_seating_capacity" class="form-label">Seating Capacity</label>
                    <input type="number" class="form-control" id="edit_seating_capacity" name="seating_capacity" min="1" max="10" value="{{ $vehicleType->seating_capacity }}">
                    <div class="invalid-feedback" id="edit_seating_capacity_error"></div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="edit_license_requirement" class="form-label">License Requirement</label>
                    <input type="text" class="form-control" id="edit_license_requirement" name="license_requirement" value="{{ $vehicleType->license_requirement }}" placeholder="e.g., * Motorcycle license required">
                    <div class="invalid-feedback" id="edit_license_requirement_error"></div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="multiple-image-upload" class="form-label">Upload Image</label>
                    <div id="multiple-image-upload" 
                         data-model-type="App\Models\VehicleType" 
                         data-model-id="{{ $vehicleType->id }}"
                         data-upload-url="{{ route('admin.vehicle-types.store') }}"
                         data-update-url="{{ route('admin.vehicle-types.update', $vehicleType) }}"
                         data-images-url="{{ route('admin.images.get') }}"
                         data-primary-url="{{ url('admin/images') }}/:id/primary"
                         data-reorder-url="{{ route('admin.images.reorder') }}"
                         data-alt-text-url="{{ url('admin/images') }}/:id/alt-text"
                         data-delete-url="{{ url('admin/images') }}/:id"
                         data-existing-images="{{ $vehicleType->images->toJson() }}"
                         data-debug="VehicleType ID: {{ $vehicleType->id }}, Images: {{ $vehicleType->images->count() }}">
                        <!-- Multiple image upload component will be initialized here -->
                    </div>
                    <!-- Hidden input for form submission -->
                    <input type="file" name="images[]" multiple style="display: none;" accept="image/*">
                    <div class="invalid-feedback" id="edit_images_error"></div>
                </div>
            </div>
        </div>
        

        
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1" {{ $vehicleType->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="edit_is_active">
                    Active
                </label>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Vehicle Type</button>
    </div>
</form>
