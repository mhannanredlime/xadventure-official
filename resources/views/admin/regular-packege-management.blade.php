@extends('layouts.admin')
@section('title', isset($package) ? 'Edit Regular Package' : 'Add Regular Package')
@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/gallery.css') }}">
    <style>
        /* ---------- General Styles ---------- */
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.1);
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
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
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #F76B19;
            box-shadow: 0 0 0 0.2rem rgba(247, 107, 25, 0.25);
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
            border-top: 2px solid #fff;
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

        /* ---------- Day Pricing Styles ---------- */
        .day-pill {
            margin: 0.25rem;
        }

        .day-pill .btn {
            min-width: 70px;
            text-transform: capitalize;
        }

        .day-pill .btn.active {
            background: #F76B19;
            color: #fff;
            border-color: #F76B19;
        }

        .price-input {
            max-width: 150px;
            margin-bottom: 0.5rem;
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
                    <div class="d-flex align-items-center"><i
                            class="bi {{ $msg == 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-2"></i>{{ session($msg) }}
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
            action="{{ isset($package) ? route('admin.regular-packege-management.update', $package->id ?? 1) : route('admin.regular-packege-management.store') }}"
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

                        <select class="form-select" name="packageType" required>
                            <option value="">Select Package Type</option>
                            @foreach ($packageTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('packageType', $package->package_type_id ?? '') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}</option>
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
                        <label class="form-label">Display Base Price (৳) <span class="text-danger">*</span> </label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01"
                                class="form-control @error('displayStartingPrice') is-invalid @enderror"
                                name="displayStartingPrice"
                                value="{{ old('displayStartingPrice', $package->display_starting_price ?? '') }}"
                                placeholder="0.00" min="50">
                        </div>
                        @error('displayStartingPrice')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Minimum Participants <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('minParticipant') is-invalid @enderror"
                            name="minParticipant" value="{{ old('minParticipant', $package->min_participants ?? "") }}"
                            min="1" required>
                        @error('minParticipant')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maximum Participants <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('maxParticipant') is-invalid @enderror"
                            name="maxParticipant" value="{{ old('maxParticipant', $package->max_participants ?? "") }}"
                            min="1" required>
                        @error('maxParticipant')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            @php
                // Ensure $selectedDays is always an array
                $selectedDays = isset($selectedDays) ? (array) json_decode(json_encode($selectedDays), true) : [];
                $dayPrices = isset($package->day_prices) ? (array) json_decode($package->day_prices, true) : [];
            @endphp

            {{-- ---------- Package Pricing (Day-wise) ---------- --}}
            <div class="card p-4">
                <h5 class="card-title"><i class="bi bi-tag me-2"></i>Package Pricing (Day-wise)</h5>
                <div class="mb-3">
                    <label class="fw-semibold mb-2">Select Active Days</label>
                    <div class="d-flex flex-wrap">
                        @foreach ($days as $day)
                            <div class="form-check form-check-inline day-pill">
                                <input type="checkbox" class="btn-check day-checkbox" id="day-{{ $day }}"
                                    value="{{ $day }}" autocomplete="off"
                                    {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                                <label class="btn  btn-sm jatio-color"
                                    for="day-{{ $day }}">{{ strtoupper($day) }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div id="priceContainer" class="row"></div>
                <div class="mt-3">
                    <label class="fw-semibold">Apply Same Price to Days</label>
                    <input type="number" class="form-control" id="applyAllPrice" placeholder="e.g. 1200">
                    <button type="button" class="btn btn-sm btn-dark mt-2" id="applyAllBtn">Apply</button>
                </div>
                <input type="hidden" name="active_days" id="activeDaysInput"
                    value="{{ old('active_days', json_encode($selectedDays)) }}">
                <input type="hidden" name="day_prices" id="dayPricesInput"
                    value="{{ old('day_prices', json_encode($dayPrices)) }}">
            </div>


            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-save" id="submitBtn"><i
                        class="bi bi-save me-2"></i>{{ isset($package) ? 'Update Package' : 'Save Package' }}</button>
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

            const container = document.getElementById('multiple-image-upload');
            const uploader = new MultipleImageUpload(container.id, {
                maxFiles: parseInt(container.dataset.maxFiles) || 4,
                maxFileSize: parseInt(container.dataset.maxFileSize) || 5 * 1024 * 1024
            });

            window.multipleImageUploadInstance = uploader;

            // -------------------------------------------------
            // Day-wise Pricing Variables
            // -------------------------------------------------
            let selectedDays = new Set({!! json_encode($selectedDays) !!});
            let prices = {!! json_encode($package->day_prices ?? []) !!};
            const priceContainer = $("#priceContainer");

            // -------------------------------------------------
            // Initialize prices for all selected days
            // -------------------------------------------------
            function initializePrices() {
                [...selectedDays].forEach(day => {
                    // If day doesn't exist in prices or price is empty, set to null
                    if (prices[day] === undefined || prices[day] === '') {
                        prices[day] = null;
                    }
                });
            }

            // Call initialization to ensure all selected days have prices
            initializePrices();

            // -------------------------------------------------
            // Render Price Inputs
            // -------------------------------------------------
            function renderPriceInputs() {
                priceContainer.empty();

                [...selectedDays].forEach(day => {
                    let isWeekend = ['fri', 'sat'].includes(day);
                    let weekendBadge = isWeekend ?
                        '<span class="badge bg-warning text-dark ms-2">Weekend</span>' :
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
                    >
                </div>
            `);
                });
            }

            renderPriceInputs();

            // -------------------------------------------------
            // Select / Unselect Day
            // -------------------------------------------------
            $(".day-checkbox").on("change", function() {
                const day = $(this).val();

                if (this.checked) {
                    selectedDays.add(day);
                    // Initialize price for newly selected day
                    if (prices[day] === undefined) {
                        prices[day] = null;
                    }
                } else {
                    selectedDays.delete(day);
                    delete prices[day];
                }

                renderPriceInputs();
            });

            // -------------------------------------------------
            // Update Individual Day Price
            // -------------------------------------------------
            $(document).on("input", ".day-price-input", function() {
                const day = $(this).data("day");
                const val = $(this).val();

                if (val !== "" && val !== null) {
                    prices[day] = Number(val);
                } else {
                    prices[day] = null;
                }
            });

            // -------------------------------------------------
            // Apply One Price to All Days
            // -------------------------------------------------
            $("#applyAllBtn").on("click", function() {
                const val = $("#applyAllPrice").val();

                [...selectedDays].forEach(day => {
                    if (val !== "" && val !== null) {
                        prices[day] = Number(val);
                    } else {
                        prices[day] = null;
                    }
                });

                renderPriceInputs();
            });

            // -------------------------------------------------
            // FINAL SUBMIT HANDLER (One and Only)
            // -------------------------------------------------
            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();

                submitBtn.disabled = true;
                submitBtn.classList.add("btn-loading");

                // Convert to array format for backend processing
                const dayPricesArray = [];
                [...selectedDays].forEach(day => {
                    const priceValue = prices[day] !== undefined && prices[day] !== null ? prices[
                        day] : null;
                    dayPricesArray.push(priceValue);
                });

                // Send hidden day values
                document.getElementById("activeDaysInput").value = JSON.stringify([...selectedDays]);
                document.getElementById("dayPricesInput").value = JSON.stringify(dayPricesArray);

                console.log("Submitting active_days:", [...selectedDays]);
                console.log("Submitting day_prices (array):", dayPricesArray);

                // Handle new and existing images
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

                form.submit();
            });

        });
    </script>
@endpush
