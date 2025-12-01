@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Vehicle Management</h1>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Vehicle Management</li>
                <li class="breadcrumb-item active">Add New Vehicle</li>
            </ol>
        </nav>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('admin.vehicles.store') }}" method="POST" enctype="multipart/form-data"
                    onsubmit="updateFormData(this)">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vehicle_type_id" class="form-label">Vehicle Type <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('vehicle_type_id') is-invalid @enderror"
                                    id="vehicle_type_id" name="vehicle_type_id" required>
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
                                <label for="name" class="form-label">Vehicle Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required>
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
                                <input type="text" class="form-control @error('details') is-invalid @enderror"
                                    id="details" name="details" value="{{ old('details') }}">
                                @error('details')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="op_start_date" class="form-label">Operation Start Date</label>
                                <input type="date" class="form-control @error('op_start_date') is-invalid @enderror"
                                    id="op_start_date" name="op_start_date" value="{{ old('op_start_date') }}">
                                @error('op_start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ---------- Image Upload Section ---------- --}}
                    <h5 class="card-title"><i class="bi bi-images me-2"></i>Package Images</h5>
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label">Upload Package Images (Max 2 images)</label>
                            <div id="multiple-image-upload" data-model-type="App\Models\Vehicle"
                                data-model-id="{{ $vehicle->id ?? '' }}"
                                data-upload-url="{{ route('admin.vehicles.store') }}"
                                data-update-url="{{ isset($vehicle) ? route('admin.vehicles.update', $vehicle) : '' }}"
                                data-images-url="{{ route('admin.images.get') }}"
                                data-primary-url="{{ url('admin/images') }}/:id/primary"
                                data-reorder-url="{{ route('admin.images.reorder') }}"
                                data-alt-text-url="{{ url('admin/images') }}/:id/alt-text"
                                data-delete-url="{{ url('admin/images') }}/:id"
                                data-existing-images="{{ isset($vehicle) ? $vehicle->images->toJson() : '[]' }}"
                                data-max-files="2" data-max-file-size="{{ 5 * 1024 * 1024 }}">
                            </div>
                            <input type="file" id="vehicle_images_input" name="images[]" multiple accept="image/*"
                                style="display:none;">
                        </div>
                    </div>


                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active Status
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Save Vehicle</button>
                        <a href="{{ route('admin.vehical-management') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
    <style>
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: ">";
            color: #6c757d;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #495057;
        }

        .vehicle-type-images-container {
            min-height: 300px;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .vehicle-type-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .vehicle-type-image-item {
            position: relative;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background-color: white;
            transition: all 0.3s ease;
        }

        .vehicle-type-image-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .vehicle-type-image-item.border-primary {
            border-color: #0d6efd;
        }

        .vehicle-type-image-item img {
            border-radius: 4px;
        }

        .multiple-image-upload-area {
            margin-top: 0.5rem;
        }

        .multiple-image-upload-area h6 {
            font-size: 0.875rem;
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.75rem;
        }

        .image-preview-box,
        .additional-image-item>div,
        .add-more-box {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .image-preview-box:hover,
        .additional-image-item>div:hover,
        .add-more-box:hover {
            border-color: #007bff;
            background-color: #e3f2fd;
        }

        .form-check-input:checked {
            background-color: #ff6b35;
            border-color: #ff6b35;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-save {
            background: linear-gradient(135deg, #F76B19 0%, #e55e14 100%);
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 0.75rem;
            padding: 1rem 2.5rem;
            border: none;
            box-shadow: 0 6px 20px rgba(247, 107, 25, 0.3);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('admin/js/multiple-image-upload.js') }}"></script>
    <script>
        // Vehicle Type Image Display Functions
        function initializeVehicleTypeImages() {
            const vehicleTypeSelect = document.getElementById('vehicle_type_id');
            if (vehicleTypeSelect.value) {
                updateVehicleTypeImages();
            }
        }

        function updateVehicleTypeImages() {
            const vehicleTypeSelect = document.getElementById('vehicle_type_id');
            const imageContainer = document.getElementById('vehicle-type-images');
            const selectedOption = vehicleTypeSelect.options[vehicleTypeSelect.selectedIndex];

            if (!selectedOption || !selectedOption.value) {
                imageContainer.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="bi  bi-image fa-3x mb-3"></i>
                <p>Select a vehicle type to see its images</p>
            </div>
        `;
                return;
            }

            try {
                const images = JSON.parse(selectedOption.dataset.images || '[]');
                const displayImage = selectedOption.dataset.displayImage;

                if (images.length === 0 && !displayImage) {
                    imageContainer.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="bi  bi-image fa-3x mb-3"></i>
                    <p>No images available for ${selectedOption.text}</p>
                </div>
            `;
                    return;
                }

                let imageHtml = '<div class="vehicle-type-images-grid">';

                if (images.length > 0) {
                    images.forEach((image, index) => {
                        const isPrimary = image.is_primary ? 'border-primary' : 'border-secondary';
                        imageHtml += `
                    <div class="vehicle-type-image-item ${isPrimary}">
                        <img src="${image.url}" alt="${image.alt_text || selectedOption.text}"
                             class="img-fluid rounded" style="max-height: 200px; width: 100%; object-fit: cover;">
                        ${image.is_primary ? '<span class="badge bg-primary position-absolute top-0 end-0 m-1">Primary</span>' : ''}
                    </div>
                `;
                    });
                } else if (displayImage) {
                    imageHtml += `
                <div class="vehicle-type-image-item border-primary">
                    <img src="${displayImage}" alt="${selectedOption.text}"
                         class="img-fluid rounded" style="max-height: 200px; width: 100%; object-fit: cover;">
                </div>
            `;
                }

                imageHtml += '</div>';
                imageContainer.innerHTML = imageHtml;

            } catch (error) {
                // Error parsing vehicle type images
                imageContainer.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="bi  bi-exclamation-triangle fa-3x mb-3"></i>
                <p>Error loading images for ${selectedOption.text}</p>
            </div>
        `;
            }
        }

        // Initialize vehicle type images on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeVehicleTypeImages();

            // Handle vehicle type selection change
            document.getElementById('vehicle_type_id').addEventListener('change', function() {
                updateVehicleTypeImages();
            });
        });

        // Update form data before submission
        function updateFormData(form) {
            // Form submission triggered

            if (window.multipleImageUploadInstance) {
                const selectedFiles = window.multipleImageUploadInstance.getSelectedFiles();
                // Selected files logged
                const formImageInput = form.querySelector('input[name="images[]"]');
                // Form image input logged

                if (selectedFiles.length > 0) {
                    // Create a new FileList-like object
                    const dt = new DataTransfer();
                    selectedFiles.forEach(file => dt.items.add(file));
                    formImageInput.files = dt.files;
                    // Files assigned to form input
                }
            }

            // Allow form to submit
            return true;
        }

        // Multiple image upload initialization is handled in the external JS file
    </script>
@endpush
