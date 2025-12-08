@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Vehicle Type Setup</h1>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Vehicle Type Setup</li>
                <li class="breadcrumb-item active">Add New Type</li>
            </ol>
        </nav>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('admin.vehicle-types.store') }}" method="POST" enctype="multipart/form-data"
                    onsubmit="updateFormData(this)">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Vehicle Type <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subtitle">Sub Title</label>
                                <input type="text" class="form-control @error('subtitle') is-invalid @enderror"
                                    id="subtitle" name="subtitle" value="{{ old('subtitle') }}"
                                    placeholder="e.g., 2 Seater ATV">
                                @error('subtitle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="seating_capacity">Seating Capacity</label>
                                <input type="number" class="form-control @error('seating_capacity') is-invalid @enderror"
                                    id="seating_capacity" name="seating_capacity" min="1" max="10"
                                    value="{{ old('seating_capacity', 2) }}">
                                @error('seating_capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="multiple-image-upload">Upload Image</label>
                        <div class="d-flex gap-2 mb-2">
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="openGalleryModal({multiple: true, onSelect: handleGallerySelection})">
                                <i class="bi bi-images"></i> Browse Gallery
                            </button>
                        </div>
                        <div id="multiple-image-upload" data-model-type="App\Models\VehicleType" data-model-id=""
                            data-upload-url="{{ route('admin.vehicle-types.store') }}" data-update-url=""
                            data-images-url="{{ route('admin.images.get') }}"
                            data-primary-url="{{ url('admin/images') }}/:id/primary"
                            data-reorder-url="{{ route('admin.images.reorder') }}"
                            data-alt-text-url="{{ url('admin/images') }}/:id/alt-text"
                            data-delete-url="{{ url('admin/images') }}/:id" data-existing-images="[]"
                            data-debug="Creating new vehicle type">
                            <!-- Multiple image upload component will be initialized here -->
                        </div>
                        <!-- Hidden input for form submission -->
                        <input type="file" name="images[]" multiple style="display: none;" accept="image/*">
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <div class="form-group ">
                        <button type="submit" class="btn btn-primary mt-2">Save Vehicle Type</button>
                        <a href="{{ route('admin.vehicle-types.index') }}" class="btn btn-secondary mt-2">Cancel</a>
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
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('admin/js/multiple-image-upload.js') }}"></script>
    <script src="{{ asset('admin/js/gallery-manager.js') }}"></script>
    <script>
        // Update form data before submission
        function updateFormData(form) {
            // Form submission triggered

            if (window.multipleImageUploadInstance) {
                const selectedFiles = window.multipleImageUploadInstance.getSelectedFiles();
                // Selected files logged
                const formImageInput = form.querySelector('input[name="images[]"]');
                // Form image input logged

                if (selectedFiles.length > 0) {
                    // Separate regular files and gallery images
                    const regularFiles = selectedFiles.filter(file => !file.isGalleryImage);
                    const galleryImages = selectedFiles.filter(file => file.isGalleryImage);

                    // Handle regular files
                    if (regularFiles.length > 0) {
                        const dt = new DataTransfer();
                        regularFiles.forEach(file => dt.items.add(file));
                        formImageInput.files = dt.files;
                    }

                    // Handle gallery images - copy them to the appropriate folder
                    if (galleryImages.length > 0) {
                        // Add gallery image IDs to form data
                        const galleryIdsInput = document.createElement('input');
                        galleryIdsInput.type = 'hidden';
                        galleryIdsInput.name = 'gallery_image_ids';
                        galleryIdsInput.value = galleryImages.map(img => img.galleryId).join(',');
                        form.appendChild(galleryIdsInput);
                    }
                }
            }

            // Allow form to submit
            return true;
        }

        // Handle gallery image selection
        function handleGallerySelection(selectedImages) {
            if (window.multipleImageUploadInstance) {
                // Convert gallery images to file-like objects for the upload component
                selectedImages.forEach(image => {
                    // Create a mock file object for gallery images
                    const mockFile = {
                        name: image.alt || 'gallery-image.jpg',
                        size: 0,
                        type: 'image/jpeg',
                        lastModified: Date.now(),
                        isGalleryImage: true,
                        galleryId: image.id,
                        url: image.url
                    };

                    // Add to the upload component
                    window.multipleImageUploadInstance.addGalleryImage(mockFile);
                });
            }
        }

        // Multiple image upload initialization is handled in the external JS file
    </script>
@endpush

@include('admin.gallery.partials.gallery-modal')
