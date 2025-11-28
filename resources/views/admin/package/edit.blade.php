@extends('layouts.admin')
@section('title', isset($package) ? 'Edit Regular Package' : 'Add Regular Package')
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

        /* Day selector pills */
        .day-pill {
            margin: 0.25rem;
        }

        .day-pill .btn {
            min-width: 70px;
            text-transform: uppercase;
        }

        .price-input {
            max-width: 150px;
        }

        .jatio-color {
            /* background-color: #F76B19; */
            color: white;
        }

        .jatio-color:hover {
            background-color: #e55e14;
            color: white;
        }

        .day-price-input:invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            display: block;
            font-size: 0.875em;
        }

        .alert ul {
            margin-bottom: 0;
        }

        .weekend-badge {
            font-size: 0.7em;
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
    <main class="mt-4">
        <header class="d-flex justify-content-between align-items-center page-header mb-4">
            <div>
                <h3>{{ isset($package) ? 'Edit Regular Package' : 'Add Regular Package' }}</h3>
                <p class="breadcrumb-custom"><i class="bi bi-home me-1"></i> Package Management >
                    {{ isset($package) ? 'Edit' : 'Add' }} Package</p>
            </div>
            <a href="{{ url('admin/add-packege-management') }}" class="btn btn-outline-secondary"><i
                    class="bi bi-arrow-left me-2"></i>Back to Packages</a>
        </header>

        {{-- Alerts --}}
        @foreach (['success', 'error'] as $msg)
            @if (session($msg))
                <div class="alert alert-{{ $msg == 'success' ? 'success' : 'danger' }} alert-dismissible fade show"
                    role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi {{ $msg == 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-2"></i>
                        {{ session($msg) }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach

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

        <form id="packageForm" method="POST"
            action="{{ isset($package) ? route('admin.regular-packege-management.update', $package->id) : route('admin.regular-packege-management.store') }}"
            enctype="multipart/form-data" novalidate>
            @csrf
            @if (isset($package))
                @method('PUT')
            @endif

            {{-- ---------- Image Upload Section ---------- --}}
            <div class="card p-4">
                <h5 class="card-title"><i class="bi bi-images me-2"></i>Package Images</h5>
                <div class="row">
                    <div class="col-12">
                        <label class="form-label">Upload Package Images (Max 4 images)</label>
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
                            data-max-files="4" data-max-file-size="{{ 5 * 1024 * 1024 }}">
                        </div>
                        <input type="file" id="package_images_input" name="images[]" multiple accept="image/*"
                            style="display:none;">
                        <input type="hidden" id="gallery_images_input" name="gallery_images">
                    </div>
                </div>
            </div>

            {{-- ---------- Package Details ---------- --}}
            <div class="card p-4">
                <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>Package Details</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Package Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('packageName') is-invalid @enderror"
                            name="packageName" value="{{ old('packageName', $package->name ?? '') }}"
                            placeholder="Enter package name" required>
                        @error('packageName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sub Title</label>
                        <input type="text" class="form-control @error('subTitle') is-invalid @enderror" name="subTitle"
                            value="{{ old('subTitle', $package->subtitle ?? '') }}" placeholder="Enter package subtitle">
                        @error('subTitle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Package Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('packageType') is-invalid @enderror" name="packageType" required>
                            <option value="">Select Package Type</option>
                            @foreach ($packageTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('packageType', $package->package_type_id ?? '') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('packageType')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Package Details</label>
                        <textarea class="form-control @error('details') is-invalid @enderror" name="details" rows="5"
                            placeholder="Describe the package details...">{{ old('details', $package->details ?? '') }}</textarea>
                        @error('details')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Display Base Price (৳) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01"
                                class="form-control @error('displayStartingPrice') is-invalid @enderror"
                                name="displayStartingPrice"
                                value="{{ old('displayStartingPrice', $package->display_starting_price ?? '') }}"
                                placeholder="0.00" min="50" required>
                        </div>
                        @error('displayStartingPrice')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Minimum Participants <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('minParticipant') is-invalid @enderror"
                            name="minParticipant" value="{{ old('minParticipant', $package->min_participants ?? '') }}"
                            min="1" required>
                        @error('minParticipant')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maximum Participants <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('maxParticipant') is-invalid @enderror"
                            name="maxParticipant" value="{{ old('maxParticipant', $package->max_participants ?? '') }}"
                            min="1" required>
                        @error('maxParticipant')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ---------- Package Pricing (Day-wise) ---------- --}}
            <div class="card p-4">
                <h5 class="card-title"><i class="bi bi-tag me-2"></i>Package Pricing (Day-wise)</h5>
                <div class="mb-3">
                    <label class="fw-semibold mb-2">Select Active Days <span class="text-danger">*</span></label>
                    <div class="d-flex flex-wrap">
                        @foreach ($days as $day)
                            <div class="form-check form-check-inline day-pill">
                                <input type="checkbox" class="btn-check day-checkbox" id="day-{{ $day }}"
                                    value="{{ $day }}" autocomplete="off"
                                    {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                                <label class="btn btn-sm jatio-color"
                                    for="day-{{ $day }}">{{ strtoupper($day) }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div id="priceContainer" class="row">
                    @if (count($selectedDays) > 0)
                        @foreach ($selectedDays as $day)
                            @php
                                $isWeekend = in_array($day, ['fri', 'sat']);
                                $priceValue = $dayPrices[$day] ?? '';
                            @endphp
                            <div class="col-12 col-sm-6 col-md-4 mb-3">
                                <label class="form-label text-uppercase">
                                    {{ $day }} Price (৳)
                                    @if ($isWeekend)
                                        <span class="badge bg-warning text-dark weekend-badge">Weekend</span>
                                    @endif
                                </label>
                                <input type="number" class="form-control day-price-input"
                                    data-day="{{ $day }}" value="{{ $priceValue }}"
                                    placeholder="Enter price" min="0" step="0.01">
                                <div class="invalid-feedback" id="error-{{ $day }}"></div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Please select at least one day for pricing
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-3">
                    <label class="fw-semibold">Apply Same Price to All Selected Days</label>
                    <div class="d-flex align-items-end gap-2 mb-3">
                        <div class="flex-grow-1">
                            <input type="number" class="form-control" id="applyAllPrice" placeholder="e.g. 1200"
                                min="0" step="0.01">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-dark" id="applyAllBtn">Apply to All</button>
                </div>

                <input type="hidden" name="active_days" id="activeDaysInput"
                    value="{{ old('active_days', json_encode($selectedDays)) }}">
                <input type="hidden" name="day_prices" id="dayPricesInput"
                    value="{{ old('day_prices', json_encode($dayPrices)) }}">
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-save" id="submitBtn">
                    <i class="bi bi-save me-2"></i>
                    {{ isset($package) ? 'Update Package' : 'Save Package' }}
                </button>
            </div>
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
            const realFileInput = document.getElementById('package_images_input');
            const galleryInput = document.getElementById('gallery_images_input');

            // Initialize image uploader if container exists
            const container = document.getElementById('multiple-image-upload');
            let uploader;
            if (container) {
                uploader = new MultipleImageUpload(container.id, {
                    maxFiles: parseInt(container.dataset.maxFiles) || 4,
                    maxFileSize: parseInt(container.dataset.maxFileSize) || 5 * 1024 * 1024
                });
                window.multipleImageUploadInstance = uploader;
            }

            // -------------------------------------------------
            // Day-wise Pricing Variables
            // -------------------------------------------------
            let selectedDays = new Set({!! json_encode($selectedDays) !!});
            let prices = {!! json_encode($dayPrices ?? []) !!};
            const priceContainer = $("#priceContainer");

            // -------------------------------------------------
            // Render Price Inputs
            // -------------------------------------------------
            function renderPriceInputs() {
                priceContainer.empty();

                if (selectedDays.size === 0) {
                    priceContainer.html(`
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Please select at least one day for pricing
                            </div>
                        </div>
                    `);
                    return;
                }

                // Convert to array and sort for consistent display
                const sortedDays = [...selectedDays].sort();

                sortedDays.forEach(day => {
                    let isWeekend = ['fri', 'sat'].includes(day);
                    let weekendBadge = isWeekend ?
                        '<span class="badge bg-warning text-dark weekend-badge">Weekend</span>' :
                        '';

                    let existingValue = prices[day] !== undefined && prices[day] !== null ? prices[day] :
                        '';

                    priceContainer.append(`
                        <div class="col-12 col-sm-6 col-md-4 mb-3">
                            <label class="form-label text-uppercase">
                                ${day} Price (৳) ${weekendBadge}
                            </label>
                            <input 
                                type="number" 
                                class="form-control day-price-input"
                                data-day="${day}"
                                value="${existingValue}"
                                placeholder="Enter price"
                                min="0"
                                step="0.01"
                            >
                            <div class="invalid-feedback" id="error-${day}"></div>
                        </div>
                    `);
                });
            }

            // Initialize on page load
            renderPriceInputs();

            // -------------------------------------------------
            // Select / Unselect Day
            // -------------------------------------------------
            $(".day-checkbox").on("change", function() {
                const day = $(this).val();

                if (this.checked) {
                    selectedDays.add(day);
                    // Initialize price for newly selected day if not exists
                    if (prices[day] === undefined) {
                        prices[day] = null;
                    }
                } else {
                    selectedDays.delete(day);
                    // Keep the price in the object but don't submit it
                }

                updateHiddenInputs();
                renderPriceInputs();
            });

            // -------------------------------------------------
            // Update Individual Day Price
            // -------------------------------------------------
            $(document).on("input", ".day-price-input", function() {
                const day = $(this).data("day");
                const val = $(this).val();
                const $errorField = $(`#error-${day}`);

                // Clear previous errors
                $(this).removeClass('is-invalid');
                $errorField.text('');

                if (val !== "" && val !== null && !isNaN(val)) {
                    const numericValue = parseFloat(val);
                    if (numericValue >= 0) {
                        prices[day] = numericValue;
                    } else {
                        prices[day] = null;
                        $(this).addClass('is-invalid');
                        $errorField.text('Price must be a positive number');
                    }
                } else {
                    prices[day] = null;
                }

                updateHiddenInputs();
            });

            // -------------------------------------------------
            // Apply One Price to All Days
            // -------------------------------------------------
            $("#applyAllBtn").on("click", function() {
                const val = $("#applyAllPrice").val();

                if (val === "" || isNaN(val) || parseFloat(val) < 0) {
                    alert('Please enter a valid positive number');
                    return;
                }

                const numericValue = parseFloat(val);
                [...selectedDays].forEach(day => {
                    prices[day] = numericValue;
                });

                updateHiddenInputs();
                renderPriceInputs();
            });

            // -------------------------------------------------
            // Update Hidden Inputs
            // -------------------------------------------------
            function updateHiddenInputs() {
                // Convert to array format for backend processing
                const activeDaysArray = [...selectedDays].sort();

                // Create associative array maintaining day-price relationship
                const dayPricesObject = {};
                activeDaysArray.forEach(day => {
                    dayPricesObject[day] = prices[day] !== undefined && prices[day] !== null ? prices[day] :
                        null;
                });

                document.getElementById("activeDaysInput").value = JSON.stringify(activeDaysArray);
                document.getElementById("dayPricesInput").value = JSON.stringify(dayPricesObject);
            }

            // -------------------------------------------------
            // Form Validation
            // -------------------------------------------------
            function validateForm() {
                let isValid = true;

                // Check if at least one day is selected
                if (selectedDays.size === 0) {
                    alert('Please select at least one day for pricing');
                    isValid = false;
                }

                // Check if all selected days have valid prices
                let hasValidPrice = false;
                let emptyPriceDays = [];

                [...selectedDays].forEach(day => {
                    if (prices[day] !== null && prices[day] !== undefined && prices[day] > 0) {
                        hasValidPrice = true;
                    } else {
                        emptyPriceDays.push(day.toUpperCase());
                    }
                });

                if (!hasValidPrice) {
                    alert('Please set at least one valid price for the selected days');
                    isValid = false;
                } else if (emptyPriceDays.length > 0) {
                    // Warn about days without prices but allow submission
                    const confirmMessage =
                        `The following days don't have prices set: ${emptyPriceDays.join(', ')}. Do you want to continue?`;
                    if (!confirm(confirmMessage)) {
                        isValid = false;
                    }
                }

                // Validate required form fields
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');
                    }
                });

                return isValid;
            }

            // -------------------------------------------------
            // FINAL SUBMIT HANDLER
            // -------------------------------------------------
            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();

                if (!validateForm()) {
                    return;
                }

                // Update hidden inputs one final time
                updateHiddenInputs();

                console.log("Submitting active_days:", [...selectedDays]);
                console.log("Submitting day_prices:", prices);

                // Handle file uploads if uploader exists
                if (typeof uploader !== 'undefined') {
                    const selectedFiles = uploader.getSelectedFiles() || [];
                    const newFiles = selectedFiles.filter(f => f instanceof File);
                    const existingImages = selectedFiles.filter(f => f.isGalleryImage);

                    const dt = new DataTransfer();
                    newFiles.forEach(f => dt.items.add(f));
                    realFileInput.files = dt.files;

                    if (galleryInput) {
                        galleryInput.value = JSON.stringify(
                            existingImages.map(g => g.galleryId || g.id).filter(Boolean)
                        );
                    }
                }

                // Show loading state and submit
                submitBtn.disabled = true;
                submitBtn.classList.add("btn-loading");

                // Small timeout to ensure loading state is visible
                setTimeout(() => {
                    form.submit();
                }, 100);
            });

            // Initialize hidden inputs on page load
            updateHiddenInputs();

            // Real-time validation for required fields
            form.querySelectorAll('[required]').forEach(field => {
                field.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.classList.remove('is-invalid');
                    }
                });
            });
        });
    </script>
@endpush
