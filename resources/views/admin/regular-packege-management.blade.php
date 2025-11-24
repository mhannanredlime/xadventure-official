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
                    <button class="btn btn-save jatio-bg-color" onclick="submitForm()">Update Package</button>
                @else
                    <button class="btn btn-save jatio-bg-color" onclick="submitForm()">Save Package</button>
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
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="packageName" class="form-label">Package Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('packageName') is-invalid @enderror"
                                    id="packageName" name="packageName"
                                    value="{{ old('packageName', $package->name ?? '') }}" required>
                                @error('packageName')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="subTitle" class="form-label">Sub Title</label>
                                <input type="text" class="form-control @error('subTitle') is-invalid @enderror"
                                    id="subTitle" name="subTitle" value="{{ old('subTitle', $package->subtitle ?? '') }}">
                                @error('subTitle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="packageType" class="form-label">Package Type <span
                                        class="text-danger">*</span></label>
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
                                <label for="minParticipant" class="form-label">Minimum Participant <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('minParticipant') is-invalid @enderror"
                                    id="minParticipant" name="minParticipant"
                                    value="{{ old('minParticipant', $package->min_participants ?? 5) }}" required
                                    min="1">
                                @error('minParticipant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="maxParticipant" class="form-label">Maximum Participant <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('maxParticipant') is-invalid @enderror"
                                    id="maxParticipant" name="maxParticipant"
                                    value="{{ old('maxParticipant', $package->max_participants ?? 50) }}" required
                                    min="1">
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
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-day="monday">Monday</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-day="tuesday">Tuesday</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-day="wednesday">Wednesday</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-day="thursday">Thursday</a>
                        </li>
                    </ul>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="weekdayPrice" class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01"
                                    class="form-control @error('weekdayPrice') is-invalid @enderror" id="weekdayPrice"
                                    name="weekdayPrice"
                                    value="{{ old('weekdayPrice', isset($package) && $package->variants->first() ? $package->variants->first()->prices->where('price_type', 'weekday')->first()->amount ?? 1000 : 1000) }}"
                                    required min="0">
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
                        <li class="nav-item">
                            <a href="#" class="nav-link active" data-day="friday">Friday</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-day="saturday">Saturday</a>
                        </li>
                    </ul>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="weekendPrice" class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01"
                                    class="form-control @error('weekendPrice') is-invalid @enderror" id="weekendPrice"
                                    name="weekendPrice"
                                    value="{{ old('weekendPrice', isset($package) && $package->variants->first() ? $package->variants->first()->prices->where('price_type', 'weekend')->first()->amount ?? 1500 : 1500) }}"
                                    required min="0">
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

    <!-- Hidden Forms -->
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

            <!-- Hidden field for gallery image IDs -->
            <input type="hidden" name="gallery_image_ids" id="galleryImageIds" value="">

            <!-- File input for new uploads -->
            <input type="file" name="images[]" multiple accept="image/*" style="display: none;" id="newImagesInput">
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

            <!-- Hidden field for gallery image IDs -->
            <input type="hidden" name="gallery_image_ids" id="galleryImageIds" value="">

            <!-- File input for new uploads -->
            <input type="file" name="images[]" multiple accept="image/*" style="display: none;" id="newImagesInput">
        </form>
    @endif

    <script>
        // Global variables for form data and image handling
        let selectedDays = {
            weekday: 'monday',
            weekend: 'friday'
        };

        let dayPrices = {
            monday: '1000',
            tuesday: '1000',
            wednesday: '1000',
            thursday: '1000',
            friday: '1500',
            saturday: '1500'
        };

        function updateFormData() {
            const form = document.getElementById('{{ isset($package) ? 'updateForm' : 'createForm' }}');
            const galleryImageIdsInput = document.getElementById('galleryImageIds');
            const newImagesInput = document.getElementById('newImagesInput');

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

            console.log('Updating form data:', {
                packageName,
                subTitle,
                packageType,
                displayStartingPrice,
                minParticipant,
                maxParticipant,
                weekdayPrice,
                weekendPrice
            });

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

            // Handle image uploads - both gallery and new files
            handleImageUploads(form, galleryImageIdsInput, newImagesInput);
        }

        function handleImageUploads(form, galleryImageIdsInput, newImagesInput) {
            const multipleImageUpload = document.querySelector('#multiple-image-upload');

            if (multipleImageUpload && window.multipleImageUploadInstance) {
                const selectedFiles = window.multipleImageUploadInstance.getSelectedFiles();

                if (selectedFiles && selectedFiles.length > 0) {
                    // Separate gallery images (with IDs) from new files
                    const galleryImages = selectedFiles.filter(file => file.id && !(file instanceof File));
                    const newFiles = selectedFiles.filter(file => file instanceof File);

                    // Set gallery image IDs
                    const galleryImageIds = galleryImages.map(img => img.id).join(',');
                    galleryImageIdsInput.value = galleryImageIds;
                    console.log('Gallery Image IDs:', galleryImageIds);

                    // Set new files to file input
                    if (newFiles.length > 0) {
                        const dt = new DataTransfer();
                        newFiles.forEach(file => {
                            dt.items.add(file);
                        });
                        newImagesInput.files = dt.files;
                        console.log('New files to upload:', newFiles.length);
                    } else {
                        newImagesInput.files = new DataTransfer().files;
                    }
                } else {
                    // No images selected
                    galleryImageIdsInput.value = '';
                    newImagesInput.files = new DataTransfer().files;
                    console.log('No images selected');
                }
            } else {
                console.log('Multiple image upload instance not found');
            }
        }

        // Enhanced form submission with image validation
        function submitForm() {
            if (!validateForm()) {
                showAlert('Please fix the validation errors before submitting.', 'error');
                return false;
            }

            updateFormData();

            // Show loading state
            const submitBtn = document.querySelector('.btn-save');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;

            const form = document.getElementById('{{ isset($package) ? 'updateForm' : 'createForm' }}');

            console.log('Submitting form...');
            form.submit();

            return true;
        }

        function showAlert(message, type = 'info') {
            // Remove any existing custom alerts
            const existingAlert = document.querySelector('.custom-form-alert');
            if (existingAlert) {
                existingAlert.remove();
            }

            const alertClass = type === 'error' ? 'alert-danger' : 'alert-info';
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show custom-form-alert" role="alert">
                    <i class="fas ${type === 'error' ? 'fa-exclamation-triangle' : 'fa-info-circle'}"></i> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            document.querySelector('main').insertAdjacentHTML('afterbegin', alertHtml);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize pricing pills
            initializePricingPills();

            // Initialize form validation
            initializeFormValidation();

            // Set initial active states for pricing pills
            const initialWeekdayPill = document.querySelector('#weekdayPills .nav-link[data-day="monday"]');
            if (initialWeekdayPill) {
                initialWeekdayPill.classList.add('active');
            }

            const initialWeekendPill = document.querySelector('#weekendPills .nav-link[data-day="friday"]');
            if (initialWeekendPill) {
                initialWeekendPill.classList.add('active');
            }

            // Initialize multiple image upload with enhanced event handling
            initializeImageUploadEvents();

            // Add real-time validation for participant numbers
            const minParticipant = document.getElementById('minParticipant');
            const maxParticipant = document.getElementById('maxParticipant');

            if (minParticipant && maxParticipant) {
                minParticipant.addEventListener('input', validateParticipantNumbers);
                maxParticipant.addEventListener('input', validateParticipantNumbers);
            }
        });

        function initializePricingPills() {
            const weekdayPills = document.querySelectorAll('#weekdayPills .nav-link');
            const weekendPills = document.querySelectorAll('#weekendPills .nav-link');

            // Initialize weekday pills
            weekdayPills.forEach(pill => {
                pill.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Save current price to previously active weekday
                    const activeWeekday = document.querySelector('#weekdayPills .nav-link.active');
                    if (activeWeekday) {
                        const currentPrice = document.getElementById('weekdayPrice').value;
                        const previousDay = activeWeekday.getAttribute('data-day');
                        dayPrices[previousDay] = currentPrice;
                    }

                    // Activate new pill
                    weekdayPills.forEach(p => p.classList.remove('active'));
                    this.classList.add('active');

                    // Update price field with selected day's price
                    const selectedDay = this.getAttribute('data-day');
                    document.getElementById('weekdayPrice').value = dayPrices[selectedDay];
                    selectedDays.weekday = selectedDay;

                    console.log('Switched to weekday:', selectedDay, 'Price:', dayPrices[selectedDay]);
                });
            });

            // Initialize weekend pills
            weekendPills.forEach(pill => {
                pill.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Save current price to previously active weekend
                    const activeWeekend = document.querySelector('#weekendPills .nav-link.active');
                    if (activeWeekend) {
                        const currentPrice = document.getElementById('weekendPrice').value;
                        const previousDay = activeWeekend.getAttribute('data-day');
                        dayPrices[previousDay] = currentPrice;
                    }

                    // Activate new pill
                    weekendPills.forEach(p => p.classList.remove('active'));
                    this.classList.add('active');

                    // Update price field with selected day's price
                    const selectedDay = this.getAttribute('data-day');
                    document.getElementById('weekendPrice').value = dayPrices[selectedDay];
                    selectedDays.weekend = selectedDay;

                    console.log('Switched to weekend:', selectedDay, 'Price:', dayPrices[selectedDay]);
                });
            });

            // Initialize price input listeners
            const weekdayPriceInput = document.getElementById('weekdayPrice');
            const weekendPriceInput = document.getElementById('weekendPrice');

            if (weekdayPriceInput) {
                weekdayPriceInput.addEventListener('input', function() {
                    const activePill = document.querySelector('#weekdayPills .nav-link.active');
                    if (activePill) {
                        const currentDay = activePill.getAttribute('data-day');
                        dayPrices[currentDay] = this.value;
                        console.log('Updated weekday price for', currentDay, ':', this.value);
                    }
                });
            }

            if (weekendPriceInput) {
                weekendPriceInput.addEventListener('input', function() {
                    const activePill = document.querySelector('#weekendPills .nav-link.active');
                    if (activePill) {
                        const currentDay = activePill.getAttribute('data-day');
                        dayPrices[currentDay] = this.value;
                        console.log('Updated weekend price for', currentDay, ':', this.value);
                    }
                });
            }
        }

        function initializeImageUploadEvents() {
            // Listen for custom events from the multiple image upload component
            document.addEventListener('imageSelectionChanged', function(e) {
                console.log('Image selection changed:', e.detail);
            });

            document.addEventListener('imageUploadProgress', function(e) {
                console.log('Upload progress:', e.detail);
            });

            document.addEventListener('imageUploadComplete', function(e) {
                console.log('Upload complete:', e.detail);
            });
        }

        function validateForm() {
            let isValid = true;
            const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');

            // Validate required fields
            requiredFields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });

            // Validate price fields
            const weekdayPrice = document.getElementById('weekdayPrice');
            const weekendPrice = document.getElementById('weekendPrice');

            if (weekdayPrice && (parseFloat(weekdayPrice.value) < 0 || isNaN(parseFloat(weekdayPrice.value)))) {
                showFieldError(weekdayPrice, 'Please enter a valid price');
                isValid = false;
            }

            if (weekendPrice && (parseFloat(weekendPrice.value) < 0 || isNaN(parseFloat(weekendPrice.value)))) {
                showFieldError(weekendPrice, 'Please enter a valid price');
                isValid = false;
            }

            // Validate participant numbers
            if (!validateParticipantNumbers()) {
                isValid = false;
            }

            return isValid;
        }

        function validateParticipantNumbers() {
            const minParticipant = document.getElementById('minParticipant');
            const maxParticipant = document.getElementById('maxParticipant');
            let isValid = true;

            if (minParticipant && maxParticipant) {
                const min = parseInt(minParticipant.value);
                const max = parseInt(maxParticipant.value);

                clearFieldError(minParticipant);
                clearFieldError(maxParticipant);

                if (min > max) {
                    showFieldError(maxParticipant,
                        'Maximum participants must be greater than or equal to minimum participants');
                    isValid = false;
                }

                if (min < 1) {
                    showFieldError(minParticipant, 'Minimum participants must be at least 1');
                    isValid = false;
                }

                if (max < 1) {
                    showFieldError(maxParticipant, 'Maximum participants must be at least 1');
                    isValid = false;
                }
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
            errorDiv.style.display = 'block';
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

        function initializeFormValidation() {
            const inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });

                input.addEventListener('input', function() {
                    clearFieldError(this);
                });
            });
        }

        // Debug function to check current state
        function debugFormState() {
            console.log('=== FORM DEBUG INFO ===');
            console.log('Selected Days:', selectedDays);
            console.log('Day Prices:', dayPrices);
            console.log('Form Values:', {
                packageName: document.getElementById('packageName').value,
                packageType: document.getElementById('packageType').value,
                minParticipant: document.getElementById('minParticipant').value,
                maxParticipant: document.getElementById('maxParticipant').value,
                weekdayPrice: document.getElementById('weekdayPrice').value,
                weekendPrice: document.getElementById('weekendPrice').value
            });

            const galleryImageIds = document.getElementById('galleryImageIds').value;
            const newImagesInput = document.getElementById('newImagesInput');
            console.log('Gallery Image IDs:', galleryImageIds);
            console.log('New Files:', newImagesInput.files.length);
        }

        // Expose debug function to global scope for testing
        window.debugFormState = debugFormState;
    </script>

    <style>
        .custom-form-alert {
            margin-bottom: 1rem;
        }

        .pricing-pills .nav-link {
            cursor: pointer;
        }

        .pricing-pills .nav-link.active {
            background-color: #007bff;
            color: white;
        }

        .main-content-packege-manage {
            padding: 20px;
        }

        .page-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .breadcrumb-custom {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 0;
        }

        .btn-save {
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            font-weight: 500;
        }

        .jatio-bg-color {
            background-color: #007bff;
        }

        .jatio-bg-color:hover {
            background-color: #0056b3;
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        .card-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .text-danger {
            color: #dc3545;
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }
    </style>
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
