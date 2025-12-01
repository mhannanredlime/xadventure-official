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
                <h3>{{ isset($package) ? 'Edit ATV UTV Package' : 'Add ATV UTV Package' }}</h3>
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
            action="{{ isset($package) ? route('admin.atvutv-packege-management.update', $package->id) : route('admin.atvutv-packege-management.store') }}"
            enctype="multipart/form-data" novalidate>
            @csrf
            @if (isset($package))
                @method('PUT')
            @endif

            {{-- ---------- Package Details ---------- --}}
            <div class="card p-4">
                <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>Package Details</h5>
                <div class="row g-4">

                    <div class="col-md-6">
                        <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="vehicleType" required>
                            <option value="">Select Vehicle Type</option>
                            @foreach ($vehicleTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('vehicleType', $package->package_type_id ?? '') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('packageType')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

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



                    <div class="col-12">
                        <label class="form-label">Package Details</label>
                        <textarea class="form-control @error('details') is-invalid @enderror" name="details" rows="5"
                            placeholder="Describe the package details...">{{ old('details', $package->details ?? '') }}</textarea>
                        @error('details')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Rider Quantity <span class="text-danger">*</span></label>
                        <input type="number" min="1"
                            class="form-control @error('rider_quantity') is-invalid @enderror" name="rider_quantity"
                            value="{{ old('rider_quantity', $package->min_participants ?? '') }}" min="1" required>
                        @error('rider_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            @php
                // পরিষ্কার array তৈরি করুন
                $selectedDays = isset($selectedDays) ? (array) json_decode(json_encode($selectedDays), true) : [];

                // দিন অনুসারে price array তৈরি করুন
                $dayPriceArray = [];
                if (isset($package->day_prices)) {
                    $prices = json_decode($package->day_prices, true);
                    foreach ($selectedDays as $index => $day) {
                        $dayPriceArray[$day] = $prices[$day] ?? null;
                    }
                }
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
                <div class="mt-4">
                    <label class="fw-semibold">Apply Same Price to Days</label>
                    <input type="number" class="form-control" id="applyAllPrice" placeholder="e.g. 1200">
                    <button type="button" class="btn btn-sm btn-dark mt-2" id="applyAllBtn">Apply to all days</button>
                </div>
                <input type="hidden" name="active_days" id="activeDaysInput"
                    value="{{ old('active_days', json_encode($selectedDays)) }}">
                <input type="hidden" name="day_prices" id="dayPricesInput"
                    value="{{ old('day_prices', json_encode($dayPriceArray)) }}">
            </div>
            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-save" id="submitBtn"><i
                        class="bi bi-save me-2"></i>{{ isset($package) ? 'Update Package' : 'Save Package' }}</button>
            </div>
        </form>
    </main>
@endsection


@push('scripts')

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
                console.log("Getting active_days:", [...selectedDays]);
                console.log("Getting day_prices (array):", dayPricesArray);
                // return;
                form.submit();
            });

        });
    </script>
@endpush
