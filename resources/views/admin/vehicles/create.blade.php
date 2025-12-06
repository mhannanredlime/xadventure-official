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

            <form action="{{ route('admin.vehicles.store') }}" method="POST" enctype="multipart/form-data"
                onsubmit="updateFormData(this)">

                @include('admin.vehicles._form')
            </form>

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

        .vehicle-type-images-container {
            min-height: 250px;
            border: 2px dashed #d3d7dd;
            border-radius: 10px;
            background: #f9fbfd;
            padding: 25px;
        }

        .vehicle-type-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .vehicle-type-image-item {
            border: 2px solid #e4e6e9;
            border-radius: 10px;
            padding: 8px;
            background: white;
            position: relative;
            transition: 0.25s;
        }

        .vehicle-type-image-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .vehicle-type-image-item.border-primary {
            border-color: #0d6efd !important;
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
