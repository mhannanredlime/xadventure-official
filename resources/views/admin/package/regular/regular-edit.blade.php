@extends('layouts.admin')
@section('title', 'Edit Regular Package')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/gallery.css') }}">

    <style>
        .card {
            border: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-weight: 700;
            margin-bottom: 1rem;
            color: #2c3e50;
            font-size: 1.25rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
        }

        .btn-save {
            background: linear-gradient(135deg, #F76B19, #e55e14);
            color: #fff;
            font-weight: 600;
            border-radius: 0.75rem;
            padding: 1rem 2.5rem;
            border: none;
            box-shadow: 0 6px 20px rgba(247, 107, 25, 0.3);
            transition: .3s;
            position: relative;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(247, 107, 25, 0.4);
        }

        .btn-save:disabled {
            opacity: 0.7;
            transform: none;
            box-shadow: none;
        }

        .btn-save.btn-loading {
            pointer-events: none;
            color: transparent !important;
        }

        .btn-save.btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid transparent;
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .table-warning {
            background-color: #fff3cd !important;
        }

        .weekend-badge {
            font-size: 0.7em;
            vertical-align: middle;
        }

        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .existing-image {
            position: relative;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            width: 100px;
            height: 100px;
        }

        .existing-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-number {
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
    </style>
@endpush

@section('content')
    <main class="mt-4">
        <header class="d-flex justify-content-between align-items-center page-header mb-4">
            <div>
                <h3>Edit Regular Package</h3>
                <p class="breadcrumb-custom">
                    <i class="bi bi-home me-1"></i>
                    Package Management > Edit Package
                </p>
            </div>
            <a href="{{ url('admin/packege/list') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Packages
            </a>
        </header>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Please correct the following errors:</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form id="packageForm" method="POST" action="{{ route('admin.regular-packege-management.update', $package) }}"
            enctype="multipart/form-data">
            @include('admin.package.regular_form')
        </form>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/multiple-image-upload.js') }}"></script>
    <script src="{{ asset('admin/js/gallery-manager.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // -------------------------------------------------
            // Base Variables
            // -------------------------------------------------
            const form = document.getElementById('packageForm');
            const submitBtn = document.getElementById('submitBtn');
            const dayPricesInput = document.getElementById('dayPricesInput');
            const applyAllInput = document.getElementById('applyAllPrice');
            const imageFilesInput = document.getElementById('package_images_input');

            // All days array
            const allDays = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

            // Initialize prices from existing data
            let prices = @json($dayPrices ?? []);

            console.log('Initial prices from database:', prices);

            // -------------------------------------------------
            // Initialize Custom Image Uploader
            // -------------------------------------------------
            const container = document.getElementById('multiple-image-upload');
            let uploaderInstance = null;

            if (container) {
                try {
                    // Parse existing images from data attribute
                    const existingImages = JSON.parse(container.dataset.existingImages || '[]');
                    console.log('Existing images for uploader:', existingImages);

                    // Initialize uploader
                    if (typeof MultipleImageUpload !== 'undefined') {
                        uploaderInstance = new MultipleImageUpload(container.id, {
                            maxFiles: parseInt(container.dataset.maxFiles) || 4,
                            maxFileSize: parseInt(container.dataset.maxFileSize) || 5 * 1024 * 1024,
                            modelType: container.dataset.modelType || 'Package',
                            modelId: container.dataset.modelId || '',
                            uploadUrl: container.dataset.uploadUrl || '',
                            updateUrl: container.dataset.updateUrl || '',
                            imagesUrl: container.dataset.imagesUrl || '',
                            primaryUrl: container.dataset.primaryUrl || '',
                            reorderUrl: container.dataset.reorderUrl || '',
                            altTextUrl: container.dataset.altTextUrl || '',
                            deleteUrl: container.dataset.deleteUrl || '',
                            existingImages: existingImages
                        });

                        window.multipleImageUploadInstance = uploaderInstance;
                        console.log('Image uploader initialized');
                    }
                } catch (error) {
                    console.error('Error initializing image uploader:', error);
                }
            }

            // -------------------------------------------------
            // Price Input Handling
            // -------------------------------------------------
            // Individual price input changes
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('day-price-input')) {
                    const day = e.target.dataset.day;
                    const val = e.target.value;

                    if (val && val > 0) {
                        prices[day] = parseFloat(val);
                    } else {
                        // Keep the existing price if user clears the field
                        // Or set to null if you want to clear it
                        prices[day] = null;
                    }

                    updateDayPricesInput();
                }
            });

            // Apply All functionality
            applyAllInput.addEventListener('input', function() {
                const val = this.value;
                if (val && val > 0) {
                    document.querySelectorAll('.day-price-input').forEach(input => {
                        input.value = val;
                        const day = input.dataset.day;
                        prices[day] = parseFloat(val);
                    });
                    updateDayPricesInput();
                }
            });

            // Function to update hidden input
            function updateDayPricesInput() {
                const dayPricesArray = allDays.map(day => ({
                    day: day,
                    price: prices[day] !== undefined ? prices[day] : null
                }));

                dayPricesInput.value = JSON.stringify(dayPricesArray);
                console.log('Updated day prices:', dayPricesArray);
            }

            // -------------------------------------------------
            // Form Validation
            // -------------------------------------------------
            function validateForm() {
                let isValid = true;

                // Check required fields
                const requiredFields = [{
                        selector: 'input[name="packageName"]',
                        message: 'Package name is required'
                    },
                    {
                        selector: 'select[name="packageType"]',
                        message: 'Package type is required'
                    },
                    {
                        selector: 'input[name="displayStartingPrice"]',
                        message: 'Display price is required'
                    },
                    {
                        selector: 'input[name="minParticipant"]',
                        message: 'Minimum participants is required'
                    },
                    {
                        selector: 'input[name="maxParticipant"]',
                        message: 'Maximum participants is required'
                    }
                ];

                requiredFields.forEach(field => {
                    const element = document.querySelector(field.selector);
                    if (!element || !element.value.trim()) {
                        alert(field.message);
                        if (element) element.focus();
                        isValid = false;
                        return false;
                    }

                    // Additional validation for numbers
                    if (field.selector.includes('Participant') || field.selector.includes(
                            'StartingPrice')) {
                        const value = parseFloat(element.value);
                        if (isNaN(value) || value <= 0) {
                            alert(`${field.message} (must be a positive number)`);
                            element.focus();
                            isValid = false;
                            return false;
                        }
                    }
                });

                if (!isValid) return false;

                // Check min <= max participants
                const minParticipant = parseInt(document.querySelector('input[name="minParticipant"]').value);
                const maxParticipant = parseInt(document.querySelector('input[name="maxParticipant"]').value);

                if (minParticipant > maxParticipant) {
                    alert('Minimum participants cannot be greater than maximum participants');
                    return false;
                }

                return true;
            }

            // -------------------------------------------------
            // Form Submission Handler
            // -------------------------------------------------
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                console.log('=== EDIT FORM SUBMISSION STARTED ===');

                // Validate form
                if (!validateForm()) {
                    return;
                }

                // Update day prices
                updateDayPricesInput();

                // Handle image files from uploader
                if (uploaderInstance) {
                    try {
                        // Get new files from uploader
                        let newFiles = [];
                        if (typeof uploaderInstance.getFiles === 'function') {
                            newFiles = uploaderInstance.getFiles();
                        } else if (uploaderInstance.files) {
                            newFiles = uploaderInstance.files;
                        }

                        newFiles = newFiles.filter(file => file instanceof File);
                        console.log('New files to upload:', newFiles.length);

                        if (newFiles.length > 0) {
                            const dataTransfer = new DataTransfer();
                            newFiles.forEach(file => {
                                dataTransfer.items.add(file);
                            });

                            imageFilesInput.files = dataTransfer.files;
                            console.log(`Attached ${imageFilesInput.files.length} new image files`);
                        }
                    } catch (error) {
                        console.error('Error getting files from uploader:', error);
                    }
                }

                // Debug logging
                console.log('Final form data check:');
                console.log('Package ID:', '{{ $package->id }}');
                console.log('Day prices:', JSON.parse(dayPricesInput.value));
                console.log('Image files:', imageFilesInput.files.length);

                // Show loading state
                submitBtn.disabled = true;
                submitBtn.classList.add('btn-loading');

                // Submit form after short delay
                setTimeout(() => {
                    console.log('Submitting edit form...');
                    form.submit();
                }, 500);
            });

            // Initialize
            updateDayPricesInput();

            // Log initial state
            console.log('Edit form initialized for package:', '{{ $package->id }}');
            console.log('Initial prices:', prices);
        });
    </script>
@endpush
