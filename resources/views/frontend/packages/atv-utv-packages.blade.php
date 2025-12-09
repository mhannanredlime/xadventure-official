@extends('layouts.frontend')

@section('title', 'ATV/UTV Trail Rides - Packages')

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontEnd/css/packege.css') }}">
    <style>
        /* Price Tags */
        .price-tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-right: 8px;
        }

        .price-tag.premium {
            background-color: #8b5cf6;
            color: white;
        }

        .price-tag.discounted {
            background-color: #10b981;
            color: white;
        }

        /* Capacity Info */
        .capacity-info {
            font-size: 0.7rem;
            color: #6b7280;
            text-align: center;
            margin-top: 4px;
            font-weight: 500;
        }

        /* Pricing */
        .price {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .price .original {
            text-decoration: line-through;
            color: #6b7280;
            font-size: 0.9rem;
        }

        /* Time Slots */
        .time-slots-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin-top: 20px;
        }

        .time-slot {
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .time-slot:hover {
            border-color: #f59e0b;
            background-color: #fef3c7;
        }

        .time-slot.selected {
            border-color: #f59e0b;
            background-color: #f59e0b;
            color: white;
        }

        @media (max-width: 768px) {
            .time-slots-container {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 8px;
            }

            .time-slot {
                padding: 10px 12px;
                font-size: 0.8rem;
            }
        }

        /* Quantity Buttons */
        .quantity-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #e9ecef;
        }

        /* Loading State */
        .packages-loading {
            opacity: 0.6;
            pointer-events: none;
            transition: opacity 0.3s ease;
            position: relative;
        }

        /* Date Picker Toggle */
        .date-picker-toggle-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border: 2px solid #ff6f00;
            background-color: white;
            color: #ff6f00;
            font-weight: 500;
            font-size: 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .date-picker-toggle-btn:hover {
            background-color: #ff6f00;
            color: white;
            border-color: #ff6f00;
        }

        .date-picker-toggle-btn .toggle-icon {
            transition: transform 0.3s ease;
        }

        .date-picker-toggle-btn.active .toggle-icon {
            transform: rotate(180deg);
        }

        #calendarContainer {
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Modal Styles */
        .modal-acknowledgment {
            max-width: 90%;
            width: 1200px;
        }

        .modal-header-primary {
            background-color: #2c5aa0;
            color: white;
        }

        .acknowledgment-item {
            background-color: #f8f9fa;
        }

        .acknowledgment-checkbox {
            transform: scale(1.2);
        }

        .acknowledgment-label {
            cursor: pointer;
        }

        .text-danger-underline {
            color: #dc3545;
            text-decoration: underline;
        }

        .text-primary-underline {
            color: #007bff;
            text-decoration: underline;
        }

        /* Signature Canvas */
        #signatureCanvas {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            cursor: crosshair;
        }

        .signature-upload-input {
            max-width: 400px;
            margin: 0 auto;
        }

        #signaturePreviewImg {
            max-width: 400px;
            max-height: 200px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
        }

        /* Success Modal Cards */
        .success-card {
            border-radius: 1rem;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .success-card:hover {
            transform: scale(1.05);
        }

        .success-card-img {
            filter: brightness(60%);
        }

        /* Buttons */
        .btn-pay-now {
            min-width: 200px;
        }

        .btn-view-cart {
            background-color: #ff6f00;
            color: #fff;
        }

        .btn-view-cart:hover {
            background-color: #e65a00;
            color: #fff;
        }

        /* Summary Box */
        .total-vehicles {
            font-size: 1rem;
        }

        .summary-item-box {
            background-color: #f5f5f5;
            padding: 7% 2%;
            border-radius: 5%;
        }

        .summary-item-box.double {
            padding: 9% 2%;
        }

        /* Alpine.js cloak */
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="container main-container">
        <h1>ATV/UTV Trail Rides</h1>

        <div class="accordion" id="sessionAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne"
                        aria-expanded="true" aria-controls="collapseOne">
                        Session 1
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
                    data-bs-parent="#sessionAccordion">
                    <div class="accordion-body">

                        <div class="mb-5">
                            <h3 class="section-heading">Choose Date</h3>
                            <button type="button" id="datePickerToggle"
                                class="btn btn-outline-primary date-picker-toggle-btn w-100 mb-3">
                                <i class="bi bi-calendar3 me-2"></i>
                                <span id="datePickerText">Choose Date</span>
                                <i class="bi bi-chevron-down ms-auto toggle-icon"></i>
                            </button>
                            <div class="calendar-container" id="calendarContainer" x-show="calendarOpen" x-cloak>
                                <div class="calendar">
                                    <div class="calendar-header">
                                        <i class="bi bi-chevron-left nav-arrow"></i>
                                        <span class="month">August 2025</span>
                                    </div>
                                    <div class="calendar-grid">
                                        <span class="day-name">Su</span>
                                        <span class="day-name">Mo</span>
                                        <span class="day-name">Tu</span>
                                        <span class="day-name">We</span>
                                        <span class="day-name">Th</span>
                                        <span class="day-name">Fr</span>
                                        <span class="day-name">Sa</span>
                                        <!-- Days will be populated by JavaScript -->
                                    </div>
                                </div>
                                <div class="calendar">
                                    <div class="calendar-header">
                                        <span class="month">September 2025</span>
                                        <i class="bi bi-chevron-right nav-arrow"></i>
                                    </div>
                                    <div class="calendar-grid">
                                        <span class="day-name">Su</span>
                                        <span class="day-name">Mo</span>
                                        <span class="day-name">Tu</span>
                                        <span class="day-name">We</span>
                                        <span class="day-name">Th</span>
                                        <span class="day-name">Fr</span>
                                        <span class="day-name">Sa</span>
                                        <!-- Days will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h3 class="section-heading">Choose Time Slot</h3>
                            <div class="time-slots-container" id="dynamic-time-slots">
                                @foreach ($scheduleSlots as $slot)
                                    {{-- @dd($slot); --}}
                                    <div class="time-slot" data-slot-id="{{ $slot->id }}">
                                        {{ $slot->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h3 class="section-heading">Select Vehicle</h3>
                            <div class="row g-4">
                                @foreach ($packages as $index => $package)
                                    @php
                                        $totalAvailableVehicles = 0;
                                        $vehicleTypes = $package->vehicleTypes;
                                        foreach ($vehicleTypes as $vehicleType) {
                                            $today = \Carbon\Carbon::today()->format('Y-m-d');
                                            $vehicleCount = \App\Models\Vehicle::where(
                                                'vehicle_type_id',
                                                $vehicleType->id,
                                            )
                                                ->where('is_active', true)
                                                ->where(function ($query) use ($today) {
                                                    $query
                                                        ->whereNull('op_start_date')
                                                        ->orWhere('op_start_date', '<=', $today);
                                                })
                                                ->count();
                                            $totalAvailableVehicles += $vehicleCount;
                                        }

                                        // Check if this is ATV/UTV package using Blade
                                        $isATVUTV =
                                            str_contains($package->name, 'ATV') || str_contains($package->name, 'UTV');

                                        $today = \Carbon\Carbon::today();
                                        $dayName = $today->isWeekend() ? 'weekend' : 'weekday';

                                        // Single rider price (rider_type_id = 1)
                                        $singlePrice = get_package_price($package, $dayName, 1);

                                        // Double rider price (rider_type_id = 2) - only for ATV/UTV
                                        $doublePrice = $isATVUTV ? get_package_price($package, $dayName, 2) : 0;

                                        // Get display starting price if available
                                        $displayPrice = $package->display_starting_price ?? 0;
                                        $effectivePrice =
                                            $displayPrice > 0
                                                ? $displayPrice
                                                : ($isATVUTV
                                                    ? min($singlePrice, $doublePrice)
                                                    : $singlePrice);

                                        // Store price data in data attributes
                                        $priceData = [
                                            'single' => $singlePrice,
                                            'double' => $doublePrice,
                                            'display' => $displayPrice,
                                            'day' => $dayName,
                                        ];
                                    @endphp
                                    <div class="col-lg-6">
                                        <div class="vehicle-card" data-vehicle="{{ strtolower($package->name) }}"
                                            data-package="{{ strtolower($package->name) }}"
                                            data-package-id="{{ $package->id }}"
                                            data-is-atv-utv="{{ $isATVUTV ? '1' : '0' }}"
                                            data-available-vehicles="{{ $totalAvailableVehicles }}"
                                            data-price-data="{{ json_encode($priceData) }}"
                                            data-base-url="{{ url('/') }}">
                                            <div class="vehicle-header">
                                                <div class="vehicle-info">
                                                    <div class="number"
                                                        data-original-count="{{ $totalAvailableVehicles }}">
                                                        {{ $totalAvailableVehicles }}</div>
                                                    <div class="type">{{ $package->name }}</div>
                                                    <div
                                                        class="availability {{ $totalAvailableVehicles > 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $totalAvailableVehicles > 0 ? 'Available' : 'No vehicles available' }}
                                                    </div>
                                                    <div class="">
                                                        {{ $package->description ?? 'Max 2 Person Can Ride This' }}</div>
                                                    <br>
                                                    <p class="vehicle-license-note">
                                                        {{ $package->requirements ?? '* Motorcycle license required' }}</p>
                                                </div>
                                                <img src="{{ $package->display_image_url }}" alt="{{ $package->name }}"
                                                    class="vehicle-image">
                                            </div>
                                            <div class="rider-options">
                                                @if ($isATVUTV)
                                                    <!-- Single Rider Option -->
                                                    <div class="rider-option-card" data-rider-type-id="1"
                                                        data-price="{{ $displayPrice > 0 ? $displayPrice : $singlePrice }}"
                                                        data-original-price="{{ $singlePrice }}">
                                                        <span class="rider-icon">
                                                            <img src="{{ asset('frontEnd/images/Helmet.svg') }}"
                                                                height="20px" alt="">
                                                        </span>
                                                        <div class="rider-title">Single Rider</div>
                                                        <div class="rider-desc">1 person can ride this</div>
                                                        <div class="price">
                                                            @if ($displayPrice > 0 && $displayPrice < $singlePrice)
                                                                <span class="original">TK
                                                                    {{ number_format($singlePrice) }}</span>
                                                            @endif
                                                            <span class="current-price">TK
                                                                {{ number_format($displayPrice > 0 ? $displayPrice : $singlePrice) }}</span>
                                                        </div>
                                                        <div class="quantity-selector">
                                                            <button class="quantity-btn" data-type="single"
                                                                data-action="decrement">-</button>
                                                            <span class="quantity-value"
                                                                id="{{ strtolower($package->name) }}-single-qty">0</span>
                                                            <button class="quantity-btn" data-type="single"
                                                                data-action="increment">+</button>
                                                            <div class="capacity-info">Max: {{ $totalAvailableVehicles }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Double Rider Option -->
                                                    <div class="rider-option-card" data-rider-type-id="2"
                                                        data-price="{{ $displayPrice > 0 ? $displayPrice : $doublePrice }}"
                                                        data-original-price="{{ $doublePrice }}">
                                                        <span class="rider-icon">
                                                            <img src="{{ asset('frontEnd/images/Helmet.svg') }}"
                                                                height="20px" alt="">
                                                            <img src="{{ asset('frontEnd/images/Helmet.svg') }}"
                                                                height="20px" alt="">
                                                        </span>
                                                        <div class="rider-title">Double Rider</div>
                                                        <div class="rider-desc">2 person can ride this</div>
                                                        <div class="price">
                                                            @if ($displayPrice > 0 && $displayPrice < $doublePrice)
                                                                <span class="original">TK
                                                                    {{ number_format($doublePrice) }}</span>
                                                            @endif
                                                            <span class="current-price">TK
                                                                {{ number_format($displayPrice > 0 ? $displayPrice : $doublePrice) }}</span>
                                                        </div>
                                                        <div class="quantity-selector">
                                                            <button class="quantity-btn" data-type="double"
                                                                data-action="decrement">-</button>
                                                            <span class="quantity-value"
                                                                id="{{ strtolower($package->name) }}-double-qty">0</span>
                                                            <button class="quantity-btn" data-type="double"
                                                                data-action="increment">+</button>
                                                            <div class="capacity-info">Max: {{ $totalAvailableVehicles }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <!-- Regular Package (Non-ATV/UTV) -->
                                                    <div class="rider-option-card" data-rider-type-id="0"
                                                        data-price="{{ $displayPrice > 0 ? $displayPrice : $singlePrice }}"
                                                        data-original-price="{{ $singlePrice }}">
                                                        <span class="rider-icon">
                                                            <img src="{{ asset('frontEnd/images/Helmet.svg') }}"
                                                                height="20px" alt="">
                                                        </span>
                                                        <div class="rider-title">{{ $package->name }} Standard</div>
                                                        <div class="rider-desc">1 person can ride this</div>
                                                        <div class="price">
                                                            @if ($displayPrice > 0 && $displayPrice < $singlePrice)
                                                                <span class="original">TK
                                                                    {{ number_format($singlePrice) }}</span>
                                                            @endif
                                                            <span class="current-price">TK
                                                                {{ number_format($displayPrice > 0 ? $displayPrice : $singlePrice) }}</span>
                                                        </div>
                                                        <div class="quantity-selector">
                                                            <button class="quantity-btn" data-type="single"
                                                                data-action="decrement">-</button>
                                                            <span class="quantity-value"
                                                                id="{{ strtolower($package->name) }}-single-qty">0</span>
                                                            <button class="quantity-btn" data-type="single"
                                                                data-action="increment">+</button>
                                                            <div class="capacity-info">Max: {{ $totalAvailableVehicles }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="summary-box-wrapper">
                                                <div class="row">
                                                    <div class="col-sm-7 col-md-8"
                                                        id="{{ strtolower($package->name) }}-summary-list"></div>
                                                    <div class="col-sm-5 col-md-4">
                                                        <div class="summary-total-box">
                                                            <div class="total-label">TOTAL</div>
                                                            <div class="total-vehicles"
                                                                id="{{ strtolower($package->name) }}-total-vehicles">0
                                                            </div>
                                                            <div class="total-price"
                                                                id="{{ strtolower($package->name) }}-total-price">TK 0
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <button type="button" class="btn btn-add-to-cart w-25 btn-pay-now"
                            onclick="addToCartAndShowModal()">
                            Pay Now
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ATV/UTV Acknowledgment Modal -->
    <div class="modal fade" id="acknowledgmentModal" tabindex="-1" aria-labelledby="acknowledgmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-acknowledgment">
            <div class="modal-content bg-white">
                <div class="modal-header modal-header-primary">
                    <h5 class="modal-title" id="acknowledgmentModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Booking Acknowledgment - ATV/UTV Trail Rides
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info">
                        <strong>By booking this reservation I acknowledge below and all rules and regulations of the <span
                                class="text-danger-underline">xadventure</span></strong>
                    </div>

                    <!-- Acknowledgment Checkboxes -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="acknowledgment-item p-3 border rounded">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <input type="checkbox" class="form-check-input acknowledgment-checkbox"
                                            id="acknowledgment1">
                                    </div>
                                    <div class="flex-grow-1">
                                        <label for="acknowledgment1" class="form-label mb-0 acknowledgment-label">
                                            I understand that for Each vehicle reservation, we will have at least one person
                                            have <span class="text-primary-underline">Motorcycle</span> or
                                            Car Driver's licenses
                                        </label>
                                    </div>
                                </div>
                                <div class="text-end mt-2">
                                    <span class="badge bg-primary">I Acknowledge</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="acknowledgment-item p-3 border rounded">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <input type="checkbox" class="form-check-input acknowledgment-checkbox"
                                            id="acknowledgment2">
                                    </div>
                                    <div class="flex-grow-1">
                                        <label for="acknowledgment2" class="form-label mb-0 acknowledgment-label">
                                            I understand I have to <span class="text-danger-underline">carry</span> show my
                                            driver's license before the ride start. Failure the show my physical license,
                                            <span class="text-danger-underline">Xadventure</span>
                                            will Deny the Ride, and I will not get my money back.
                                        </label>
                                    </div>
                                </div>
                                <div class="text-end mt-2">
                                    <span class="badge bg-primary">I Acknowledge</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Signature Section -->
                    <div class="signature-section">
                        <h6 class="mb-3"><i class="bi  bi-signature me-2"></i>Digital Signature Required</h6>

                        <!-- Signature Tabs -->
                        <ul class="nav nav-tabs mb-3" id="signatureTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="draw-tab" data-bs-toggle="tab"
                                    data-bs-target="#draw-pane" type="button" role="tab">
                                    <i class="bi  bi-pen me-1"></i> Draw Signature
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="upload-tab" data-bs-toggle="tab"
                                    data-bs-target="#upload-pane" type="button" role="tab">
                                    <i class="bi  bi-upload me-1"></i> Upload Signature
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="signatureTabContent">
                            <!-- Draw Signature Tab -->
                            <div class="tab-pane fade show active" id="draw-pane" role="tabpanel">
                                <div class="signature-canvas-container text-center">
                                    <canvas id="signatureCanvas" width="500" height="200"></canvas>
                                    <div class="mt-2">
                                        <small class="text-muted">Draw your signature above</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Signature Tab -->
                            <div class="tab-pane fade" id="upload-pane" role="tabpanel">
                                <div class="signature-upload-container text-center">
                                    <input type="file" id="signatureUpload" accept="image/png,image/jpeg,image/jpg"
                                        class="form-control mb-3 signature-upload-input">
                                    <div id="signaturePreview" class="mt-3" x-show="signaturePreviewVisible" x-cloak>
                                        <img id="signaturePreviewImg">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Signature Actions -->
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="clearSignature()">
                                <i class="bi  bi-eraser me-1"></i> Clear Signature
                            </button>
                            <div id="signatureStatus" class="mt-2">
                                <small class="text-muted">No signature provided</small>
                            </div>
                        </div>
                    </div>

                    <!-- Final Acknowledgment -->
                    <div class="text-center mt-4">
                        <div class="alert alert-warning">
                            <strong>I agree and acknowledge</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="submitAcknowledgmentBtn"
                        onclick="submitAcknowledgment()" disabled>
                        <i class="bi  bi-check me-1"></i> Confirm & Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal (after adding to cart) -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-white">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pt-0">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <a href="{{ route('packages.atv-utv.list') }}" class="text-decoration-none">
                                <div class="card bg-dark text-white border-0 success-card">
                                    <img src="{{ asset('frontEnd/images/atv-trial.svg') }}"
                                        class="card-img success-card-img" alt="ATV Session Image">
                                    <div class="card-img-overlay d-flex align-items-end p-4">
                                        <h5 class="card-title m-0">Add New ATV/UTV Session</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('packages.custom.index') }}" class="text-decoration-none">
                                <div class="card bg-dark text-white border-0 success-card">
                                    <img src="{{ asset('frontEnd/images/archery.svg') }}"
                                        class="card-img success-card-img" alt="Other Adventures Image">
                                    <div class="card-img-overlay d-flex align-items-end p-4">
                                        <h5 class="card-title m-0">Book Other Adventures</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('packages.regular.index') }}">
                        <button class="btn btn-orange btn-lg my-4 btn-view-cart">
                            View Cart
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('frontEnd/js/custom.js') }}"></script>
    <script>
        // ===== Alpine.js ATV/UTV Booking Component =====
        document.addEventListener('alpine:init', () => {
            Alpine.data('atvBooking', () => ({
                // State
                selectedDate: new Date(),
                selectedTimeSlot: null,
                selectedVehicles: {},
                calendarOpen: false,
                signatureHasContent: false,
                acknowledgment1: false,
                acknowledgment2: false,
                signaturePreviewVisible: false,
                loading: false,

                // Computed
                get formattedDate() {
                    const months = ['January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'
                    ];
                    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday',
                        'Saturday'
                    ];
                    const day = days[this.selectedDate.getDay()];
                    const month = months[this.selectedDate.getMonth()];
                    const dayNum = this.selectedDate.getDate();
                    const year = this.selectedDate.getFullYear();
                    return `${day}, ${month} ${dayNum}, ${year}`;
                },

                get canSubmit() {
                    return this.acknowledgment1 && this.acknowledgment2 && this.signatureHasContent;
                },

                // Methods
                init() {
                    this.initSignatureCanvas();
                    // Select first time slot
                    const firstSlot = document.querySelector('.time-slot');
                    if (firstSlot) {
                        this.selectedTimeSlot = firstSlot.dataset.slotId;
                        firstSlot.classList.add('selected');
                    }
                },

                toggleCalendar() {
                    this.calendarOpen = !this.calendarOpen;
                },

                selectDate(dateString) {
                    this.selectedDate = new Date(dateString);
                    this.calendarOpen = false;
                },

                selectTimeSlot(slotId, event) {
                    document.querySelectorAll('.time-slot').forEach(el => el.classList.remove(
                        'selected'));
                    event.target.classList.add('selected');
                    this.selectedTimeSlot = slotId;
                },

                initSignatureCanvas() {
                    const canvas = document.getElementById('signatureCanvas');
                    if (!canvas) return;

                    const ctx = canvas.getContext('2d');
                    ctx.strokeStyle = '#000000';
                    ctx.lineWidth = 2;
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';

                    let isDrawing = false;
                    let lastX = 0;
                    let lastY = 0;

                    canvas.addEventListener('mousedown', (e) => {
                        isDrawing = true;
                        [lastX, lastY] = [e.offsetX, e.offsetY];
                    });

                    canvas.addEventListener('mousemove', (e) => {
                        if (!isDrawing) return;
                        ctx.beginPath();
                        ctx.moveTo(lastX, lastY);
                        ctx.lineTo(e.offsetX, e.offsetY);
                        ctx.stroke();
                        [lastX, lastY] = [e.offsetX, e.offsetY];
                    });

                    canvas.addEventListener('mouseup', () => {
                        isDrawing = false;
                        this.signatureHasContent = true;
                    });

                    canvas.addEventListener('mouseout', () => {
                        isDrawing = false;
                    });
                },

                clearSignature() {
                    const canvas = document.getElementById('signatureCanvas');
                    if (canvas) {
                        const ctx = canvas.getContext('2d');
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                    }
                    document.getElementById('signatureUpload').value = '';
                    this.signatureHasContent = false;
                    this.signaturePreviewVisible = false;
                },

                handleSignatureUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            document.getElementById('signaturePreviewImg').src = e.target.result;
                            this.signaturePreviewVisible = true;
                            this.signatureHasContent = true;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                async submitAcknowledgment() {
                    this.loading = true;

                    try {
                        const response = await fetch('{{ route('cart.add') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                items: window.pendingCartData,
                                acknowledgment: {
                                    driver_license_requirement: this
                                        .acknowledgment1,
                                    license_show_requirement: this.acknowledgment2
                                }
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            bootstrap.Modal.getInstance(document.getElementById(
                                'acknowledgmentModal')).hide();
                            new bootstrap.Modal(document.getElementById('successModal')).show();

                            if (typeof updateCartCount === 'function') {
                                updateCartCount();
                            }
                        } else {
                            new ToastMagic().error(data.message || 'Error adding to cart');
                        }
                    } catch (error) {
                        new ToastMagic().error('Error adding to cart. Please try again.');
                    }

                    this.loading = false;
                }
            }));
        });

        // Legacy jQuery support for existing functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Global state
            let selectedDate = new Date();
            let selectedTimeSlot = null;
            let selectedVehicles = {};
            // Initialize everything
            initCalendar();
            initDatePickerToggle();
            initVehicleState();
            initTimeSlotSelection();
            initQuantityButtons();
            initAcknowledgmentModal();
            initSuccessModalCards();

            // ===== DATE PICKER FUNCTIONS =====
            function formatDateForDisplay(date) {
                const months = ['January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ];
                const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const day = days[date.getDay()];
                const month = months[date.getMonth()];
                const dayNum = date.getDate();
                const year = date.getFullYear();
                return `${day}, ${month} ${dayNum}, ${year}`;
            }

            function updateDatePickerButton(date) {
                if (date) {
                    $('#datePickerText').text(formatDateForDisplay(date));
                } else {
                    $('#datePickerText').text('Choose Date');
                }
            }

            function initDatePickerToggle() {
                $('#datePickerToggle').on('click', function(e) {
                    e.preventDefault();
                    const $calendarContainer = $('#calendarContainer');
                    const $toggleButton = $(this);

                    if ($calendarContainer.is(':visible')) {
                        $calendarContainer.hide();
                        $toggleButton.removeClass('active');
                    } else {
                        $calendarContainer.show();
                        $toggleButton.addClass('active');
                    }
                });

                updateDatePickerButton(selectedDate);
            }

            // ===== CALENDAR FUNCTIONS =====
            function initCalendar() {
                updateCalendarDisplay();
                setupCalendarNavigation();
                setupCalendarDateSelection();
            }

            function updateCalendarDisplay() {
                const monthNames = [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ];

                const currentMonth = $('.calendar .month').first();
                const nextMonth = $('.calendar .month').last();

                const currentDate = new Date();
                const nextMonthDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);

                currentMonth.text(`${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`);
                nextMonth.text(`${monthNames[nextMonthDate.getMonth()]} ${nextMonthDate.getFullYear()}`);

                renderCalendarDates();
            }

            function renderCalendarDates() {
                const currentDate = new Date();
                const nextMonthDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);

                renderMonthDates($('.calendar-grid').first(), currentDate);
                renderMonthDates($('.calendar-grid').last(), nextMonthDate);
            }

            function renderMonthDates($grid, date) {
                const year = date.getFullYear();
                const month = date.getMonth();

                // Clear existing dates
                $grid.find('.day:not(.day-name)').remove();

                // Get first day of month and number of days
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                const daysInMonth = lastDay.getDate();
                const startingDayOfWeek = firstDay.getDay();

                // Add empty cells for days before the first day of the month
                for (let i = 0; i < startingDayOfWeek; i++) {
                    $grid.append('<span class="day empty"></span>');
                }

                // Add days of the month
                for (let day = 1; day <= daysInMonth; day++) {
                    const dateString =
                        `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    const isSelected = selectedDate.toDateString() === new Date(year, month, day).toDateString();
                    const isToday = new Date().toDateString() === new Date(year, month, day).toDateString();

                    const dayClass = 'day' + (isSelected ? ' selected' : '') + (isToday ? ' today' : '');

                    $grid.append(`<span class="${dayClass}" data-date="${dateString}">${day}</span>`);
                }
            }

            function setupCalendarNavigation() {
                $('.nav-arrow').on('click', function() {
                    // Simple navigation - in real implementation, you would update months
                    alert('Calendar navigation would update months here');
                });
            }

            function setupCalendarDateSelection() {
                $(document).on('click', '.day:not(.empty)', function() {
                    // Remove previous selection
                    $('.day.selected').removeClass('selected');

                    // Add selection to clicked day
                    $(this).addClass('selected');

                    // Update selected date
                    const dateString = $(this).data('date');
                    selectedDate = new Date(dateString);

                    // Update date picker button text
                    updateDatePickerButton(selectedDate);

                    // Close calendar
                    $('#calendarContainer').hide();
                    $('#datePickerToggle').removeClass('active');

                    // Update prices for selected date
                    updatePricesForSelectedDate(dateString);

                    // Show loading
                    showPackagesLoading();

                    // Simulate API call
                    setTimeout(() => {
                        hidePackagesLoading();
                    }, 500);
                });
            }

            // ===== TIME SLOT SELECTION =====
            function initTimeSlotSelection() {
                $(document).on('click', '.time-slot', function() {
                    // Remove selected from all time slots
                    $('.time-slot').removeClass('selected');

                    // Add selected to clicked slot
                    $(this).addClass('selected');
                    selectedTimeSlot = $(this).data('slot-id');

                    // Update availability for selected time slot
                    updateAvailabilityForTimeSlot();
                });

                // Select first time slot by default
                $('.time-slot').first().addClass('selected');
                selectedTimeSlot = $('.time-slot').first().data('slot-id');
            }

            function updateAvailabilityForTimeSlot() {
                // In real implementation, you would fetch availability for this time slot
                // For now, we'll just update button states
                $('.vehicle-card').each(function() {
                    updateVehicleAvailability($(this));
                });
            }

            // ===== VEHICLE MANAGEMENT =====
            function initVehicleState() {
                $('.vehicle-card').each(function() {
                    const $card = $(this);
                    const packageName = $card.data('vehicle');
                    const isATVUTV = $card.data('is-atv-utv') === 1;

                    selectedVehicles[packageName] = {};

                    // Initialize single rider
                    selectedVehicles[packageName]['single'] = {
                        qty: 0,
                        price: parseFloat($card.find('[data-rider-type-id="1"]').data('price') ||
                            $card.find('[data-rider-type-id="0"]').data('price') || 0),
                        riderTypeId: isATVUTV ? 1 : 0,
                        packageId: $card.data('package-id'),
                        name: 'Single Rider'
                    };

                    // Initialize double rider (only for ATV/UTV)
                    if (isATVUTV) {
                        selectedVehicles[packageName]['double'] = {
                            qty: 0,
                            price: parseFloat($card.find('[data-rider-type-id="2"]').data('price') ||
                                0),
                            riderTypeId: 2,
                            packageId: $card.data('package-id'),
                            name: 'Double Rider'
                        };
                    }

                    // Update main vehicle count
                    updateMainVehicleCount(packageName);
                });
            }

            function initQuantityButtons() {
                $(document).on('click', '.quantity-btn', function() {
                    const $button = $(this);
                    const action = $button.data('action');
                    const type = $button.data('type');
                    const $packageCard = $button.closest('.vehicle-card');
                    const packageName = $packageCard.data('vehicle');
                    const totalAvailable = parseInt($packageCard.data('available-vehicles'));

                    const $qtyElement = $(`#${packageName}-${type}-qty`);
                    let currentQty = parseInt($qtyElement.text()) || 0;

                    if (action === 'increment') {
                        // Calculate currently selected vehicles
                        let currentlySelected = 0;
                        if (selectedVehicles[packageName]) {
                            $.each(selectedVehicles[packageName], function(key, vehicle) {
                                currentlySelected += vehicle.qty || 0;
                            });
                        }

                        // Check availability
                        if ((currentlySelected + 1) <= totalAvailable) {
                            currentQty++;
                        } else {
                            toastNotifications.warning(
                                `Maximum ${totalAvailable} vehicles can be selected.`);
                            return;
                        }
                    } else if (action === 'decrement' && currentQty > 0) {
                        currentQty--;
                    }

                    $qtyElement.text(currentQty);

                    // Update state
                    if (selectedVehicles[packageName] && selectedVehicles[packageName][type]) {
                        if (currentQty > 0) {
                            selectedVehicles[packageName][type].qty = currentQty;
                        } else {
                            delete selectedVehicles[packageName][type];
                        }
                    }

                    // Update button states
                    updateQuantityButtonStates($packageCard, type, currentQty, totalAvailable);

                    // Update totals
                    updateVehicleTotals(packageName);

                    // Update main count
                    updateMainVehicleCount(packageName);
                });
            }

            function updateQuantityButtonStates($packageCard, type, currentQty, totalAvailable) {
                const packageName = $packageCard.data('vehicle');
                const $riderCard = $packageCard.find(
                    `[data-rider-type-id="${type === 'single' ? (packageName.includes('atv') ? '1' : '0') : '2'}"]`
                );

                const $incrementBtn = $riderCard.find(`[data-action="increment"][data-type="${type}"]`);
                const $decrementBtn = $riderCard.find(`[data-action="decrement"][data-type="${type}"]`);

                // Calculate currently selected
                let currentlySelected = 0;
                if (selectedVehicles[packageName]) {
                    $.each(selectedVehicles[packageName], function(key, vehicle) {
                        currentlySelected += vehicle.qty || 0;
                    });
                }

                // Update increment button
                if ((currentlySelected + 1) > totalAvailable || totalAvailable === 0) {
                    $incrementBtn.addClass('disabled').prop('disabled', true);
                } else {
                    $incrementBtn.removeClass('disabled').prop('disabled', false);
                }

                // Update decrement button
                if (currentQty <= 0) {
                    $decrementBtn.addClass('disabled').prop('disabled', true);
                } else {
                    $decrementBtn.removeClass('disabled').prop('disabled', false);
                }
            }

            function updateVehicleAvailability($packageCard) {
                const packageName = $packageCard.data('vehicle');
                const totalAvailable = parseInt($packageCard.data('available-vehicles'));

                // Update all rider cards in this package
                $packageCard.find('.rider-option-card').each(function() {
                    const $riderCard = $(this);
                    const type = $riderCard.find('.quantity-btn').data('type');
                    const currentQty = parseInt($riderCard.find('.quantity-value').text()) || 0;

                    updateQuantityButtonStates($packageCard, type, currentQty, totalAvailable);

                    // Update capacity info
                    const $capacityInfo = $riderCard.find('.capacity-info');
                    if (totalAvailable > 0) {
                        $capacityInfo.text(`Max: ${totalAvailable}`).show();
                    } else {
                        $capacityInfo.hide();
                    }
                });

                updateMainVehicleCount(packageName);
            }

            function updateMainVehicleCount(packageName) {
                const $packageCard = $(`.vehicle-card[data-vehicle="${packageName}"]`);
                if (!$packageCard.length) return;

                const totalAvailable = parseInt($packageCard.data('available-vehicles'));

                // Calculate selected vehicles
                let selectedCount = 0;
                if (selectedVehicles[packageName]) {
                    $.each(selectedVehicles[packageName], function(key, vehicle) {
                        selectedCount += vehicle.qty || 0;
                    });
                }

                const remaining = Math.max(0, totalAvailable - selectedCount);

                // Update count display
                const $countElement = $packageCard.find('.vehicle-info .number');
                $countElement.text(remaining);

                // Update availability text
                const $availabilityElement = $packageCard.find('.vehicle-info .availability');
                if (remaining > 0) {
                    $availabilityElement.text('Available').css('color', '#28a745');
                } else {
                    $availabilityElement.text('No vehicles available').css('color', '#dc3545');
                }
            }

            function updateVehicleTotals(packageName) {
                const $packageCard = $(`.vehicle-card[data-vehicle="${packageName}"]`);
                if (!$packageCard.length) return;

                let totalVehicles = 0;
                let totalPrice = 0;

                // Calculate totals from state
                if (selectedVehicles[packageName]) {
                    $.each(selectedVehicles[packageName], function(type, vehicle) {
                        totalVehicles += vehicle.qty || 0;
                        totalPrice += (vehicle.price || 0) * (vehicle.qty || 0);
                    });
                }

                // Update display
                $(`#${packageName}-total-vehicles`).text(totalVehicles > 0 ?
                    `${totalVehicles} ${packageName.toUpperCase()}` : '0');
                $(`#${packageName}-total-price`).text(`TK ${totalPrice.toFixed(2)}`);

                // Update summary list
                updateSummaryList(packageName);
            }

            function updateSummaryList(packageName) {
                const $summaryList = $(`#${packageName}-summary-list`);
                if (!$summaryList.length) return;

                $summaryList.empty();

                if (selectedVehicles[packageName]) {
                    $.each(selectedVehicles[packageName], function(type, vehicle) {
                        if (vehicle.qty > 0) {
                            const isDouble = vehicle.riderTypeId === 2;
                            const helmetIcon = isDouble ?
                                '<img src="{{ asset('frontEnd/images/Helmet.svg') }}" height="20px" alt=""><img src="{{ asset('frontEnd/images/Helmet.svg') }}" height="20px" alt="">' :
                                '<img src="{{ asset('frontEnd/images/Helmet.svg') }}" height="20px" alt="">';

                            const title = vehicle.name;
                            const lineTotal = (vehicle.price || 0) * (vehicle.qty || 0);

                            $summaryList.append(`
                                <div class="summary-item" style="background-color: #f5f5f5;padding:${isDouble ? '9%' : '7%'} 2%; border-radius:5%">
                                    <div class="d-flex flex-column align-items-start">
                                        <span class="rider-icon ${isDouble ? 'd-flex' : ''}">${helmetIcon}</span>
                                        <span class="summary-item-title">${title}</span>
                                    </div>
                                    <div class="summary-item-details">
                                        <div># of ${packageName.toUpperCase()} <strong>${vehicle.qty}</strong></div>
                                        <div class="price-line">Tk ${lineTotal.toFixed(2)}</div>
                                    </div>
                                </div>
                            `);
                        }
                    });
                }
            }

            // ===== PRICING FUNCTIONS =====
            function updatePricesForSelectedDate(dateString) {
                // In real implementation, you would fetch prices for this date
                // For now, we'll just show a message
                console.log('Fetching prices for date:', dateString);

                showPackagesLoading();

                // Simulate API call
                setTimeout(() => {
                    $('.vehicle-card').each(function() {
                        const $card = $(this);
                        const priceData = $card.data('price-data');
                        const dayName = getDayNameFromDate(dateString);

                        // Update prices based on day type
                        updatePackagePrices($card, dayName);
                    });

                    hidePackagesLoading();
                }, 500);
            }

            function getDayNameFromDate(dateString) {
                const date = new Date(dateString);
                const day = date.getDay();
                return (day === 0 || day === 6) ? 'weekend' : 'weekday';
            }

            function updatePackagePrices($packageCard, dayName) {
                // This would fetch actual prices from server
                // For demo, we'll just adjust prices slightly
                const packageName = $packageCard.data('vehicle');
                const isATVUTV = $packageCard.data('is-atv-utv') === 1;
                const baseUrl = $packageCard.data('base-url');

                // Fetch prices from server
                $.ajax({
                    url: `${baseUrl}/api/package-price/${$packageCard.data('package-id')}`,
                    method: 'GET',
                    data: {
                        day: dayName
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update single rider price
                            const $singleCard = $packageCard.find(
                                '[data-rider-type-id="1"], [data-rider-type-id="0"]');
                            if ($singleCard.length) {
                                const singlePrice = response.single_price || response.price || 0;
                                updateRiderCardPrice($singleCard, singlePrice);

                                // Update state
                                if (selectedVehicles[packageName] && selectedVehicles[packageName][
                                        'single'
                                    ]) {
                                    selectedVehicles[packageName]['single'].price = singlePrice;
                                }
                            }

                            // Update double rider price
                            if (isATVUTV) {
                                const $doubleCard = $packageCard.find('[data-rider-type-id="2"]');
                                if ($doubleCard.length && response.double_price) {
                                    updateRiderCardPrice($doubleCard, response.double_price);

                                    // Update state
                                    if (selectedVehicles[packageName] && selectedVehicles[packageName][
                                            'double'
                                        ]) {
                                        selectedVehicles[packageName]['double'].price = response
                                            .double_price;
                                    }
                                }
                            }

                            // Update totals
                            updateVehicleTotals(packageName);
                        }
                    }
                });
            }

            function updateRiderCardPrice($riderCard, newPrice) {
                const displayPrice = $riderCard.data('display-price') || newPrice;
                const originalPrice = $riderCard.data('original-price');

                let priceHTML = '';
                if (displayPrice > 0 && displayPrice < originalPrice) {
                    priceHTML += `<span class="original">TK ${originalPrice.toLocaleString()}</span> `;
                }
                priceHTML += `<span class="current-price">TK ${displayPrice.toLocaleString()}</span>`;

                $riderCard.find('.price').html(priceHTML);
                $riderCard.data('price', displayPrice);
            }

            // ===== LOADING STATES =====
            function showPackagesLoading() {
                $('.row.g-4').addClass('packages-loading');
            }

            function hidePackagesLoading() {
                $('.row.g-4').removeClass('packages-loading');
            }

            // ===== CART FUNCTIONS =====
            window.addToCartAndShowModal = function() {
                // Check if any vehicles are selected
                let hasSelected = false;
                let cartData = [];

                $.each(selectedVehicles, function(packageName, vehicles) {
                    $.each(vehicles, function(type, vehicle) {
                        if (vehicle.qty > 0) {
                            hasSelected = true;
                            cartData.push({
                                package_id: vehicle.packageId,
                                rider_type_id: vehicle.riderTypeId,
                                quantity: vehicle.qty,
                                selected_date: selectedDate.toISOString().split('T')[0],
                                time_slot_id: selectedTimeSlot,
                                cart_amount: vehicle.price * vehicle.qty
                            });
                        }
                    });
                });

                if (!hasSelected) {
                    toastNotifications.warning('Please select at least one vehicle before proceeding.');
                    return;
                }

                if (!selectedTimeSlot) {
                    toastNotifications.warning('Please select a time slot.');
                    return;
                }

                // Store cart data
                window.pendingCartData = cartData;

                // Reset acknowledgment form
                $('#acknowledgment1, #acknowledgment2').prop('checked', false);
                clearSignature();

                // Show modal
                $('#acknowledgmentModal').modal('show');
            };

            // ===== ACKNOWLEDGMENT MODAL =====
            function initAcknowledgmentModal() {
                // Initialize signature canvas
                const canvas = document.getElementById('signatureCanvas');
                if (canvas) {
                    const ctx = canvas.getContext('2d');
                    ctx.strokeStyle = '#000000';
                    ctx.lineWidth = 2;
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';

                    let isDrawing = false;
                    let lastX = 0;
                    let lastY = 0;

                    canvas.addEventListener('mousedown', (e) => {
                        isDrawing = true;
                        [lastX, lastY] = [e.offsetX, e.offsetY];
                    });

                    canvas.addEventListener('mousemove', (e) => {
                        if (!isDrawing) return;
                        ctx.beginPath();
                        ctx.moveTo(lastX, lastY);
                        ctx.lineTo(e.offsetX, e.offsetY);
                        ctx.stroke();
                        [lastX, lastY] = [e.offsetX, e.offsetY];
                    });

                    canvas.addEventListener('mouseup', () => {
                        isDrawing = false;
                        updateSignatureStatus(true);
                    });

                    canvas.addEventListener('mouseout', () => {
                        isDrawing = false;
                    });
                }

                // Checkbox validation
                $('.acknowledgment-checkbox').on('change', function() {
                    validateAcknowledgment();
                });

                // File upload
                $('#signatureUpload').on('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#signaturePreviewImg').attr('src', e.target.result);
                            $('#signaturePreview').show();
                            updateSignatureStatus(true);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            function clearSignature() {
                const canvas = document.getElementById('signatureCanvas');
                if (canvas) {
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                }
                $('#signatureUpload').val('');
                $('#signaturePreview').hide();
                updateSignatureStatus(false);
            }

            function updateSignatureStatus(hasSignature) {
                if (hasSignature) {
                    $('#signatureStatus').html(
                        '<small class="text-success"><i class="bi bi-check me-1"></i>Signature provided</small>'
                    );
                } else {
                    $('#signatureStatus').html('<small class="text-muted">No signature provided</small>');
                }
                validateAcknowledgment();
            }

            function validateAcknowledgment() {
                const checkbox1 = $('#acknowledgment1').is(':checked');
                const checkbox2 = $('#acknowledgment2').is(':checked');
                const hasSignature = $('#signatureStatus').find('.text-success').length > 0;

                if (checkbox1 && checkbox2 && hasSignature) {
                    $('#submitAcknowledgmentBtn').prop('disabled', false);
                } else {
                    $('#submitAcknowledgmentBtn').prop('disabled', true);
                }
            }

            // ===== SUCCESS MODAL =====
            function initSuccessModalCards() {
                $('.card[data-href]').on('click', function() {
                    const url = $(this).data('href');
                    if (url) {
                        window.location.href = url;
                    }
                });

                $('.card[data-href]').on('mouseenter', function() {
                    $(this).css('transform', 'scale(1.05)');
                });

                $('.card[data-href]').on('mouseleave', function() {
                    $(this).css('transform', 'scale(1)');
                });
            }

            window.closeSuccessModal = function() {
                $('#successModal').modal('hide');
            };
        });

        // ===== GLOBAL FUNCTIONS FOR MODAL =====
        function submitAcknowledgment() {
            const acknowledgmentData = {
                driver_license_requirement: $('#acknowledgment1').is(':checked'),
                license_show_requirement: $('#acknowledgment2').is(':checked')
            };

            // Add to cart via AJAX
            $.ajax({
                url: '{{ route('cart.add') }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                contentType: 'application/json',
                data: JSON.stringify({
                    items: window.pendingCartData,
                    acknowledgment: acknowledgmentData
                }),
                success: function(response) {
                    if (response.success) {
                        // Close acknowledgment modal
                        $('#acknowledgmentModal').modal('hide');

                        // Show success modal
                        $('#successModal').modal('show');

                        // Update cart count if function exists
                        if (typeof updateCartCount === 'function') {
                            updateCartCount();
                        }
                    } else {
                        toastNotifications.error(response.message || 'Error adding to cart');
                    }
                },
                error: function() {
                    toastNotifications.error('Error adding to cart. Please try again.');
                }
            });
        }
    </script>
@endpush
