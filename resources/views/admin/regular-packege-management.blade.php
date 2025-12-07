@extends('layouts.admin')

@section('title', 'Add Regular Package')

@section('content')
    <main class="main-content-packege-manage">
        <header class="d-flex justify-content-between align-items-center page-header">
            <div>
                <h1>{{ isset($package) ? 'Edit Regular Package' : 'Add Regular Package' }}</h1>
                <p class="breadcrumb-custom">Package Management >
                    {{ isset($package) ? 'Edit Regular Package' : 'Add Regular Package' }}</p>
            </div>
            <div>
                @if (isset($package))
                    <button class="btn btn-save jatio-bg-color"
                        onclick="updateFormData(); document.getElementById('updateForm').submit()">Update Package</button>
                @else
                    <button class="btn btn-save jatio-bg-color"
                        onclick="updateFormData(); document.getElementById('createForm').submit()">Save Package</button>
                @endif
            </div>
        </header>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> Please correct the following errors:
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body p-4">
                <h5 class="card-title">Package Details</h5>
                <div class="row g-4">
                    <div class="col-lg-12 package-multiple-image-upload-form">
                        <label for="multiple-image-upload" class="form-label">Upload Images</label>
                        <div id="multiple-image-upload" data-model-type="App\Models\Package"
                            data-model-id="{{ $package->id ?? '' }}"
                            data-upload-url="{{ route('admin.regular-packege-management.store') }}"
                            data-update-url="{{ isset($package) ? route('admin.regular-packege-management.update', $package) : '' }}"
                            data-images-url="{{ route('admin.images.get') }}"
                            data-primary-url="{{ url('admin/images') }}/:id/primary"
                            data-reorder-url="{{ route('admin.images.reorder') }}"
                            data-alt-text-url="{{ url('admin/images') }}/:id/alt-text"
                            data-delete-url="{{ url('admin/images') }}/:id"
                            data-existing-images="{{ isset($package) ? $package->images->toJson() : '[]' }}"
                            data-debug="{{ isset($package) ? 'Package ID: ' . $package->id . ', Images: ' . $package->images->count() : 'Creating new package' }}">
                            <!-- Multiple image upload component will be initialized here -->
                        </div>
                        <!-- Hidden input for form submission -->
                        <input type="file" name="images[]" multiple style="display: none;">
                    </div>
                    <div class="col-lg-12">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="packageName" class="form-label">Package Name</label>
                                <input type="text" class="form-control @error('packageName') is-invalid @enderror"
                                    id="packageName" name="packageName"
                                    value="{{ old('packageName', $package->name ?? 'Kids Fun') }}" required>
                                @error('packageName')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="subTitle" class="form-label">Sub Title</label>
                                <input type="text" class="form-control @error('subTitle') is-invalid @enderror"
                                    id="subTitle" name="subTitle"
                                    value="{{ old('subTitle', $package->subtitle ?? 'Kids Friendly Fun Zone') }}">
                                @error('subTitle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="packageType" class="form-label">Package Type</label>
                                <select class="form-select @error('packageType') is-invalid @enderror" id="packageType"
                                    name="packageType" required>
                                    <option value="">Select Package Type</option>
                                    <option value="Single"
                                        {{ old('packageType', isset($package) && $package->variants->first() ? $package->variants->first()->variant_name : '') == 'Single' ? 'selected' : '' }}>
                                        Single</option>
                                    <option value="Bundle"
                                        {{ old('packageType', isset($package) && $package->variants->first() ? $package->variants->first()->variant_name : '') == 'Bundle' ? 'selected' : '' }}>
                                        Bundle</option>
                                    <option value="Group"
                                        {{ old('packageType', isset($package) && $package->variants->first() ? $package->variants->first()->variant_name : '') == 'Group' ? 'selected' : '' }}>
                                        Group</option>
                                </select>
                                @error('packageType')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="details" class="form-label">Details</label>
                                <textarea class="form-control @error('details') is-invalid @enderror" id="details" name="details" rows="4"
                                    placeholder="Type here...">{{ old('details', $package->details ?? '') }}</textarea>
                                @error('details')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="displayStartingPrice" class="form-label">Display Starting Price (TK)</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01"
                                        class="form-control @error('displayStartingPrice') is-invalid @enderror"
                                        id="displayStartingPrice" name="displayStartingPrice"
                                        value="{{ old('displayStartingPrice', $package->display_starting_price ?? '') }}"
                                        placeholder="e.g., 99.00">
                                </div>
                                <small class="form-text text-muted">This price will be displayed as "Starting from TK X" on
                                    the website. Leave empty to use calculated minimum price.</small>
                                @error('displayStartingPrice')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="minParticipant" class="form-label">Minimum Participant</label>
                                <input type="number" class="form-control @error('minParticipant') is-invalid @enderror"
                                    id="minParticipant" name="minParticipant"
                                    value="{{ old('minParticipant', $package->min_participants ?? 5) }}" required>
                                @error('minParticipant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="maxParticipant" class="form-label">Maximum Participant</label>
                                <input type="number" class="form-control @error('maxParticipant') is-invalid @enderror"
                                    id="maxParticipant" name="maxParticipant"
                                    value="{{ old('maxParticipant', $package->max_participants ?? 50) }}" required>
                                @error('maxParticipant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Pricing Details</h5>
                <div class="mb-4">
                    <label class="form-label">Weekdays Prices</label>
                    <ul class="nav nav-pills pricing-pills mb-3" id="weekdayPills">
                        <li class="nav-item"><a href="#" class="nav-link disabled"
                                style="opacity: 0.6; cursor: not-allowed; pointer-events: none;">Sunday</a></li>
                        <li class="nav-item"><a href="#" class="nav-link disabled"
                                style="opacity: 0.6; cursor: not-allowed; pointer-events: none;">Monday</a></li>
                        <li class="nav-item"><a href="#" class="nav-link disabled"
                                style="opacity: 0.6; cursor: not-allowed; pointer-events: none;">Tuesday</a></li>
                        <li class="nav-item"><a href="#" class="nav-link disabled"
                                style="opacity: 0.6; cursor: not-allowed; pointer-events: none;">Wednesday</a></li>
                        <li class="nav-item"><a href="#" class="nav-link disabled"
                                style="opacity: 0.6; cursor: not-allowed; pointer-events: none;">Thursday</a></li>
                    </ul>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="weekdayPrice" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="text" class="form-control @error('weekdayPrice') is-invalid @enderror"
                                    id="weekdayPrice" name="weekdayPrice"
                                    value="{{ old('weekdayPrice', isset($package) && $package->variants->first() ? $package->variants->first()->prices->where('price_type', 'weekday')->first()->amount ?? 1000 : 1000) }}"
                                    required>
                            </div>
                            @error('weekdayPrice')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div>
                    <label class="form-label">Weekend Prices</label>
                    <ul class="nav nav-pills pricing-pills mb-3" id="weekendPills">
                        <li class="nav-item"><a href="#" class="nav-link disabled"
                                style="opacity: 0.6; cursor: not-allowed; pointer-events: none;">Friday</a></li>
                        <li class="nav-item"><a href="#" class="nav-link disabled"
                                style="opacity: 0.6; cursor: not-allowed; pointer-events: none;">Saturday</a></li>
                    </ul>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="weekendPrice" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="text" class="form-control @error('weekendPrice') is-invalid @enderror"
                                    id="weekendPrice" name="weekendPrice"
                                    value="{{ old('weekendPrice', isset($package) && $package->variants->first() ? $package->variants->first()->prices->where('price_type', 'weekend')->first()->amount ?? 1500 : 1500) }}"
                                    required>
                            </div>
                            @error('weekendPrice')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @if (isset($package))
        <form id="updateForm" method="POST" action="{{ route('admin.regular-packege-management.update', $package) }}"
            enctype="multipart/form-data" style="display: none;">
            @csrf
            @method('PUT')
            <input type="hidden" name="packageName" value="">
            <input type="hidden" name="subTitle" value="">
            <input type="hidden" name="packageType" value="">
            <input type="hidden" name="details" value="">
            <input type="hidden" name="displayStartingPrice" value="">
            <input type="hidden" name="minParticipant" value="">
            <input type="hidden" name="maxParticipant" value="">
            <input type="hidden" name="weekdayPrice" value="">
            <input type="hidden" name="weekendPrice" value="">
            <input type="hidden" name="selected_weekday" value="monday">
            <input type="hidden" name="selected_weekend" value="friday">
            <input type="file" name="images[]" multiple accept="image/*" style="display: none;">
        </form>
    @else
        <form id="createForm" method="POST" action="{{ route('admin.regular-packege-management.store') }}"
            enctype="multipart/form-data" style="display: none;">
            @csrf
            <input type="hidden" name="packageName" value="">
            <input type="hidden" name="subTitle" value="">
            <input type="hidden" name="packageType" value="">
            <input type="hidden" name="details" value="">
            <input type="hidden" name="displayStartingPrice" value="">
            <input type="hidden" name="minParticipant" value="">
            <input type="hidden" name="maxParticipant" value="">
            <input type="hidden" name="weekdayPrice" value="">
            <input type="hidden" name="weekendPrice" value="">
            <input type="hidden" name="selected_weekday" value="monday">
            <input type="hidden" name="selected_weekend" value="friday">
            <input type="file" name="images[]" multiple accept="image/*" style="display: none;">
        </form>
    @endif

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imageElement = document.getElementById('packageImage');
                    if (imageElement) {
                        // Check if it's currently a div (no image selected)
                        if (imageElement.tagName === 'DIV') {
                            // Create a new img element
                            const newImg = document.createElement('img');
                            newImg.id = 'packageImage';
                            newImg.style.width = '100%';
                            newImg.style.height = '100%';
                            newImg.style.objectFit = 'cover';
                            newImg.src = e.target.result;
                            newImg.alt = 'Package Image';

                            // Replace the div with the new img
                            imageElement.parentNode.replaceChild(newImg, imageElement);
                        } else {
                            // It's already an img element, just update src
                            imageElement.src = e.target.result;
                        }
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Global variables for form data
        let selectedDays = {
            weekday: 'monday',
            weekend: 'friday'
        };

        function updateFormData() {
            const form = document.getElementById('{{ isset($package) ? "updateForm" : "createForm" }}');

            // Get form field values
            const packageName = document.getElementById('packageName').value;
            const subTitle = document.getElementById('subTitle').value;
            const packageType = document.getElementById('packageType').value;
            const details = document.getElementById('details').value;
            const displayStartingPrice = document.getElementById('displayStartingPrice').value;
            const minParticipant = document.getElementById('minParticipant').value;
            const maxParticipant = document.getElementById('maxParticipant').value;
            const weekdayPrice = document.getElementById('weekdayPrice').value;
            const weekendPrice = document.getElementById('weekendPrice').value;

            // Form values logged

            // Update hidden form fields
            form.querySelector('input[name="packageName"]').value = packageName;
            form.querySelector('input[name="subTitle"]').value = subTitle;
            form.querySelector('input[name="packageType"]').value = packageType;
            form.querySelector('input[name="details"]').value = details;
            form.querySelector('input[name="displayStartingPrice"]').value = displayStartingPrice;
            form.querySelector('input[name="minParticipant"]').value = minParticipant;
            form.querySelector('input[name="maxParticipant"]').value = maxParticipant;
            form.querySelector('input[name="weekdayPrice"]').value = weekdayPrice;
            form.querySelector('input[name="weekendPrice"]').value = weekendPrice;

            // Update selected day states
            form.querySelector('input[name="selected_weekday"]').value = selectedDays.weekday;
            form.querySelector('input[name="selected_weekend"]').value = selectedDays.weekend;

            // Handle multiple image upload
            const multipleImageUpload = document.querySelector('#multiple-image-upload');
            const formImageInput = form.querySelector('input[name="images[]"]');
            if (multipleImageUpload && window.multipleImageUploadInstance) {
                const selectedFiles = window.multipleImageUploadInstance.getSelectedFiles();
                if (selectedFiles && selectedFiles.length > 0) {
                    // Separate real files from gallery images
                    const realFiles = selectedFiles.filter(file => file instanceof File);
                    const galleryImages = selectedFiles.filter(file => !(file instanceof File));
                    
                    // Create a new FileList-like object for real files only
                    if (realFiles.length > 0) {
                        const dt = new DataTransfer();
                        realFiles.forEach(file => {
                            dt.items.add(file);
                        });
                        formImageInput.files = dt.files;
                    }
                    
                    // For gallery images, we need to handle them differently
                    // They will be processed by the multiple image upload component
                    // and sent via AJAX to the server
                    if (galleryImages.length > 0) {
                        // The gallery images are already handled by the multiple image upload component
                        // They will be sent as separate AJAX requests to copy images to the package
                    }
                }
            }
        }

        // Update form data before submission
        document.addEventListener('DOMContentLoaded', function() {
            // Store day prices in a simple object
            const dayPrices = {
                sunday: '1000',
                monday: '1000',
                tuesday: '1000',
                wednesday: '1000',
                thursday: '1000',
                friday: '1500',
                saturday: '1500'
            };

            // Update global selectedDays with current values from hidden inputs
            const hiddenWeekday = document.querySelector('input[name="selected_weekday"]');
            const hiddenWeekend = document.querySelector('input[name="selected_weekend"]');
            if (hiddenWeekday && hiddenWeekday.value) selectedDays.weekday = hiddenWeekday.value;
            if (hiddenWeekend && hiddenWeekend.value) selectedDays.weekend = hiddenWeekend.value;

            // Handle weekday pill clicks
            const weekdayPills = document.querySelectorAll('#weekdayPills .nav-link');
            weekdayPills.forEach(pill => {
                pill.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Save current price to the previously active pill
                    const previouslyActive = document.querySelector(
                        '#weekdayPills .nav-link.active');
                    if (previouslyActive) {
                        const currentPrice = document.getElementById('weekdayPrice').value;
                        const previousDay = previouslyActive.getAttribute('data-day');
                        dayPrices[previousDay] = currentPrice;
                        // Saved price for previous day
                    }

                    // Remove active class from all weekday pills
                    weekdayPills.forEach(p => {
                        p.classList.remove('active');
                    });

                    // Add active class to clicked pill
                    this.classList.add('active');

                    // Update the label and price
                    const selectedDay = this.getAttribute('data-day');
                    const selectedPrice = dayPrices[selectedDay];
                    document.getElementById('selectedWeekday').textContent = this.textContent;
                    document.getElementById('weekdayPrice').value = selectedPrice;

                    // Save the selected weekday state
                    selectedDays.weekday = selectedDay;

                    // Switched to selected day
                });
            });

            // Handle weekend pill clicks
            const weekendPills = document.querySelectorAll('#weekendPills .nav-link');
            weekendPills.forEach(pill => {
                pill.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Save current price to the previously active pill
                    const previouslyActive = document.querySelector(
                        '#weekendPills .nav-link.active');
                    if (previouslyActive) {
                        const currentPrice = document.getElementById('weekendPrice').value;
                        const previousDay = previouslyActive.getAttribute('data-day');
                        dayPrices[previousDay] = currentPrice;
                        // Saved weekend price for previous day
                    }

                    // Remove active class from all weekend pills
                    weekendPills.forEach(p => {
                        p.classList.remove('active');
                    });

                    // Add active class to clicked pill
                    this.classList.add('active');

                    // Update the label and price
                    const selectedDay = this.getAttribute('data-day');
                    const selectedPrice = dayPrices[selectedDay];
                    document.getElementById('selectedWeekend').textContent = this.textContent;
                    document.getElementById('weekendPrice').value = selectedPrice;

                    // Save the selected weekend day state
                    selectedDays.weekend = selectedDay;

                    // Switched to weekend
                });
            });

            // Handle package type changes
            const packageTypeSelect = document.getElementById('packageType');
            if (packageTypeSelect) {
                packageTypeSelect.addEventListener('change', function() {
                    const selectedType = this.value;
                    // Package type changed
                });
            }

            // Handle form submission
            const submitButtons = document.querySelectorAll('.btn-save');
            submitButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Validate form before submission
                    if (validateForm()) {
                        updateFormData();
                        const form = document.getElementById(
                            '{{ isset($package) ? "updateForm" : "createForm" }}');
                        form.submit();
                    }
                });
            });

            // Add real-time validation
            const inputs = document.querySelectorAll('input[required], select[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });

                input.addEventListener('input', function() {
                    clearFieldError(this);
                });
            });

            // Handle price input changes for dynamic day pricing
            const weekdayPriceInput = document.getElementById('weekdayPrice');
            const weekendPriceInput = document.getElementById('weekendPrice');

            if (weekdayPriceInput) {
                weekdayPriceInput.addEventListener('input', function() {
                    const activeWeekdayPill = document.querySelector('#weekdayPills .nav-link.active');
                    if (activeWeekdayPill) {
                        const currentDay = activeWeekdayPill.getAttribute('data-day');
                        dayPrices[currentDay] = this.value;
                        // Updated weekday price
                    }
                });
            }

            if (weekendPriceInput) {
                weekendPriceInput.addEventListener('input', function() {
                    const activeWeekendPill = document.querySelector('#weekendPills .nav-link.active');
                    if (activeWeekendPill) {
                        const currentDay = activeWeekendPill.getAttribute('data-day');
                        dayPrices[currentDay] = this.value;
                        // Updated weekend price
                    }
                });
            }

            // Ensure form fields are accessible
            const formFields = document.querySelectorAll('input, select, textarea');
            formFields.forEach(field => {
                field.style.position = 'relative';
                field.style.zIndex = '10';
            });

            // Add hover effect for image upload
            const imageContainer = document.querySelector('.image-preview-box');
            const overlay = document.querySelector('.image-upload-overlay');

            if (imageContainer && overlay) {
                imageContainer.addEventListener('mouseenter', function() {
                    overlay.style.opacity = '1';
                });

                imageContainer.addEventListener('mouseleave', function() {
                    overlay.style.opacity = '0';
                });
            }

            // Initialize with current form values if editing
            if (weekdayPriceInput && weekdayPriceInput.value) {
                const activeWeekdayPill = document.querySelector('#weekdayPills .nav-link.active');
                if (activeWeekdayPill) {
                    const currentDay = activeWeekdayPill.getAttribute('data-day');
                    dayPrices[currentDay] = weekdayPriceInput.value;
                    // Initialized weekday price
                }
            }

            if (weekendPriceInput && weekendPriceInput.value) {
                const activeWeekendPill = document.querySelector('#weekendPills .nav-link.active');
                if (activeWeekendPill) {
                    const currentDay = activeWeekendPill.getAttribute('data-day');
                    dayPrices[currentDay] = weekendPriceInput.value;
                    // Initialized weekend price
                }
            }

            // Debug function to check current state
            function debugDayPricing() {
                // DEBUG: Current Day Pricing State

                // Check weekday pills
                const weekdayPills = document.querySelectorAll('#weekdayPills .nav-link');
                weekdayPills.forEach(pill => {
                    const day = pill.getAttribute('data-day');
                    const price = pill.getAttribute('data-price');
                    const isActive = pill.classList.contains('active');
                    // Weekday pricing state
                });

                // Check weekend pills
                const weekendPills = document.querySelectorAll('#weekendPills .nav-link');
                weekendPills.forEach(pill => {
                    const day = pill.getAttribute('data-day');
                    const price = pill.getAttribute('data-price');
                    const isActive = pill.classList.contains('active');
                    // Weekend pricing state
                });

                // Check current form values
                const weekdayPrice = document.getElementById('weekdayPrice').value;
                const weekendPrice = document.getElementById('weekendPrice').value;
                // Form values logged
            }

            // Call debug function
            debugDayPricing();
        });

        function validateForm() {
            let isValid = true;
            const requiredFields = document.querySelectorAll('input[required], select[required]');

            requiredFields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });

            // Validate price fields
            const weekdayPrice = document.getElementById('weekdayPrice');
            const weekendPrice = document.getElementById('weekendPrice');

            if (weekdayPrice && parseFloat(weekdayPrice.value) < 0) {
                showFieldError(weekdayPrice, 'Price must be greater than or equal to 0');
                isValid = false;
            }

            if (weekendPrice && parseFloat(weekendPrice.value) < 0) {
                showFieldError(weekendPrice, 'Price must be greater than or equal to 0');
                isValid = false;
            }

            return isValid;
        }

        function validateField(field) {
            const value = field.value.trim();

            if (field.hasAttribute('required') && !value) {
                showFieldError(field, 'This field is required');
                return false;
            }

            if (field.type === 'email' && value && !isValidEmail(value)) {
                showFieldError(field, 'Please enter a valid email address');
                return false;
            }

            if (field.type === 'number' && value) {
                const numValue = parseFloat(value);
                if (isNaN(numValue) || numValue < 0) {
                    showFieldError(field, 'Please enter a valid positive number');
                    return false;
                }
            }

            clearFieldError(field);
            return true;
        }

        function showFieldError(field, message) {
            clearFieldError(field);
            field.classList.add('is-invalid');

            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
        }

        function clearFieldError(field) {
            field.classList.remove('is-invalid');
            const errorDiv = field.parentNode.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.remove();
            }
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Initialize multiple image upload
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize multiple image upload component
            const uploadContainer = document.getElementById('multiple-image-upload');
            if (uploadContainer) {
                // Multiple image upload initialization is handled in the external JS file
            }

            // Initialize pricing pills
            initializePricingPills();

            // Initialize form validation
            initializeFormValidation();
        });

        // Pricing pills functionality
        function initializePricingPills() {
            const weekdayPills = document.querySelectorAll('#weekdayPills .nav-link');
            const weekendPills = document.querySelectorAll('#weekendPills .nav-link');

            weekdayPills.forEach(pill => {
                pill.addEventListener('click', function(e) {
                    e.preventDefault();
                    weekdayPills.forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                    document.getElementById('selected_weekday').value = this.dataset.day;
                    document.getElementById('weekdayPrice').value = this.dataset.price;
                });
            });

            weekendPills.forEach(pill => {
                pill.addEventListener('click', function(e) {
                    e.preventDefault();
                    weekendPills.forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                    document.getElementById('selected_weekend').value = this.dataset.day;
                    document.getElementById('weekendPrice').value = this.dataset.price;
                });
            });
        }

        // Form validation
        function initializeFormValidation() {
            const form = document.getElementById('packageForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const vehicleType = document.getElementById('vehicleType').value;
                    const packageName = document.getElementById('packageName').value;
                    const packageType = document.getElementById('packageType').value;

                    if (!vehicleType || !packageName || !packageType) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                        return false;
                    }
                });
            }
        }
    </script>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/gallery.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('admin/js/multiple-image-upload.js') }}"></script>
    <script src="{{ asset('admin/js/gallery-manager.js') }}"></script>
@endpush

@include('admin.gallery.partials.gallery-modal')
