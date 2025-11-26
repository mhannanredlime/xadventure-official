@extends('layouts.admin')

@section('title', isset($package) ? 'Edit Regular Package' : 'Add Regular Package')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/gallery.css') }}">
    <style>
        /* ---------- General Improvements ---------- */
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.1);
            border-radius: 0.75rem;
        }

        .card-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #2c3e50;
            font-size: 1.25rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #F76B19;
            box-shadow: 0 0 0 0.2rem rgba(247, 107, 25, 0.25);
        }

        /* ---------- Image Uploader ---------- */
        .image-upload-section {
            background: #f8f9fa;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .badge.jatio-bg-color {
            background-color: #F76B19;
            color: #fff;
            font-weight: 600;
        }

        /* ---------- Pricing Card ---------- */
        .pricing-card {
            border: 1px solid #e9ecef;
            border-radius: 0.75rem;
            padding: 2rem;
            background: #ffffff;
            margin: 1.5rem 0;
        }

        .pricing-section-title {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        /* ---------- Pills Improvements ---------- */
        .pricing-pills {
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .pricing-pills .nav-link {
            border-radius: 0.6rem;
            border: 1px solid #e0e0e0;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            color: #495057;
            background: #ffffff;
            transition: all 0.2s ease;
            min-width: 100px;
            text-align: center;
        }

        .pricing-pills .nav-link.active {
            background-color: #F76B19 !important;
            border-color: #F76B19 !important;
            color: #fff !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(247, 107, 25, 0.3);
        }

        .pricing-pills .nav-link:hover:not(.active) {
            background-color: #f8f9fa;
            border-color: #F76B19;
            color: #F76B19;
            transform: translateY(-1px);
        }

        /* ---------- Price Input Styling ---------- */
        .price-input .input-group {
            max-width: 300px;
        }

        .price-input .input-group-text {
            font-weight: 600;
            background: #f8f9fa;
            border-color: #dee2e6;
            color: #495057;
        }

        .price-input .form-control {
            border-left: none;
        }

        .price-input .input-group:focus-within .input-group-text {
            border-color: #F76B19;
            background: #fffaf7;
        }

        /* ---------- Selected Day Display ---------- */
        .selected-day-display {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #F76B19;
        }

        .selected-day-display strong {
            color: #495057;
        }

        .selected-day-display span {
            color: #F76B19;
            font-weight: 600;
        }

        /* ---------- Professional Save Button ---------- */
        .btn-save {
            background: linear-gradient(135deg, #F76B19 0%, #e55e14 100%);
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 0.75rem;
            padding: 1rem 2.5rem;
            border: none;
            box-shadow: 0 6px 20px rgba(247, 107, 25, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(247, 107, 25, 0.4);
            background: linear-gradient(135deg, #e55e14 0%, #d95f17 100%);
        }

        .btn-save:active {
            transform: translateY(-1px);
        }

        .btn-save.btn-loading {
            pointer-events: none;
            color: transparent;
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
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: button-spinner 0.8s linear infinite;
        }

        @keyframes button-spinner {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* ---------- Alert Improvements ---------- */
        .alert {
            border: none;
            border-radius: 0.75rem;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d1f2eb;
            color: #0d5d47;
            border-left: 4px solid #0d5d47;
        }

        .alert-danger {
            background: #fde8e8;
            color: #c53030;
            border-left: 4px solid #c53030;
        }

        /* ---------- Page Header ---------- */
        .page-header h1 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .breadcrumb-custom {
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        /* ---------- Responsive Design ---------- */
        @media (max-width: 768px) {
            .pricing-card {
                padding: 1.5rem;
            }

            .pricing-pills .nav-link {
                min-width: 80px;
                padding: 0.6rem 1rem;
                font-size: 0.875rem;
            }

            .btn-save {
                width: 100%;
                padding: 0.875rem 2rem;
            }

            .price-input .input-group {
                max-width: 100%;
            }
        }

        @media (max-width: 576px) {
            .card-body {
                padding: 1.25rem;
            }

            .pricing-pills {
                justify-content: center;
            }

            .pricing-pills .nav-link {
                min-width: 70px;
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }
        }

        /* ---------- Validation States ---------- */
        .is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .invalid-feedback {
            font-weight: 500;
            margin-top: 0.25rem;
        }

        .text-muted {
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: block;
        }

        /* ---------- Section Dividers ---------- */
        .section-divider {
            border-top: 2px solid #e9ecef;
            margin: 2rem 0;
        }
    </style>
@endpush

@section('content')
    <main class="mt-4">
        <header class="d-flex justify-content-between align-items-center page-header mb-4">
            <div>
                <h1>{{ isset($package) ? 'Edit Regular Package' : 'Add Regular Package' }}</h1>
                <p class="breadcrumb-custom">
                    <i class="bi bi-home me-1"></i> Package Management >
                    {{ isset($package) ? 'Edit Regular Package' : 'Add Regular Package' }}
                </p>
            </div>
            <a href="" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Packages
            </a>
        </header>

        {{-- Alert Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle me-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <div>
                        <strong>Please correct the following errors:</strong>
                        <ul class="mb-0 mt-2 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body p-4">
                <form id="packageForm" method="POST"
                    action="{{ isset($package) ? route('admin.regular-packege-management.update', $package) : route('admin.regular-packege-management.store') }}"
                    enctype="multipart/form-data" novalidate>
                    @csrf
                    @if (isset($package))
                        @method('PUT')
                    @endif

                    {{-- Image Upload Section --}}
                    <div class="image-upload-section">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-images me-2"></i>Package Images
                        </h5>
                        <div class="row">
                            <div class="col-12">
                                <label for="multiple-image-upload" class="form-label">Upload Package Images (Max 4
                                    images)</label>

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
                                    data-max-files="12" data-max-file-size="{{ 5 * 1024 * 1024 }}">
                                </div>

                                <input type="file" id="package_images_input" name="images[]" multiple accept="image/*"
                                    style="display:none;">
                                <input type="hidden" id="gallery_images_input" name="gallery_images" value="">

                                <small class="text-muted mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Supported formats: JPG, PNG, WebP. Max file size: 5MB. First image will be used as main
                                    display.
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Package Details Section --}}
                    <h5 class="card-title">
                        <i class="bi bi-info-circle me-2"></i>Package Details
                    </h5>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label for="packageName" class="form-label">Package Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('packageName') is-invalid @enderror"
                                id="packageName" name="packageName" value="{{ old('packageName', $package->name ?? '') }}"
                                placeholder="Enter package name" required>
                            @error('packageName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="subTitle" class="form-label">Sub Title</label>
                            <input type="text" class="form-control @error('subTitle') is-invalid @enderror"
                                id="subTitle" name="subTitle" value="{{ old('subTitle', $package->subtitle ?? '') }}"
                                placeholder="Enter package subtitle">
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
                                    {{ old('packageType', optional($package?->variants->first())->variant_name) == 'Single' ? 'selected' : '' }}>
                                    Single Package
                                </option>
                                <option value="Bundle"
                                    {{ old('packageType', optional($package?->variants->first())->variant_name) == 'Bundle' ? 'selected' : '' }}>
                                    Bundle Package
                                </option>
                                <option value="Group"
                                    {{ old('packageType', optional($package?->variants->first())->variant_name) == 'Group' ? 'selected' : '' }}>
                                    Group Package
                                </option>
                            </select>
                            @error('packageType')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="details" class="form-label">Package Details</label>
                            <textarea class="form-control @error('details') is-invalid @enderror" id="details" name="details" rows="5"
                                placeholder="Describe the package details, features, and inclusions...">{{ old('details', $package->details ?? '') }}</textarea>
                            @error('details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="displayStartingPrice" class="form-label">Display Starting Price (৳)</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01"
                                    class="form-control @error('displayStartingPrice') is-invalid @enderror"
                                    id="displayStartingPrice" name="displayStartingPrice"
                                    value="{{ old('displayStartingPrice', $package->display_starting_price ?? '') }}"
                                    placeholder="0.00" min="0">
                            </div>

                            @error('displayStartingPrice')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="minParticipant" class="form-label">Minimum Participants <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('minParticipant') is-invalid @enderror"
                                id="minParticipant" name="minParticipant"
                                value="{{ old('minParticipant', $package->min_participants ?? 5) }}" min="1"
                                required>
                            @error('minParticipant')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="maxParticipant" class="form-label">Maximum Participants <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('maxParticipant') is-invalid @enderror"
                                id="maxParticipant" name="maxParticipant"
                                value="{{ old('maxParticipant', $package->max_participants ?? 50) }}" min="1"
                                required>
                            @error('maxParticipant')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Pricing Section --}}
                    <div class="pricing-card">
                        <h5 class="pricing-section-title">
                            <i class="bi bi-tag me-2"></i>Pricing Configuration
                        </h5>

                        <div class="row g-4">
                            {{-- Weekday Pricing --}}
                            <div class="col-lg-6">
                                <div class="pricing-day-section">
                                    <label class="fw-semibold mb-3">Weekday Prices</label>

                                    <ul class="nav nav-pills pricing-pills mb-4" id="weekdayPills" role="tablist">
                                        @foreach (['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'] as $day)
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link {{ $day == 'monday' ? 'active' : '' }}"
                                                    data-day="{{ $day }}"
                                                    data-price="{{ old('weekday_price_' . $day, '') }}" type="button">
                                                    {{ ucfirst($day) }}
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>

                                    <div class="selected-day-display">
                                        <strong>Selected Weekday:</strong>
                                        <span id="selectedWeekday">Monday</span>
                                    </div>

                                    <div class="price-input">
                                        <label class="form-label">Price (৳) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" step="0.01" id="weekdayPrice" name="weekdayPrice"
                                                class="form-control" value="{{ old('weekdayPrice') }}"
                                                placeholder="0.00" min="0" required>
                                        </div>
                                        <small class="text-muted">Price for the selected weekday</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Weekend Pricing --}}
                            <div class="col-lg-6">
                                <div class="pricing-day-section">
                                    <label class="fw-semibold mb-3">Weekend Prices</label>

                                    <ul class="nav nav-pills pricing-pills mb-4" id="weekendPills" role="tablist">
                                        @foreach (['friday', 'saturday'] as $day)
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link {{ $day == 'friday' ? 'active' : '' }}"
                                                    data-day="{{ $day }}"
                                                    data-price="{{ old('weekend_price_' . $day, '') }}" type="button">
                                                    {{ ucfirst($day) }}
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>

                                    <div class="selected-day-display">
                                        <strong>Selected Weekend:</strong>
                                        <span id="selectedWeekend">Friday</span>
                                    </div>

                                    <div class="price-input">
                                        <label class="form-label">Price (৳) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" step="0.01" id="weekendPrice" name="weekendPrice"
                                                class="form-control" value="{{ old('weekendPrice') }}"
                                                placeholder="0.00" min="0" required>
                                        </div>
                                        <small class="text-muted">Price for the selected weekend day</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden Fields --}}
                    <input type="hidden" id="selected_weekday" name="selected_weekday"
                        value="{{ old('selected_weekday', 'monday') }}">
                    <input type="hidden" id="selected_weekend" name="selected_weekend"
                        value="{{ old('selected_weekend', 'friday') }}">

                    <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                        <a href="{{ url('admin/add-packege-management') }}" class="btn btn-outline-danger btn-lg px-4">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                        <button id="submitBtn" type="button" class="btn  jatio-bg-color btn-lg px-5">
                            <i class="bi bi-check-lg me-2"></i>
                            {{ isset($package) ? 'Update Package' : 'Save Package' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/multiple-image-upload.js') }}"></script>
    <script src="{{ asset('admin/js/gallery-manager.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize uploader instance for the container
            const container = document.getElementById('multiple-image-upload');
            if (!container) return;

            const uploader = new MultipleImageUpload(container.id, {
                maxFiles: parseInt(container.dataset.maxFiles || container.dataset.maxFiles) || 12,
                maxFileSize: parseInt(container.dataset.maxFileSize || (5 * 1024 * 1024)) || (5 * 1024 *
                    1024),
            });

            // keep a global reference
            window.multipleImageUploadInstance = uploader;

            // Pricing pills logic (single source of truth)
            const weekdayPills = document.querySelectorAll('#weekdayPills .nav-link');
            const weekendPills = document.querySelectorAll('#weekendPills .nav-link');
            const selectedWeekdayLabel = document.getElementById('selectedWeekday');
            const selectedWeekendLabel = document.getElementById('selectedWeekend');
            const selectedWeekdayInput = document.getElementById('selected_weekday');
            const selectedWeekendInput = document.getElementById('selected_weekend');
            const weekdayPriceInput = document.getElementById('weekdayPrice');
            const weekendPriceInput = document.getElementById('weekendPrice');

            // helper to set active pill group
            function activatePillGroup(pills, clicked) {
                pills.forEach(p => p.classList.remove('active'));
                clicked.classList.add('active');
            }

            // initialize pills — make default active (use dataset or fallback)
            (function initPills() {
                const prevWeekday = "{{ old('selected_weekday', 'monday') }}";
                const prevWeekend = "{{ old('selected_weekend', 'friday') }}";

                // weekday
                let found = false;
                weekdayPills.forEach(p => {
                    if (p.dataset.day === prevWeekday) {
                        activatePillGroup(weekdayPills, p);
                        selectedWeekdayLabel.textContent = p.textContent.trim();
                        selectedWeekdayInput.value = p.dataset.day;
                        weekdayPriceInput.value = p.dataset.price || weekdayPriceInput.value;
                        found = true;
                    }
                });
                if (!found && weekdayPills.length) {
                    activatePillGroup(weekdayPills, weekdayPills[0]);
                    selectedWeekdayLabel.textContent = weekdayPills[0].textContent.trim();
                    selectedWeekdayInput.value = weekdayPills[0].dataset.day;
                    weekdayPriceInput.value = weekdayPills[0].dataset.price || weekdayPriceInput.value;
                }

                // weekend
                found = false;
                weekendPills.forEach(p => {
                    if (p.dataset.day === prevWeekend) {
                        activatePillGroup(weekendPills, p);
                        selectedWeekendLabel.textContent = p.textContent.trim();
                        selectedWeekendInput.value = p.dataset.day;
                        weekendPriceInput.value = p.dataset.price || weekendPriceInput.value;
                        found = true;
                    }
                });
                if (!found && weekendPills.length) {
                    activatePillGroup(weekendPills, weekendPills[0]);
                    selectedWeekendLabel.textContent = weekendPills[0].textContent.trim();
                    selectedWeekendInput.value = weekendPills[0].dataset.day;
                    weekendPriceInput.value = weekendPills[0].dataset.price || weekendPriceInput.value;
                }
            })();

            // click handlers
            weekdayPills.forEach(pill => {
                pill.addEventListener('click', function(e) {
                    e.preventDefault();
                    activatePillGroup(weekdayPills, this);
                    selectedWeekdayLabel.textContent = this.textContent.trim();
                    selectedWeekdayInput.value = this.dataset.day;
                    if (this.dataset.price) weekdayPriceInput.value = this.dataset.price;
                });
            });

            weekendPills.forEach(pill => {
                pill.addEventListener('click', function(e) {
                    e.preventDefault();
                    activatePillGroup(weekendPills, this);
                    selectedWeekendLabel.textContent = this.textContent.trim();
                    selectedWeekendInput.value = this.dataset.day;
                    if (this.dataset.price) weekendPriceInput.value = this.dataset.price;
                });
            });

            // sync price inputs to currently active pill's price dataset (live update)
            weekdayPriceInput.addEventListener('input', function() {
                const active = document.querySelector('#weekdayPills .nav-link.active');
                if (active) active.dataset.price = this.value;
            });
            weekendPriceInput.addEventListener('input', function() {
                const active = document.querySelector('#weekendPills .nav-link.active');
                if (active) active.dataset.price = this.value;
            });

            // form submit: attach selected files from uploader into real input
            const form = document.getElementById('packageForm');
            const submitBtn = document.getElementById('submitBtn');
            const realFileInput = document.getElementById('package_images_input');
            const galleryInput = document.getElementById('gallery_images_input');

            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();

                // basic client validation
                if (!validateFormClient()) return;

                // Gather files from uploader instance
                let selectedFiles = [];
                if (window.multipleImageUploadInstance && typeof window.multipleImageUploadInstance
                    .getSelectedFiles === 'function') {
                    selectedFiles = window.multipleImageUploadInstance.getSelectedFiles() || [];
                }

                console.log('Selected files:', selectedFiles);

                // Separate real File objects and gallery images
                const realFiles = selectedFiles.filter(f => f instanceof File && !f.isGalleryImage);
                const galleryImages = selectedFiles.filter(f => f.isGalleryImage);

                console.log('Real files to upload:', realFiles);
                console.log('Gallery images:', galleryImages);

                // Attach real files to the hidden file input using DataTransfer
                if (realFiles.length > 0) {
                    const dt = new DataTransfer();
                    realFiles.forEach(f => {
                        console.log('Adding file to DataTransfer:', f.name, f.type, f.size);
                        dt.items.add(f);
                    });
                    realFileInput.files = dt.files;
                    console.log('File input now has files:', realFileInput.files.length);
                } else {
                    // Don't clear the input value, just set empty files
                    realFileInput.files = new DataTransfer().files;
                    console.log('No real files to upload');
                }

                // Attach gallery image IDs as JSON string
                if (galleryImages.length > 0) {
                    const ids = galleryImages.map(g => g.galleryId || g.id).filter(Boolean);
                    galleryInput.value = JSON.stringify(ids);
                    console.log('Gallery image IDs:', galleryInput.value);
                } else {
                    galleryInput.value = '';
                }

                // Create a FormData object to see what's being sent
                const formData = new FormData(form);
                console.log('FormData contents:');
                for (let [key, value] of formData.entries()) {
                    console.log(key + ': ' + (value instanceof File ? value.name + ' (' + value.type +
                        ', ' + value.size + ' bytes)' : value));
                }

                // Finally submit the form
                console.log('Submitting form...');
                form.submit();
            });

            // simple client-side validation (expand as needed)
            function validateFormClient() {
                const required = document.querySelectorAll('#packageForm [required]');
                for (let el of required) {
                    if (!el.value || el.value.toString().trim() === '') {
                        el.classList.add('is-invalid');
                        el.focus();
                        return false;
                    } else {
                        el.classList.remove('is-invalid');
                    }
                }

                // prices validation
                if (parseFloat(weekdayPriceInput.value) < 0 || parseFloat(weekendPriceInput.value) < 0) {
                    alert('Prices must be >= 0');
                    return false;
                }

                return true;
            }
        });
    </script>
@endpush
