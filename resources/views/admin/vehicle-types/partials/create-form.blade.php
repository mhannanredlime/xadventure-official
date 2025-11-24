<form id="createVehicleTypeForm" enctype="multipart/form-data">
    @csrf
    <div class="modal-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="name" class="form-label">Vehicle Type Name *</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                    <div class="invalid-feedback" id="name_error"></div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="subtitle" class="form-label">Subtitle</label>
                    <input type="text" class="form-control" id="subtitle" name="subtitle" placeholder="e.g., 2 Seater ATV">
                    <div class="invalid-feedback" id="subtitle_error"></div>
                </div>
            </div>
        </div>
        
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="seating_capacity" class="form-label">Seating Capacity</label>
                    <input type="number" class="form-control" id="seating_capacity" name="seating_capacity" min="1" max="10" value="2">
                    <div class="invalid-feedback" id="seating_capacity_error"></div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="license_requirement" class="form-label">License Requirement</label>
                    <input type="text" class="form-control" id="license_requirement" name="license_requirement" placeholder="e.g., * Motorcycle license required">
                    <div class="invalid-feedback" id="license_requirement_error"></div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label for="multiple-image-upload" class="form-label">Upload Image</label>
                    <div id="multiple-image-upload" 
                         data-model-type="App\Models\VehicleType" 
                         data-model-id=""
                         data-upload-url="{{ route('admin.vehicle-types.store') }}"
                         data-update-url=""
                         data-images-url="{{ route('admin.images.get') }}"
                         data-primary-url="{{ url('admin/images') }}/:id/primary"
                         data-reorder-url="{{ route('admin.images.reorder') }}"
                         data-alt-text-url="{{ url('admin/images') }}/:id/alt-text"
                         data-delete-url="{{ url('admin/images') }}/:id"
                         data-existing-images="[]"
                         data-debug="Creating new vehicle type">
                        <!-- Multiple image upload component will be initialized here -->
                    </div>
                    <!-- Hidden input for form submission -->
                    <input type="file" name="images[]" multiple style="display: none;" accept="image/*">
                    <div class="invalid-feedback" id="images_error"></div>
                </div>
            </div>
        

        
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                <label class="form-check-label" for="is_active">
                    Active
                </label>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Create Vehicle Type</button>
    </div>
</form>
