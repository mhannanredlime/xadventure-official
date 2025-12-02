@extends('layouts.admin')
@section('title', isset($package) ? 'Edit Regular Package' : 'Add Regular Package')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/gallery.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/regular-package.css') }}">
@endpush

@section('content')
    <main class="mt-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <h3>{{ isset($package) ? 'Edit Package' : 'Add Package' }}</h3>
            <a href="#" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Packages
            </a>
        </header>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form id="packageForm" method="POST"
            action="{{ isset($package) ? route('admin.regular-packege-management.update', $package) : route('admin.regular-packege-management.store') }}"
            enctype="multipart/form-data">
            @include('admin.package.regular.regular_form')
        </form>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/multiple-image-upload.js') }}"></script>
    <script src="{{ asset('admin/js/gallery-manager.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('packageForm');
            const submitBtn = document.getElementById('submitBtn');
            // const priceContainer = document.getElementById('priceContainer');
            const applyAllInput = document.getElementById('applyAllPrice');
            const dayPricesInput = document.getElementById('dayPricesInput');
            const fileInput = document.getElementById('package_images_input');

            // All days array
            const allDays = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

            // Initialize prices
            let prices = {};

            // Individual price input changes
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('day-price-input')) {
                    const day = e.target.dataset.day;
                    const val = e.target.value;
                    prices[day] = val ? Number(val) : null;
                }
            });

            // Apply All functionality
            applyAllInput.addEventListener('input', function() {
                const val = this.value;
                document.querySelectorAll('.day-price-input').forEach(input => {
                    input.value = val;
                    prices[input.dataset.day] = val ? Number(val) : null;
                });
            });

            // Initialize custom image uploader
            const container = document.getElementById('multiple-image-upload');
            let uploaderInstance = null;

            if (container) {
                // Check if MultipleImageUpload class exists
                if (typeof MultipleImageUpload !== 'undefined') {
                    try {
                        uploaderInstance = new MultipleImageUpload(container.id, {
                            maxFiles: parseInt(container.dataset.maxFiles) || 4,
                            maxFileSize: parseInt(container.dataset.maxFileSize) || 5 * 1024 * 1024,
                            modelType: container.dataset.modelType,
                            modelId: container.dataset.modelId,
                            uploadUrl: container.dataset.uploadUrl,
                            updateUrl: container.dataset.updateUrl,
                            imagesUrl: container.dataset.imagesUrl,
                            primaryUrl: container.dataset.primaryUrl,
                            reorderUrl: container.dataset.reorderUrl,
                            altTextUrl: container.dataset.altTextUrl,
                            deleteUrl: container.dataset.deleteUrl,
                            existingImages: JSON.parse(container.dataset.existingImages || '[]')
                        });

                        // Store instance for debugging
                        window.multipleImageUploadInstance = uploaderInstance;
                        console.log('Image uploader initialized:', uploaderInstance);

                    } catch (error) {
                        console.error('Failed to initialize image uploader:', error);
                    }
                } else {
                    console.warn('MultipleImageUpload class not found. Check script loading.');
                }
            }

            // Form submission handler
            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();

                console.log('=== FORM SUBMISSION STARTED ===');

                // 1. VALIDATE FORM FIELDS
                const packageName = document.querySelector('input[name="packageName"]').value.trim();
                const packageType = document.querySelector('select[name="packageType"]').value;
                const displayPrice = document.querySelector('input[name="displayStartingPrice"]').value;
                const minParticipant = document.querySelector('input[name="minParticipant"]').value;
                const maxParticipant = document.querySelector('input[name="maxParticipant"]').value;

                // Validation checks
                if (!packageName) {
                    alert('Please enter package name');
                    document.querySelector('input[name="packageName"]').focus();
                    return;
                }

                if (!packageType) {
                    alert('Please select package type');
                    document.querySelector('select[name="packageType"]').focus();
                    return;
                }

                if (!displayPrice || displayPrice <= 0) {
                    alert('Please enter a valid display price');
                    document.querySelector('input[name="displayStartingPrice"]').focus();
                    return;
                }

                if (!minParticipant || minParticipant <= 0) {
                    alert('Please enter minimum participants');
                    document.querySelector('input[name="minParticipant"]').focus();
                    return;
                }

                if (!maxParticipant || maxParticipant <= 0) {
                    alert('Please enter maximum participants');
                    document.querySelector('input[name="maxParticipant"]').focus();
                    return;
                }

                if (parseInt(minParticipant) > parseInt(maxParticipant)) {
                    alert('Minimum participants cannot be greater than maximum participants');
                    return;
                }

                // 2. UPDATE DAY PRICES
                const dayPricesArray = allDays.map(day => ({
                    day: day,
                    price: prices[day] !== undefined && prices[day] !== null ? prices[day] :
                        null
                }));
                dayPricesInput.value = JSON.stringify(dayPricesArray);
                console.log('Day prices updated:', dayPricesArray);

                // 3. HANDLE IMAGE FILES - CRITICAL FIX
                console.log('Handling image files...');

                // Method 1: Try to get files from uploader instance
                if (uploaderInstance && typeof uploaderInstance.getFiles === 'function') {
                    try {
                        const files = uploaderInstance.getFiles();
                        console.log('Files from uploader:', files);

                        if (files && files.length > 0) {
                            const dataTransfer = new DataTransfer();
                            files.forEach(file => {
                                if (file instanceof File) {
                                    dataTransfer.items.add(file);
                                }
                            });

                            fileInput.files = dataTransfer.files;
                            console.log(`Attached ${fileInput.files.length} files to form input`);
                        }
                    } catch (error) {
                        console.error('Error getting files from uploader:', error);
                    }
                }
                // Method 2: Try alternative method if getFiles doesn't exist
                else if (uploaderInstance && uploaderInstance.files) {
                    try {
                        const files = uploaderInstance.files;
                        console.log('Files from uploader.files:', files);

                        if (files && files.length > 0) {
                            const dataTransfer = new DataTransfer();
                            files.forEach(file => {
                                if (file instanceof File) {
                                    dataTransfer.items.add(file);
                                }
                            });

                            fileInput.files = dataTransfer.files;
                            console.log(`Attached ${fileInput.files.length} files to form input`);
                        }
                    } catch (error) {
                        console.error('Error getting files from uploader.files:', error);
                    }
                }
                // Method 3: If uploader doesn't work, check if there are any manually selected files
                else {
                    console.log('Uploader instance not available or has no files method');
                    console.log('File input current files:', fileInput.files.length);

                    // Check if there are any files already in the input
                    if (fileInput.files.length === 0) {
                        console.warn('No image files attached to form!');
                        // You might want to ask user to select images or make it optional
                        // alert('Please select at least one image for the package');
                        // return;
                    }
                }

                // 4. DEBUG: Show what's being submitted
                console.log('=== FORM DATA DEBUG ===');
                console.log('Package Name:', packageName);
                console.log('Package Type:', packageType);
                console.log('Display Price:', displayPrice);
                console.log('Min Participants:', minParticipant);
                console.log('Max Participants:', maxParticipant);
                console.log('Day Prices:', dayPricesArray);
                console.log('Image Files Count:', fileInput.files.length);

                // Show file details
                for (let i = 0; i < fileInput.files.length; i++) {
                    const file = fileInput.files[i];
                    console.log(`File ${i + 1}:`, file.name, `(${Math.round(file.size / 1024)} KB)`);
                }

                // 5. SHOW LOADING STATE AND SUBMIT
                submitBtn.disabled = true;
                submitBtn.classList.add('btn-loading');

                // Small delay to ensure everything is processed
                setTimeout(() => {
                    console.log('Submitting form...');
                    form.submit();
                }, 500);
            });

            // Debug helper: Log when file input changes
            fileInput.addEventListener('change', function(e) {
                console.log('File input changed:', this.files.length, 'files');
            });

            // Add event listener for form submit to capture everything
            form.addEventListener('submit', function(e) {
                console.log('Form submit event fired');

                // Final check before actual submission
                const formData = new FormData(form);
                console.log('Final FormData check:');

                for (let [key, value] of formData.entries()) {
                    if (value instanceof File) {
                        console.log(`${key}: File - ${value.name} (${value.size} bytes)`);
                    } else {
                        console.log(`${key}: ${value}`);
                    }
                }
            });
        });
    </script>
@endpush
