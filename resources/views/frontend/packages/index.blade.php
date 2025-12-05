@extends('layouts.frontend')

@section('title', 'ATV/UTV Trail Rides - Packages')

@section('body_attrs', 'style="color: rgb(255, 111, 0) !important;"')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('frontEnd/css/csutom.css') }}">
    <link rel="stylesheet" href="{{ asset('frontEnd/css/packege.css') }}">
    <style>
        /* Price Tags and Availability Badges */
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



        .limited-badge {
            display: inline-block;
            padding: 2px 6px;
            background-color: #f59e0b;
            color: white;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 500;
            margin-left: 8px;
        }



        .capacity-info {
            font-size: 0.7rem;
            color: #6b7280;
            text-align: center;
            margin-top: 4px;
            font-weight: 500;
        }

        /* Price display improvements */
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

        /* Time slots grid layout */
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

        /* Responsive adjustments */
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

        /* Modal close button improvements */
        .btn-close {
            position: relative;
            z-index: 1055;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }

        .btn-close:hover {
            opacity: 1;
        }

        .modal-header {
            position: relative;
            z-index: 1050;
        }

        /* Quantity button disabled state */
        .quantity-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #e9ecef;
        }

        .quantity-btn.disabled:hover {
            opacity: 0.5;
            background-color: #e9ecef;
        }

        /* Subtle loading states for packages area */
        .packages-loading {
            opacity: 0.6;
            pointer-events: none;
            transition: opacity 0.3s ease;
            position: relative;
        }

        .packages-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            z-index: 5;
        }

        /* Acknowledgment Modal Styles */
        .acknowledgment-item {
            transition: all 0.3s ease;
            min-height: 120px;
        }

        .acknowledgment-item:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .acknowledgment-checkbox:checked + .form-label {
            color: #28a745;
            font-weight: 600;
        }

        .acknowledgment-checkbox:checked ~ .badge {
            background-color: #28a745 !important;
        }

        /* Signature Canvas Styles */
        #signatureCanvas {
            background-color: #fff;
            touch-action: none;
        }

        .signature-canvas-container {
            position: relative;
        }

        .signature-canvas-container canvas {
            max-width: 100%;
            height: auto;
        }

        /* Signature Upload Styles */
        .signature-upload-container input[type="file"] {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: border-color 0.3s ease;
        }

        .signature-upload-container input[type="file"]:hover {
            border-color: #007bff;
        }

        /* Signature Status */
        #signatureStatus .text-success {
            color: #28a745 !important;
            font-weight: 600;
        }

        #signatureStatus .text-muted {
            color: #6c757d !important;
        }

        /* Submit Button States */
        #submitAcknowledgmentBtn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        #submitAcknowledgmentBtn:not(:disabled) {
            background-color: #28a745;
            border-color: #28a745;
        }

        /* Tab Navigation */
        .nav-tabs .nav-link {
            border: 1px solid transparent;
            border-radius: 0.375rem 0.375rem 0 0;
        }

        .nav-tabs .nav-link.active {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }

        .nav-tabs .nav-link:hover:not(.active) {
            border-color: #e9ecef #e9ecef #dee2e6;
            background-color: #f8f9fa;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .modal-dialog[style*="width: 1200px"] {
                max-width: 95% !important;
                width: 95% !important;
            }
        }

        @media (max-width: 768px) {
            .acknowledgment-item {
                margin-bottom: 15px;
            }

            #signatureCanvas {
                width: 100% !important;
                height: 150px !important;
            }

            .modal-dialog[style*="width: 1200px"] {
                max-width: 98% !important;
                width: 98% !important;
            }
        }

        @media (max-width: 576px) {
            .modal-body {
                padding: 1rem;
            }

            .acknowledgment-item {
                padding: 1rem !important;
                min-height: 100px;
            }

            .signature-canvas-container {
                padding: 0 10px;
            }
        }

        /* Date Picker Toggle Button Styles */
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

        .date-picker-toggle-btn .toggle-icon {
            font-size: 0.875rem;
        }

        /* Calendar container transition */
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
                            <button type="button" id="datePickerToggle" class="btn btn-outline-primary date-picker-toggle-btn w-100 mb-3">
                                <i class="bi bi-calendar3 me-2"></i>
                                <span id="datePickerText">Choose Date</span>
                                <i class="bi bi-chevron-down ms-auto toggle-icon"></i>
                            </button>
                            <div class="calendar-container" id="calendarContainer" style="display: none;">
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
                                <!-- Time slots will be populated dynamically -->
                            </div>
                        </div>

                        <div>
                            <h3 class="section-heading">Select Vehicle</h3>
                            <div class="row g-4">
                                @foreach ($packages as $index => $package)
                                    @php
                                        // Calculate total available vehicles for this package (for display purposes only)
                                        // The actual availability will be calculated per variant
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
                                    @endphp
                                    <div class="col-lg-6">
                                        <div class="vehicle-card" data-vehicle="{{ strtolower($package->name) }}"
                                            data-package="{{ strtolower($package->name) }}"
                                            data-package-id="{{ $package->id }}">
                                            <div class="vehicle-header">
                                                <div class="vehicle-info">
                                                    @php
                                                        // For ATV/UTV package, show the maximum available capacity from variants
                                                        $maxAvailableCapacity = 0;
                                                        if ($package->name === 'ATV/UTV Trail Rides') {
                                                            foreach (
                                                                $variantsByPackage[$package->id] ?? []
                                                                as $variant
                                                            ) {
                                                                $today = \Carbon\Carbon::today()->format('Y-m-d');
                                                                $pricingData =
                                                                    $pricingWithAvailability[$variant->id][$today] ??
                                                                    null;
                                                                $availableCapacity =
                                                                    $pricingData['available_capacity'] ?? 0;
                                                                $maxAvailableCapacity = max(
                                                                    $maxAvailableCapacity,
                                                                    $availableCapacity,
                                                                );
                                                            }
                                                        } else {
                                                            $maxAvailableCapacity = $totalAvailableVehicles;
                                                        }
                                                    @endphp
                                                    <div class="number" data-original-count="{{ $maxAvailableCapacity }}">
                                                        {{ $maxAvailableCapacity }}</div>
                                                    <div class="type">{{ $package->name }}</div>
                                                    <div class="availability">
                                                        {{ $maxAvailableCapacity > 0 ? 'Available' : 'No vehicles available' }}
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
                                                @php
                                                    $variants = $variantsByPackage[$package->id] ?? collect();
                                                @endphp
                                                @if ($variants->count() > 0)
                                                    @foreach ($variants as $variant)
                                                        <div class="rider-option-card"
                                                            data-variant-id="{{ $variant->id }}">
                                                            <span class="rider-icon">
                                                                @for ($i = 0; $i < min($variant->capacity, 2); $i++)
                                                                    <img src="{{ asset('frontEnd/images/Helmet.svg') }}"
                                                                        height="20px" alt="">
                                                                @endfor
                                                            </span>
                                                            <div class="rider-title">{{ $variant->name }}</div>
                                                            <div class="rider-desc">{{ $variant->capacity }} person can
                                                                ride this</div>
                                                            <div class="price">
                                                                @php
                                                                    $today = \Carbon\Carbon::today()->format('Y-m-d');
                                                                    $pricingData =
                                                                        $pricingWithAvailability[$variant->id][
                                                                            $today
                                                                        ] ?? null;
                                                                    $finalPrice = $pricingData['final_price'] ?? 0;
                                                                    $defaultPrice = $pricingData['default_price'] ?? 0;
                                                                    $priceTag = $pricingData['price_tag'] ?? null;
                                                                    $isAvailable = $pricingData['is_available'] ?? true;
                                                                    $availableCapacity =
                                                                        $pricingData['available_capacity'] ?? 0;
                                                                @endphp
                                                                @if ($priceTag)
                                                                    <span
                                                                        class="price-tag {{ $priceTag }}">{{ ucfirst($priceTag) }}</span>
                                                                @endif
                                                                @if ($defaultPrice > $finalPrice)
                                                                    <span class="original">TK
                                                                        {{ number_format($defaultPrice) }}</span>
                                                                @endif
                                                                TK {{ number_format($finalPrice) }}

                                                            </div>
                                                            <div class="quantity-selector">
                                                                <button class="quantity-btn"
                                                                    data-type="{{ $variant->capacity == 1 ? 'single' : 'double' }}"
                                                                    data-action="decrement">-</button>
                                                                <span class="quantity-value"
                                                                    id="{{ strtolower($package->name) }}-{{ $variant->capacity == 1 ? 'single' : 'double' }}-qty">0</span>
                                                                <button class="quantity-btn"
                                                                    data-type="{{ $variant->capacity == 1 ? 'single' : 'double' }}"
                                                                    data-action="increment">+</button>
                                                                @php
                                                                    $isDayOff = $pricingData['is_day_off'] ?? false;
                                                                @endphp
                                                                @if ($isDayOff)
                                                                    <div class="capacity-info text-danger">Day Off</div>
                                                                @elseif($availableCapacity > 0)
                                                                    <div class="capacity-info">Max:
                                                                        {{ $availableCapacity }}</div>
                                                                @else
                                                                    <div class="capacity-info text-muted">Unavailable</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="rider-option-card">
                                                        <span class="rider-icon">
                                                            <img src="{{ asset('frontEnd/images/Helmet.svg') }}"
                                                                height="20px" alt="">
                                                        </span>
                                                        <div class="rider-title">{{ $package->name }} Standard</div>
                                                        <div class="rider-desc">1 person can ride this</div>
                                                        <div class="price">TK 0</div>
                                                        <div class="quantity-selector">
                                                            <button class="quantity-btn" data-type="single"
                                                                data-action="decrement">-</button>
                                                            <span class="quantity-value"
                                                                id="{{ strtolower($package->name) }}-single-qty">0</span>
                                                            <button class="quantity-btn" data-type="single"
                                                                data-action="increment">+</button>
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
                                                            <div style="font-size: 1rem;" class="total-vehicles"
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
                        <button style="min-width: 200px;" type="button" class="btn btn-add-to-cart w-25"
                            onclick="addToCartAndShowModal()">
                            Pay Now
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ATV/UTV Acknowledgment Modal -->
    <div class="modal fade" id="acknowledgmentModal" tabindex="-1" aria-labelledby="acknowledgmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 90%; width: 1200px;">
            <div class="modal-content" style="background-color: #ffffff;">
                <div class="modal-header" style="background-color: #2c5aa0; color: white;">
                    <h5 class="modal-title" id="acknowledgmentModalLabel">
                        <i class="bi  bi-exclamation-triangle me-2"></i>
                        Booking Acknowledgment - ATV/UTV Trail Rides
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info">
                        <strong>By booking this reservation I acknowledge below and all rules and regulations of the <span style="color: #dc3545; text-decoration: underline;">xadventure</span></strong>
                    </div>

                    <!-- Acknowledgment Checkboxes -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="acknowledgment-item p-3 border rounded" style="background-color: #f8f9fa;">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <input type="checkbox" class="form-check-input acknowledgment-checkbox" id="acknowledgment1" style="transform: scale(1.2);">
                                    </div>
                                    <div class="flex-grow-1">
                                        <label for="acknowledgment1" class="form-label mb-0" style="cursor: pointer;">
                                            I understand that for Each vehicle reservation, we will have at least one person have <span style="color: #007bff; text-decoration: underline;">Motorcycle</span> or Car Driver's licenses
                                        </label>
                                    </div>
                                </div>
                                <div class="text-end mt-2">
                                    <span class="badge bg-primary">I Acknowledge</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="acknowledgment-item p-3 border rounded" style="background-color: #f8f9fa;">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <input type="checkbox" class="form-check-input acknowledgment-checkbox" id="acknowledgment2" style="transform: scale(1.2);">
                                    </div>
                                    <div class="flex-grow-1">
                                        <label for="acknowledgment2" class="form-label mb-0" style="cursor: pointer;">
                                            I understand I have to <span style="color: #dc3545; text-decoration: underline;">carry</span> show my driver's license before the ride start. Failure the show my physical license, <span style="color: #dc3545; text-decoration: underline;">Xadventure</span> will Deny the Ride, and I will not get my money back.
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
                                <button class="nav-link active" id="draw-tab" data-bs-toggle="tab" data-bs-target="#draw-pane" type="button" role="tab">
                                    <i class="bi  bi-pen me-1"></i> Draw Signature
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-pane" type="button" role="tab">
                                    <i class="bi  bi-upload me-1"></i> Upload Signature
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="signatureTabContent">
                            <!-- Draw Signature Tab -->
                            <div class="tab-pane fade show active" id="draw-pane" role="tabpanel">
                                <div class="signature-canvas-container text-center">
                                    <canvas id="signatureCanvas" width="500" height="200" style="border: 2px dashed #dee2e6; border-radius: 8px; cursor: crosshair;"></canvas>
                                    <div class="mt-2">
                                        <small class="text-muted">Draw your signature above</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Signature Tab -->
                            <div class="tab-pane fade" id="upload-pane" role="tabpanel">
                                <div class="signature-upload-container text-center">
                                    <input type="file" id="signatureUpload" accept="image/png,image/jpeg,image/jpg" class="form-control mb-3" style="max-width: 400px; margin: 0 auto;">
                                    <div id="signaturePreview" class="mt-3" style="display: none;">
                                        <img id="signaturePreviewImg" style="max-width: 400px; max-height: 200px; border: 2px solid #dee2e6; border-radius: 8px;">
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
                    <button type="button" class="btn btn-success" id="submitAcknowledgmentBtn" onclick="submitAcknowledgment()" disabled>
                        <i class="bi  bi-check me-1"></i> Confirm & Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal (after adding to cart) -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: #ffffff;">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeSuccessModal()"></button>
                </div>
                <div class="modal-body text-center pt-0">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card bg-dark text-white border-0"
                                style="border-radius: 1rem; overflow: hidden; cursor: pointer; transition: transform 0.3s ease;"
                                data-href="{{ route('frontend.packages.index') }}">
                                <img src="{{ asset('frontEnd/images/atv-trial.svg') }}" class="card-img"
                                    alt="ATV Session Image" style="filter: brightness(60%);">
                                <div class="card-img-overlay d-flex align-items-end p-4">
                                    <h5 class="card-title m-0">Add New ATV/UTV Session</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-dark text-white border-0"
                                style="border-radius: 1rem; overflow: hidden; cursor: pointer; transition: transform 0.3s ease;"
                                data-href="{{ route('custom-packages') }}">
                                <img src="{{ asset('frontEnd/images/archery.svg') }}" class="card-img"
                                    alt="Other Adventures Image" style="filter: brightness(60%);">
                                <div class="card-img-overlay d-flex align-items-end p-4">
                                    <h5 class="card-title m-0">Book Other Adventures</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('frontend.process-to-checkout') }}">
                        <button class="btn btn-orange btn-lg my-4" style="background-color: #ff6f00; color: #fff;">
                            View Cart
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('frontEnd/js/custom.js') }}"></script>
    <script type="application/json" id="packages-data">
    @json($packages)
</script>
    <script type="application/json" id="schedule-slots-data">
    @json($scheduleSlots)
</script>
    <script type="application/json" id="availability-data">
    @json($availabilityData)
</script>
    <script type="application/json" id="package-variants-data">
    @json($packageVariants)
</script>
    <script type="application/json" id="variants-by-package-data">
    @json($variantsByPackage)
</script>
    <script type="application/json" id="pricing-with-availability-data">
    @json($pricingWithAvailability)
</script>
    <script>
        // Pass PHP data to JavaScript
        const packages = JSON.parse(document.getElementById('packages-data').textContent || '[]');
        const scheduleSlots = JSON.parse(document.getElementById('schedule-slots-data').textContent || '[]');
        const availabilityData = JSON.parse(document.getElementById('availability-data').textContent || '{}');
        const packageVariants = JSON.parse(document.getElementById('package-variants-data').textContent || '[]');
        const variantsByPackage = JSON.parse(document.getElementById('variants-by-package-data').textContent || '{}');
        const pricingWithAvailability = JSON.parse(document.getElementById('pricing-with-availability-data').textContent ||
            '{}');

        // Global state
        let currentDate = new Date();
        let selectedDate = new Date();
        let selectedTimeSlot = null;
        let selectedVehicles = {};
        // Per-slot, per-package aggregated availability (total across variants)
        let slotPackageAvailability = {};

        // Date Picker Toggle Functions
        function formatDateForDisplay(date) {
            const months = ['January', 'February', 'March', 'April', 'May', 'June',
                          'July', 'August', 'September', 'October', 'November', 'December'];
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const day = days[date.getDay()];
            const month = months[date.getMonth()];
            const dayNum = date.getDate();
            const year = date.getFullYear();
            return `${day}, ${month} ${dayNum}, ${year}`;
        }

        function updateDatePickerButton(date) {
            const button = document.getElementById('datePickerToggle');
            const textSpan = document.getElementById('datePickerText');
            if (button && textSpan) {
                if (date) {
                    textSpan.textContent = formatDateForDisplay(date);
                } else {
                    textSpan.textContent = 'Choose Date';
                }
            }
        }

        function toggleCalendar() {
            const calendarContainer = document.getElementById('calendarContainer');
            const toggleButton = document.getElementById('datePickerToggle');

            if (calendarContainer && toggleButton) {
                const isVisible = calendarContainer.style.display !== 'none';

                if (isVisible) {
                    calendarContainer.style.display = 'none';
                    toggleButton.classList.remove('active');
                } else {
                    // Use flex display to maintain the two-month side-by-side layout
                    calendarContainer.style.display = 'flex';
                    toggleButton.classList.add('active');
                }
            }
        }

        // Initialize date picker toggle button
        function initDatePickerToggle() {
            const toggleButton = document.getElementById('datePickerToggle');
            if (toggleButton) {
                toggleButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleCalendar();
                });
            }

            // Update button text on initial load
            updateDatePickerButton(selectedDate);
        }

        // Loading state management
        let isLoading = false;
        let loadingTimeout = null;

        // Subtle loading management functions
        function showPackagesLoading() {
            const packagesContainer = document.querySelector('.row.g-4');
            if (packagesContainer) {
                packagesContainer.classList.add('packages-loading');
                isLoading = true;

                // Set a timeout to hide loading if it takes too long
                loadingTimeout = setTimeout(() => {
                    hidePackagesLoading();
                }, 10000); // 10 seconds timeout
            }
        }

        function hidePackagesLoading() {
            const packagesContainer = document.querySelector('.row.g-4');
            if (packagesContainer) {
                packagesContainer.classList.remove('packages-loading');
                isLoading = false;

                if (loadingTimeout) {
                    clearTimeout(loadingTimeout);
                    loadingTimeout = null;
                }
            }
        }

        // Global function to force update all quantity button states
        function forceUpdateAllQuantityButtonStates() {

            packages.forEach(package => {
                const packageName = package.name.toLowerCase();
                const variants = Array.isArray(variantsByPackage[package.id]) ? variantsByPackage[package.id] : [];

                variants.forEach(variant => {
                    const type = variant.capacity == 1 ? 'single' : 'double';
                    const qtyElement = document.getElementById(`${packageName}-${type}-qty`);

                    if (qtyElement) {
                        const currentQty = parseInt(qtyElement.textContent) || 0;

                        // Find the decrement button for this variant
                        const decrementBtn = document.querySelector(
                            `[data-action="decrement"][data-type="${type}"]`);
                        if (decrementBtn) {
                            const btnPackageCard = decrementBtn.closest('.vehicle-card');
                            const btnPackageName = btnPackageCard.getAttribute('data-vehicle');

                            if (btnPackageName === packageName) {
                                if (currentQty > 0) {
                                    decrementBtn.classList.remove('disabled');
                                    decrementBtn.disabled = false;
                                } else {
                                    decrementBtn.classList.add('disabled');
                                    decrementBtn.disabled = true;
                                }
                            }
                        }
                    }
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Delegate hover/scale and click for CTA cards
            document.querySelectorAll('.card[data-href]').forEach(function(card) {
                card.addEventListener('click', function() {
                    const url = card.getAttribute('data-href');
                    if (url) window.location.href = url;
                });
                card.addEventListener('mouseenter', function() {
                    card.style.transform = 'scale(1.05)';
                });
                card.addEventListener('mouseleave', function() {
                    card.style.transform = 'scale(1)';
                });
            });

            // Initialize calendar
            initCalendar();

            // Initialize date picker toggle
            initDatePickerToggle();

            // Initialize vehicle state
            initVehicleState();

            // Initialize quantity button states
            initializeQuantityButtonStates();

            // Force update all button states after initialization
            setTimeout(() => {
                packages.forEach(package => {
                    const packageName = package.name.toLowerCase();
                    const variants = Array.isArray(variantsByPackage[package.id]) ?
                        variantsByPackage[package.id] : [];
                    variants.forEach(variant => {
                        const type = variant.capacity == 1 ? 'single' : 'double';
                        const qtyElement = document.getElementById(
                            `${packageName}-${type}-qty`);
                        if (qtyElement) {
                            const currentQty = parseInt(qtyElement.textContent) || 0;
                            updateQuantityButtonStates(packageName, type, currentQty,
                                999); // Use high capacity for testing
                        }
                    });
                });
            }, 100);

            // Update all variants availability to ensure correct initial state
            packages.forEach(package => {
                const packageName = package.name.toLowerCase();
                updateAllVariantsAvailability(packageName);
            });
        });

        // Initialize vehicle state
        function initVehicleState() {
            packages.forEach(package => {
                const packageName = package.name.toLowerCase();
                selectedVehicles[packageName] = {};

                const variants = Array.isArray(variantsByPackage[package.id]) ? variantsByPackage[package.id] : [];
                variants.forEach(variant => {
                    const type = variant.capacity == 1 ? 'single' : 'double';
                    selectedVehicles[packageName][type] = {
                        qty: 0,
                        price: 0,
                        name: variant.name,
                        variantId: variant.id
                    };
                });

                // Initialize the big number to show total available vehicles
                updateMainVehicleCount(packageName);
            });
        }

        // Initialize quantity button states
        function initializeQuantityButtonStates() {
            packages.forEach(package => {
                const packageName = package.name.toLowerCase();
                const variants = Array.isArray(variantsByPackage[package.id]) ? variantsByPackage[package.id] : [];

                variants.forEach(variant => {
                    const type = variant.capacity == 1 ? 'single' : 'double';
                    const currentQty = 0; // Initial quantity is always 0

                    // Get the package card to find total available vehicles
                    const packageCard = document.querySelector(`[data-vehicle="${packageName}"]`);
                    if (packageCard) {
                        const packageHeader = packageCard.querySelector('.vehicle-info .number');
                        const totalAvailableVehicles = parseInt(packageHeader.getAttribute(
                            'data-original-count')) || 0;

                        // Initially, all variants can use the total available vehicles
                        updateQuantityButtonStates(packageName, type, currentQty, totalAvailableVehicles);
                    }
                });
            });
        }

        // Initialize calendar
        function initCalendar() {
            updateCalendarDisplay();
            setupCalendarNavigation();
            setupCalendarDateSelection();
            // Load dynamic time slots for the initially selected date
            const initialDateString = selectedDate.toISOString().split('T')[0];
            refreshDynamicSlots(initialDateString);
        }

        function updateCalendarDisplay() {
            const monthNames = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];

            // Update both calendar months
            const monthElements = document.querySelectorAll('.month');

            if (monthElements.length >= 2) {
                const currentMonth = monthElements[0];
                const nextMonth = monthElements[1];

                currentMonth.textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;

                const nextMonthDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
                nextMonth.textContent = `${monthNames[nextMonthDate.getMonth()]} ${nextMonthDate.getFullYear()}`;

            }

            renderCalendarDates();
        }

        function renderCalendarDates() {
            const calendarGrids = document.querySelectorAll('.calendar-grid');

            if (calendarGrids.length < 2) {
                return;
            }

            const currentGrid = calendarGrids[0];
            const nextGrid = calendarGrids[1];

            // Clear existing dates (keep day names)
            currentGrid.querySelectorAll('.day:not(.day-name)').forEach(el => el.remove());
            nextGrid.querySelectorAll('.day:not(.day-name)').forEach(el => el.remove());

            // Render current month
            renderMonthDates(currentGrid, currentDate);

            // Render next month
            const nextMonthDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
            renderMonthDates(nextGrid, nextMonthDate);

        }

        function renderMonthDates(grid, date) {
            const year = date.getFullYear();
            const month = date.getMonth();

            // Get first day of month and number of days
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDayOfWeek = firstDay.getDay();

            // Add empty cells for days before the first day of the month
            for (let i = 0; i < startingDayOfWeek; i++) {
                const emptyDay = document.createElement('span');
                emptyDay.className = 'day empty';
                grid.appendChild(emptyDay);
            }

            // Add days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const isSelected = selectedDate.toDateString() === new Date(year, month, day).toDateString();
                const isToday = new Date().toDateString() === new Date(year, month, day).toDateString();

                const dayElement = document.createElement('span');
                dayElement.className = 'day';
                dayElement.textContent = day;
                dayElement.setAttribute('data-date', dateString);

                if (isSelected) dayElement.classList.add('selected');
                if (isToday) dayElement.classList.add('today');

                grid.appendChild(dayElement);
            }
        }

        function setupCalendarNavigation() {
            const navArrows = document.querySelectorAll('.nav-arrow');

            navArrows.forEach((arrow, index) => {
                arrow.addEventListener('click', function() {
                    if (index === 0) { // Left arrow
                        currentDate.setMonth(currentDate.getMonth() - 1);
                    } else { // Right arrow
                        currentDate.setMonth(currentDate.getMonth() + 1);
                    }
                    updateCalendarDisplay();
                });
            });
        }

        function setupCalendarDateSelection() {
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('day') && !e.target.classList.contains('empty')) {

                    // Remove previous selection from all days
                    document.querySelectorAll('.day.selected').forEach(day => {
                        day.classList.remove('selected');
                    });

                    // Add selection to clicked day
                    e.target.classList.add('selected');

                    // Update selected date
                    const dateString = e.target.getAttribute('data-date');
                    selectedDate = new Date(dateString);

                    // Update date picker button text
                    updateDatePickerButton(selectedDate);

                    // Close calendar after selection
                    const calendarContainer = document.getElementById('calendarContainer');
                    const toggleButton = document.getElementById('datePickerToggle');
                    if (calendarContainer && calendarContainer.style.display !== 'none') {
                        calendarContainer.style.display = 'none';
                        if (toggleButton) {
                            toggleButton.classList.remove('active');
                        }
                    }

                    console.log('Date selected:', dateString);
                    console.log('Pricing data for this date:', pricingWithAvailability);

                    // Show subtle loading for date change
                    showPackagesLoading();

                    // Update pricing for selected date
                    updatePricingForDate(dateString);

                    // Refresh dynamic slots
                    refreshDynamicSlots(dateString);

                    // Check availability for selected vehicles
                    if (Object.keys(selectedVehicles).length > 0) {
                        Object.keys(selectedVehicles).forEach(packageName => {
                            Object.keys(selectedVehicles[packageName]).forEach(type => {
                                const vehicleData = selectedVehicles[packageName][type];
                                if (vehicleData.quantity > 0 && vehicleData.variantId) {
                                    checkAvailabilityForVehicle(packageName, type, vehicleData
                                        .variantId, dateString, selectedTimeSlot, vehicleData
                                        .quantity);
                                }
                            });
                        });
                    }

                    // Update quantity button states for all variants
                    initializeQuantityButtonStates();

                }
            });
        }

        // Fetch and render dynamic time slots for selected date and first variant of each package
        function refreshDynamicSlots(dateString) {
            const container = document.getElementById('dynamic-time-slots');
            if (!container) return;

            // Show subtle loading for time slots
            showPackagesLoading();

            // Aggregate availability across ALL variants so a slot remains enabled
            // as long as ANY package has capacity in that slot
            const allVariants = packageVariants && packageVariants.length ? packageVariants : [];
            if (allVariants.length === 0) {
                hidePackagesLoading();
                return;
            }

            const availabilityPromises = allVariants.map(v =>
                fetch(`/api/schedule-slots/availability?variant_id=${v.id}&date=${dateString}`)
                .then(r => r.json())
            );

            Promise.all(availabilityPromises)
                .then(allSlotsData => {
                    // slotId -> { id, label, is_open, has_any }
                    const slotAvailability = {};
                    allSlotsData.forEach(variantSlots => {
                        (variantSlots || []).forEach(slot => {
                            if (!slotAvailability[slot.id]) {
                                slotAvailability[slot.id] = {
                                    id: slot.id,
                                    label: slot.label,
                                    is_open: !!slot.is_open,
                                    has_any: false
                                };
                            } else {
                                // Preserve label and openness if already set; merge openness
                                slotAvailability[slot.id].is_open = slotAvailability[slot.id].is_open ||
                                    !!slot.is_open;
                            }
                            // Enable slot if ANY variant has capacity in this slot
                            if (slot.is_open && (slot.available_capacity || 0) > 0) {
                                slotAvailability[slot.id].has_any = true;
                            }
                        });
                    });

                    // Render
                    container.innerHTML = '';
                    let firstAvailableId = null;
                    Object.values(slotAvailability)
                        .sort((a, b) => a.id - b.id)
                        .forEach(slot => {
                            const div = document.createElement('div');
                            div.className = 'time-slot';
                            div.setAttribute('data-slot-id', slot.id);
                            div.textContent = slot.label;
                            if (!firstAvailableId && slot.is_open && slot.has_any) {
                                firstAvailableId = slot.id;
                                div.classList.add('selected');
                                selectedTimeSlot = String(slot.id);
                            }
                            if (!slot.is_open || !slot.has_any) {
                                div.style.opacity = '0.5';
                                div.title = 'Sold out';
                            }
                            container.appendChild(div);
                        });

                    // After we pick the first available slot, hydrate pricing and availability
                    if (selectedTimeSlot) {
                        updateAllPricingDisplays(dateString);
                        refreshAllVariantsAvailabilityForSlot(dateString, selectedTimeSlot);
                        initializeQuantityButtonStates();
                    }

                    // Hide subtle loading
                    hidePackagesLoading();
                })
                .catch(err => {
                    // Failed to load dynamic slots
                    console.error('Error loading time slots:', err);
                    hidePackagesLoading();
                });
        }

        // Enhanced time slot selection
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('time-slot')) {

                // Remove selected from all time slots
                document.querySelectorAll('.time-slot').forEach(slot => {
                    slot.classList.remove('selected');
                });

                // Add selected to clicked slot
                e.target.classList.add('selected');
                selectedTimeSlot = e.target.getAttribute('data-slot-id');

                console.log('Time slot selected:', selectedTimeSlot);

                // Show subtle loading for time slot change
                showPackagesLoading();

                // Update availability for all variants for the new selected slot
                if (selectedDate && selectedTimeSlot) {
                    const dateString = selectedDate.toISOString().split('T')[0];
                    console.log('Updating availability for date:', dateString, 'slot:', selectedTimeSlot);
                    refreshAllVariantsAvailabilityForSlot(dateString, selectedTimeSlot);
                }

                // Check availability for all selected vehicles
                packages.forEach(package => {
                    Object.keys(selectedVehicles[package.name.toLowerCase()]).forEach(type => {
                        const quantity = selectedVehicles[package.name.toLowerCase()][type].qty;
                        if (quantity > 0) {
                            checkAvailabilityForVehicle(package.name.toLowerCase(), type, quantity);
                        }
                    });
                });

                // Update quantity button states for all variants
                initializeQuantityButtonStates();
            }
        });

        // Refresh availability for all visible variants for a given date and slot
        function refreshAllVariantsAvailabilityForSlot(dateString, slotId) {
            console.log('refreshAllVariantsAvailabilityForSlot called:', {
                dateString,
                slotId
            });

            // Show subtle loading for availability updates
            showPackagesLoading();

            const riderCards = document.querySelectorAll('.rider-option-card');
            console.log('Found rider cards:', riderCards.length);

            const availabilityByPackage = {};
            let completedRequests = 0;
            const totalRequests = riderCards.length;

            riderCards.forEach(card => {
                const variantId = card.getAttribute('data-variant-id');
                if (!variantId) return;

                const packageCard = card.closest('.vehicle-card');
                if (!packageCard) return;
                const packageName = packageCard.getAttribute('data-vehicle');

                // Get the type from the quantity buttons
                const quantityBtn = card.querySelector('.quantity-btn');
                const type = quantityBtn ? quantityBtn.getAttribute('data-type') : 'single';

                console.log('Fetching availability for:', {
                    variantId,
                    packageName,
                    type,
                    slotId
                });

                fetch(
                        `/api/availability/check?date=${dateString}&variant_id=${variantId}&slot_id=${slotId}&quantity=1`
                    )
                    .then(response => response.json())
                    .then(data => {
                        console.log('API response for', variantId, ':', data);

                        // Update the pricingWithAvailability data with per-slot availability
                        if (!pricingWithAvailability[variantId]) {
                            pricingWithAvailability[variantId] = {};
                        }
                        if (!pricingWithAvailability[variantId][dateString]) {
                            pricingWithAvailability[variantId][dateString] = {};
                        }

                        // Update with per-slot availability data
                        pricingWithAvailability[variantId][dateString] = {
                            ...pricingWithAvailability[variantId][dateString],
                            available_capacity: data.available_quantity,
                            total_available: data.available_quantity,
                            total_booked: data.total_booked,
                            final_price: data.final_price,
                            is_available: data.available,
                            slot_id: slotId
                        };

                        console.log('Updated pricingWithAvailability for', variantId, ':',
                            pricingWithAvailability[variantId][dateString]);

                        // Update availability display for this specific variant
                        updateAvailabilityDisplay(packageName, type, data);

                        // Update quantity button states for this variant
                        const qtyElement = document.getElementById(`${packageName}-${type}-qty`);
                        if (qtyElement) {
                            const currentQty = parseInt(qtyElement.textContent) || 0;
                            updateQuantityButtonStates(packageName, type, currentQty, data.available_quantity);
                        }

                        // Store data for this package total calculation
                        if (!availabilityByPackage[packageName]) {
                            availabilityByPackage[packageName] = {};
                        }
                        availabilityByPackage[packageName][type] = data.available_quantity || 0;

                        // Update main package availability with package-specific totals
                        updateMainPackageAvailability(packageName, availabilityByPackage[packageName]);

                        console.log('Availability update completed for', packageName, type, ':', data
                            .available_quantity);

                        // Track completion
                        completedRequests++;
                        if (completedRequests >= totalRequests) {
                            hidePackagesLoading();
                        }
                    })
                    .catch((error) => {
                        console.error('Error fetching availability for', variantId, ':', error);

                        // Track completion even on error
                        completedRequests++;
                        if (completedRequests >= totalRequests) {
                            hidePackagesLoading();
                        }
                    });
            });
        }

        // Function to update main package availability
        function updateMainPackageAvailability(packageName, availabilityData) {
            console.log('updateMainPackageAvailability called:', {
                packageName,
                availabilityData
            });

            const packageCard = document.querySelector(`[data-vehicle="${packageName}"]`);
            if (!packageCard) {
                console.log('Package card not found for:', packageName);
                return;
            }

            // Use the maximum available among variants to avoid double-counting the same vehicle pool
            const totalAvailable = Object.values(availabilityData).reduce((maxV, qty) => Math.max(maxV, qty || 0), 0);
            console.log('Total available for', packageName, ':', totalAvailable);

            // Persist per-slot aggregated availability for this package
            slotPackageAvailability[packageName] = totalAvailable;

            // Update header availability text
            const mainAvailabilityElement = packageCard.querySelector('.vehicle-info .availability');
            if (mainAvailabilityElement) {
                if (totalAvailable > 0) {
                    mainAvailabilityElement.textContent = `Available: ${totalAvailable}`;
                    mainAvailabilityElement.style.color = '#28a745';
                } else {
                    mainAvailabilityElement.textContent = 'Only 0 available';
                    mainAvailabilityElement.style.color = '#dc3545';
                }
            }

            // Update the big number to reflect this slot's total and set as the new base
            const mainCountElement = packageCard.querySelector('.vehicle-info .number');
            if (mainCountElement) {
                mainCountElement.textContent = totalAvailable;
                mainCountElement.setAttribute('data-original-count', String(totalAvailable));
            }

            // Recompute remaining after updating totals
            updateMainVehicleCount(packageName);
        }

        // Enhanced quantity button functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('quantity-btn')) {
                const button = e.target;
                const action = button.getAttribute('data-action');
                const type = button.getAttribute('data-type');
                const packageCard = button.closest('.vehicle-card');
                const packageName = packageCard.getAttribute('data-vehicle');

                // Find the quantity element
                const qtyElement = document.getElementById(`${packageName}-${type}-qty`);
                if (qtyElement) {
                    let currentQty = parseInt(qtyElement.textContent) || 0;

                    // Find variant ID for this package and type
                    const riderCard = button.closest('.rider-option-card');
                    const variantId = riderCard.getAttribute('data-variant-id');

                    // Get available capacity for this variant
                    let availableCapacity = 0;
                    if (variantId && selectedDate && selectedTimeSlot) {
                        const dateString = selectedDate.toISOString().split('T')[0];
                        const pricingData = pricingWithAvailability[variantId]?.[dateString];
                        if (pricingData && pricingData.slot_id === selectedTimeSlot) {
                            // Use per-slot availability
                            availableCapacity = pricingData.available_capacity || 0;
                        } else {
                            // Fallback to global availability if per-slot data not available
                            availableCapacity = pricingData?.available_capacity || 0;
                        }
                    }

                    if (action === 'increment') {
                        // Get the total available vehicles for this package from the original count
                        const packageHeader = packageCard.querySelector('.vehicle-info .number');
                        const totalAvailableVehicles = parseInt(packageHeader.getAttribute(
                            'data-original-count')) || 0;

                        // Calculate currently selected vehicles across all variants
                        let currentlySelectedVehicles = 0;
                        if (selectedVehicles[packageName]) {
                            Object.keys(selectedVehicles[packageName]).forEach(selectedType => {
                                currentlySelectedVehicles += selectedVehicles[packageName][selectedType]
                                    .qty || 0;
                            });
                        }

                        // Check if we can increment without exceeding total available vehicles for the package
                        if ((currentlySelectedVehicles + 1) <= totalAvailableVehicles) {
                            currentQty++;
                        } else {
                            // Show warning that maximum capacity reached for this package
                            if (totalAvailableVehicles === 0) {
                                toastNotifications.warning('No vehicles available for this package.');
                            } else {
                                toastNotifications.warning(
                                    `Maximum ${totalAvailableVehicles} vehicles can be selected across all options.`
                                );
                            }
                            return;
                        }
                    } else if (action === 'decrement' && currentQty > 0) {
                        currentQty--;
                    }

                    qtyElement.textContent = currentQty;

                    // Update selected vehicles state
                    if (!selectedVehicles[packageName]) {
                        selectedVehicles[packageName] = {};
                    }

                    if (currentQty > 0) {
                        selectedVehicles[packageName][type] = {
                            qty: currentQty,
                            variantId: variantId
                        };
                    } else {
                        delete selectedVehicles[packageName][type];
                    }


                    // Immediately update button states for this specific variant
                    updateQuantityButtonStates(packageName, type, currentQty, availableCapacity);

                    // Force update button states after a short delay to ensure DOM is updated
                    setTimeout(() => {
                        updateQuantityButtonStates(packageName, type, currentQty, availableCapacity);

                        // Force update all quantity button states
                        forceUpdateAllQuantityButtonStates();
                    }, 50);

                    updateVehicleTotals(packageName);
                    checkAvailabilityForVehicle(packageName, type, currentQty);

                    // Update availability and button states for all variants in this package
                    updateAllVariantsAvailability(packageName);

                    // Update the main vehicle count display
                    updateMainVehicleCount(packageName);
                }
            }
        });

        // Function to update quantity button states with synchronized availability
        function updateQuantityButtonStates(packageName, type, currentQty, availableCapacity) {
            const packageCard = document.querySelector(`[data-vehicle="${packageName}"]`);
            if (!packageCard) return;

            // Get total available vehicles for this package from the original count
            const packageHeader = packageCard.querySelector('.vehicle-info .number');
            const totalAvailableVehicles = parseInt(packageHeader.getAttribute('data-original-count')) || 0;



            // Find the specific rider card for this type
            const riderCards = packageCard.querySelectorAll('.rider-option-card');
            let targetRiderCard = null;

            for (let card of riderCards) {
                const qtyElement = card.querySelector('.quantity-value');
                if (qtyElement && qtyElement.id === `${packageName}-${type}-qty`) {
                    targetRiderCard = card;
                    break;
                }
            }

            if (!targetRiderCard) return;

            const incrementBtn = targetRiderCard.querySelector(`[data-action="increment"][data-type="${type}"]`);
            const decrementBtn = targetRiderCard.querySelector(`[data-action="decrement"][data-type="${type}"]`);

            if (incrementBtn) {
                // Check if this is a day off by looking for day off indicator
                const capacityInfo = targetRiderCard.querySelector('.capacity-info');
                const isDayOff = capacityInfo && capacityInfo.textContent.includes('Day Off');

                // Calculate currently selected vehicles across all variants for this package
                let currentlySelectedVehicles = 0;
                if (selectedVehicles[packageName]) {
                    Object.keys(selectedVehicles[packageName]).forEach(selectedType => {
                        currentlySelectedVehicles += selectedVehicles[packageName][selectedType].qty || 0;
                    });
                }

                // Check if we can increment without exceeding total available vehicles for the package
                const wouldExceedLimit = (currentlySelectedVehicles + 1) > totalAvailableVehicles;

                if (isDayOff || wouldExceedLimit || totalAvailableVehicles === 0) {
                    incrementBtn.classList.add('disabled');
                    incrementBtn.disabled = true;
                } else {
                    incrementBtn.classList.remove('disabled');
                    incrementBtn.disabled = false;
                }
            }

            if (decrementBtn) {
                // Decrement button should be enabled if quantity > 0, regardless of total available capacity
                if (currentQty <= 0) {
                    decrementBtn.classList.add('disabled');
                    decrementBtn.disabled = true;
                } else {
                    decrementBtn.classList.remove('disabled');
                    decrementBtn.disabled = false;
                }
            }

            // Update the capacity info to show total available vehicles for this variant
            const capacityInfo = targetRiderCard.querySelector('.capacity-info');
            if (capacityInfo) {
                if (totalAvailableVehicles > 0) {
                    capacityInfo.textContent = `Max: ${totalAvailableVehicles}`;
                    capacityInfo.style.display = 'block';
                } else {
                    capacityInfo.style.display = 'none';
                }
            }
        }

        // Function to update availability for all variants in a package
        function updateAllVariantsAvailability(packageName) {
            const packageCard = document.querySelector(`[data-vehicle="${packageName}"]`);
            if (!packageCard) return;

            // Get total available vehicles for this package
            const packageHeader = packageCard.querySelector('.vehicle-info .number');
            const totalAvailableVehicles = parseInt(packageHeader.getAttribute('data-original-count')) || 0;

            // Update each variant's availability and button states
            const allRiderCards = packageCard.querySelectorAll('.rider-option-card');
            allRiderCards.forEach(card => {
                const qtyElement = card.querySelector('.quantity-value');
                if (!qtyElement) return;

                const qtyId = qtyElement.id;
                const typeMatch = qtyId.match(new RegExp(`${packageName}-(single|double)-qty`));
                if (!typeMatch) return;

                const type = typeMatch[1];
                const currentQty = parseInt(qtyElement.textContent) || 0;

                // Update button states with the total available capacity
                // Use a higher capacity value to ensure buttons work correctly
                const effectiveCapacity = Math.max(totalAvailableVehicles, currentQty + 1);
                updateQuantityButtonStates(packageName, type, currentQty, effectiveCapacity);

                // Update capacity info to show total available for this variant
                const capacityInfo = card.querySelector('.capacity-info');
                if (capacityInfo) {
                    if (totalAvailableVehicles > 0) {
                        capacityInfo.textContent = `Max: ${totalAvailableVehicles}`;
                        capacityInfo.style.display = 'block';
                    } else {
                        capacityInfo.style.display = 'none';
                    }
                }
            });

            // Update the main vehicle count display
            updateMainVehicleCount(packageName);
        }

        // Function to update pricing for selected date
        function updatePricingForDate(dateString) {
            // Update pricing for all packages
            packages.forEach(package => {
                const packageName = package.name.toLowerCase();
                if (selectedVehicles[packageName]) {
                    Object.keys(selectedVehicles[packageName]).forEach(type => {
                        const vehicleData = selectedVehicles[packageName][type];
                        if (vehicleData.variantId) {
                            updatePricingForVariantFromData(dateString, vehicleData.variantId, packageName,
                                type);
                        }
                    });
                }
            });

            // Also update all visible pricing displays
            updateAllPricingDisplays(dateString);
        }

        // Function to update all pricing displays for a specific date
        async function updateAllPricingDisplays(dateString) {
            console.log('Updating pricing displays for date:', dateString);

            // Show subtle loading for pricing updates
            showPackagesLoading();

            try {
                // Always fetch fresh availability data for all variants instead of using cached data
                const availabilityPromises = packageVariants.map(async (variant) => {
                    if (selectedTimeSlot) {
                        // Fetch fresh availability data for this variant and time slot
                        const freshData = await getFreshAvailabilityForVariant(variant.id, dateString,
                            selectedTimeSlot);
                        if (freshData) {
                            updatePricingDisplayForVariant(variant.id, freshData);
                        }
                    }
                });

                // Wait for all availability data to be fetched
                await Promise.all(availabilityPromises);
            } catch (error) {
                console.error('Error updating pricing displays:', error);
            } finally {
                // Hide subtle loading
                hidePackagesLoading();
            }
        }

        // Function to update pricing display for a specific variant
        function updatePricingDisplayForVariant(variantId, availabilityData) {
            console.log(`Updating pricing display for variant ${variantId}:`, availabilityData);

            // Find the rider card for this variant
            const riderCard = document.querySelector(`[data-variant-id="${variantId}"]`);
            if (!riderCard) {
                console.log(`Rider card not found for variant ${variantId}`);
                return;
            }

            const priceElement = riderCard.querySelector('.price');
            if (!priceElement) {
                console.log(`Price element not found for variant ${variantId}`);
                return;
            }

            // Build the new price HTML
            let priceHTML = '';

            // Add price tag if available
            if (availabilityData.price_tag) {
                priceHTML +=
                    `<span class="price-tag ${availabilityData.price_tag}">${availabilityData.price_tag.charAt(0).toUpperCase() + availabilityData.price_tag.slice(1)}</span> `;
            }

            // Add original price if discounted
            if (availabilityData.default_price > availabilityData.final_price) {
                priceHTML += `<span class="original">TK ${availabilityData.default_price.toLocaleString()}</span> `;
            }

            // Add final price
            priceHTML += `TK ${availabilityData.final_price.toLocaleString()}`;

            // Removed unavailable badges - only show price

            // Update the price element
            priceElement.innerHTML = priceHTML;

            // Update availability display
            const availabilityElement = riderCard.querySelector('.availability');
            if (availabilityElement) {
                if (availabilityData.available_quantity > 0) {
                    availabilityElement.textContent = `Available: ${availabilityData.available_quantity}`;
                    availabilityElement.style.color = '#28a745';
                } else {
                    availabilityElement.textContent = 'Only 0 available';
                    availabilityElement.style.color = '#dc3545';
                }
            }

            // Update capacity info in quantity selector
            const quantitySelector = riderCard.querySelector('.quantity-selector');
            if (quantitySelector) {
                let capacityInfo = quantitySelector.querySelector('.capacity-info');

                // Remove existing capacity info
                if (capacityInfo) {
                    capacityInfo.remove();
                }

                // Create new capacity info based on availability
                if (availabilityData.is_day_off) {
                    capacityInfo = document.createElement('div');
                    capacityInfo.className = 'capacity-info text-danger';
                    capacityInfo.textContent = 'Day Off';
                    quantitySelector.appendChild(capacityInfo);
                } else if (availabilityData.available_quantity > 0) {
                    capacityInfo = document.createElement('div');
                    capacityInfo.className = 'capacity-info';
                    capacityInfo.textContent = `Max: ${availabilityData.available_quantity}`;
                    quantitySelector.appendChild(capacityInfo);
                } else {
                    capacityInfo = document.createElement('div');
                    capacityInfo.className = 'capacity-info text-muted';
                    capacityInfo.textContent = 'Unavailable';
                    quantitySelector.appendChild(capacityInfo);
                }
            }
        }

        // Function to update pricing using server data
        function updatePricingForVariantFromData(dateString, variantId, packageName, type) {
            const pricingData = pricingWithAvailability[variantId]?.[dateString];
            if (pricingData) {
                updateVehiclePricingWithData(packageName, type, pricingData);
            } else {
                // Fallback to API call if data not available
                fetchPricingForVariant(dateString, variantId, packageName, type);
            }
        }

        // Fallback function for API calls
        function fetchPricingForVariant(dateString, variantId, packageName, type) {
            fetch(`/api/pricing/date?date=${dateString}&variant_id=${variantId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.final_price !== undefined) {
                        updateVehiclePricingWithData(packageName, type, data);
                    }
                })
                .catch(error => {
                    // Error fetching pricing
                });
        }

        // Function to check availability for a vehicle
        function checkAvailabilityForVehicle(packageName, type, quantity) {
            if (!selectedDate || !selectedTimeSlot || quantity === 0) return;

            const packageCard = document.querySelector(`[data-vehicle="${packageName}"]`);
            const riderCard = packageCard.querySelector(
                `[data-variant-id="${selectedVehicles[packageName][type].variantId}"]`);
            const variantId = riderCard.getAttribute('data-variant-id');
            const slotId = selectedTimeSlot;
            const dateString = selectedDate.toISOString().split('T')[0];

            fetch(
                    `/api/availability/check?date=${dateString}&variant_id=${variantId}&slot_id=${slotId}&quantity=${quantity}`
                )
                .then(response => response.json())
                .then(data => {
                    updateAvailabilityDisplay(packageName, type, data);
                })
                .catch(error => {
                    // Error checking availability
                });
        }

        // Function to update availability display
        function updateAvailabilityDisplay(packageName, type, availabilityData) {
            console.log('updateAvailabilityDisplay called:', {
                packageName,
                type,
                availabilityData
            });

            const packageCard = document.querySelector(`[data-vehicle="${packageName}"]`);
            console.log('packageCard found:', !!packageCard);

            // Find the specific rider card for this type
            const riderCards = packageCard.querySelectorAll('.rider-option-card');
            let targetRiderCard = null;

            for (let card of riderCards) {
                const qtyElement = card.querySelector('.quantity-value');
                if (qtyElement && qtyElement.id === `${packageName}-${type}-qty`) {
                    targetRiderCard = card;
                    break;
                }
            }

            console.log('targetRiderCard found:', !!targetRiderCard);

            if (targetRiderCard) {
                // Update the availability badge in the price section
                const priceElement = targetRiderCard.querySelector('.price');
                console.log('priceElement found:', !!priceElement);

                if (priceElement) {
                    // Removed unavailable badge handling - only show price
                }

                // Update the max quantity display to show total available vehicles for this variant
                const maxElement = targetRiderCard.querySelector('.capacity-info');
                const packageCard = targetRiderCard.closest('.vehicle-card');
                const packageHeader = packageCard.querySelector('.vehicle-info .number');
                const totalAvailableVehicles = parseInt(packageHeader.getAttribute('data-original-count')) || 0;

                // Calculate currently selected vehicles across all variants (excluding current variant)
                const packageName = packageCard.getAttribute('data-vehicle');
                let availabilitySelectedVehicles = 0;
                if (selectedVehicles[packageName]) {
                    Object.keys(selectedVehicles[packageName]).forEach(selectedType => {
                        if (selectedType !== type) { // Don't count the current variant
                            availabilitySelectedVehicles += selectedVehicles[packageName][selectedType].qty || 0;
                        }
                    });
                }

                const availabilityRemainingForThisVariant = Math.max(0, totalAvailableVehicles -
                    availabilitySelectedVehicles);

                if (maxElement) {
                    maxElement.textContent = `Max: ${availabilityRemainingForThisVariant}`;
                    console.log('Updated existing capacity info:', availabilityRemainingForThisVariant);
                } else if (availabilityRemainingForThisVariant > 0) {
                    // Create capacity info if it doesn't exist
                    const quantitySelector = targetRiderCard.querySelector('.quantity-selector');
                    if (quantitySelector) {
                        const newCapacityInfo = document.createElement('div');
                        newCapacityInfo.className = 'capacity-info';
                        newCapacityInfo.textContent = `Max: ${availabilityRemainingForThisVariant}`;
                        quantitySelector.appendChild(newCapacityInfo);
                        console.log('Created new capacity info:', availabilityRemainingForThisVariant);
                    }
                }

                // Also update all other variants in this package to reflect the new distribution
                updateAllVariantsAvailability(packageName);
            }
        }

        // Function to fetch fresh availability data for a specific date and variant
        async function fetchFreshAvailability(variantId, dateString, slotId) {
            try {
                const response = await fetch(
                    `/api/availability/check?date=${dateString}&variant_id=${variantId}&slot_id=${slotId}&quantity=1`
                );
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return await response.json();
            } catch (error) {
                console.error('Error fetching fresh availability:', error);
                return null;
            }
        }

        // Enhanced vehicle totals function
        function updateVehicleTotals(packageName) {
            const packageCard = document.querySelector(`[data-vehicle="${packageName}"]`);
            if (!packageCard) return;

            let totalVehicles = 0;
            let totalPrice = 0;

            // Get all quantity elements for this package
            const qtyElements = packageCard.querySelectorAll('.quantity-value');
            qtyElements.forEach(qtyElement => {
                const qty = parseInt(qtyElement.textContent) || 0;
                totalVehicles += qty;

                // Find the variant ID and get correct pricing
                const riderCard = qtyElement.closest('.rider-option-card');
                const variantId = riderCard.getAttribute('data-variant-id');

                if (variantId && selectedDate) {
                    const dateString = selectedDate.toISOString().split('T')[0];
                    const pricingData = pricingWithAvailability[variantId]?.[dateString];

                    if (pricingData) {
                        // Use per-slot pricing if available, otherwise fallback to global pricing
                        const price = pricingData.final_price || 0;
                        totalPrice += qty * price;
                    }
                }
            });

            // Update totals
            const totalVehiclesElement = document.getElementById(`${packageName}-total-vehicles`);
            const totalPriceElement = document.getElementById(`${packageName}-total-price`);

            if (totalVehiclesElement) {
                totalVehiclesElement.textContent = totalVehicles > 0 ? `${totalVehicles} ${packageName.toUpperCase()}` :
                    '0';
            }
            if (totalPriceElement) {
                totalPriceElement.textContent = `TK ${totalPrice.toFixed(2)}`;
            }

            // Update summary list
            updateSummaryList(packageName, packageCard);
        }

        // Function to update the main vehicle count display
        function updateMainVehicleCount(packageName) {
            const packageCard = document.querySelector(`[data-vehicle="${packageName}"]`);
            if (!packageCard) return;

            // Get the main vehicle count element
            const mainCountElement = packageCard.querySelector('.vehicle-info .number');
            if (!mainCountElement) return;

            // Get original available count - prioritize data-original-count as it's the server-calculated value
            let totalAvailableVehicles = 0;
            const originalCount = parseInt(mainCountElement.getAttribute('data-original-count') || 0);
            if (originalCount > 0) {
                totalAvailableVehicles = originalCount;
            } else if (slotPackageAvailability[packageName] !== undefined) {
                totalAvailableVehicles = parseInt(slotPackageAvailability[packageName]) || 0;
            } else {
                totalAvailableVehicles = 0;
            }

            // Calculate currently selected vehicles across all variants
            let mainSelectedVehicles = 0;
            if (selectedVehicles[packageName]) {
                Object.keys(selectedVehicles[packageName]).forEach(selectedType => {
                    const qty = selectedVehicles[packageName][selectedType].qty || 0;
                    mainSelectedVehicles += qty;
                });
            }

            // Show remaining available vehicles (total available minus selected)
            const remainingVehicles = Math.max(0, totalAvailableVehicles - mainSelectedVehicles);
            mainCountElement.textContent = remainingVehicles;

            // Update the availability text
            const availabilityElement = packageCard.querySelector('.vehicle-info .availability');
            if (availabilityElement) {
                if (remainingVehicles > 0) {
                    availabilityElement.textContent = 'Available';
                    availabilityElement.style.color = '#28a745';
                } else {
                    availabilityElement.textContent = 'No vehicles available';
                    availabilityElement.style.color = '#dc3545';
                }
            }
        }

        // Function to update summary list
        function updateSummaryList(packageName, packageCard) {
            const summaryList = document.getElementById(`${packageName}-summary-list`);
            if (!summaryList) return;

            summaryList.innerHTML = '';

            const riderCards = packageCard.querySelectorAll('.rider-option-card');
            riderCards.forEach(card => {
                const qtyElement = card.querySelector('.quantity-value');
                const qty = parseInt(qtyElement.textContent) || 0;

                if (qty > 0) {
                    const titleElement = card.querySelector('.rider-title');
                    const helmetIcons = card.querySelector('.rider-icon').innerHTML;
                    const variantId = card.getAttribute('data-variant-id');

                    if (titleElement && variantId) {
                        const title = titleElement.textContent;
                        const isDouble = helmetIcons.includes('Helmet.svg') && (helmetIcons.match(/Helmet\.svg/g) ||
                            []).length > 1;

                        // Get correct pricing for selected date
                        let lineTotal = 0;
                        if (selectedDate) {
                            const dateString = selectedDate.toISOString().split('T')[0];
                            const pricingData = pricingWithAvailability[variantId]?.[dateString];

                            if (pricingData) {
                                lineTotal = qty * pricingData.final_price;
                            }
                        }

                        summaryList.innerHTML += `
                <div class="summary-item" style="background-color: #f5f5f5;padding:${isDouble ? '9%' : '7%'} 2%; border-radius:5%">
                    <div class="d-flex flex-column align-items-start">
                        <span class="rider-icon ${isDouble ? 'd-flex' : ''}">${helmetIcons}</span>
                        <span class="summary-item-title">${title}</span>
                    </div>
                    <div class="summary-item-details">
                        <div># of ${packageName.toUpperCase()} <strong>${qty}</strong></div>
                        <div class="price-line">Tk ${lineTotal.toFixed(2)}</div>
                    </div>
                </div>`;
                    }
                }
            });
        }

        // Function to add selected packages to cart and show modal
        window.addToCartAndShowModal = function() {
            // addToCartAndShowModal function called

            // Check if any packages are selected
            let hasSelectedPackages = false;
            let cartData = [];

            packages.forEach(package => {
                const packageName = package.name.toLowerCase();
                // Checking package
                if (selectedVehicles[packageName]) {
                    Object.keys(selectedVehicles[packageName]).forEach(type => {
                        const vehicleData = selectedVehicles[packageName][type];
                        const qty = vehicleData.qty || 0;
                        // Vehicle data logged

                        if (qty > 0 && vehicleData.variantId && selectedDate && selectedTimeSlot) {
                            hasSelectedPackages = true;
                            cartData.push({
                                variant_id: vehicleData.variantId,
                                quantity: qty,
                                date: selectedDate.toISOString().split('T')[0],
                                slot_id: selectedTimeSlot
                            });
                            // Added to cart data
                        }
                    });
                }
            });

            // Has selected packages and cart data logged

            if (!hasSelectedPackages) {
                toastNotifications.warning('Please select at least one package and time slot before proceeding.');
                return;
            }

            // Store cart data temporarily and show acknowledgment modal
            pendingCartData = cartData;

            // Reset acknowledgment form
            document.getElementById('acknowledgment1').checked = false;
            document.getElementById('acknowledgment2').checked = false;
            clearSignature();

            // Show acknowledgment modal
            const modal = new bootstrap.Modal(document.getElementById('acknowledgmentModal'));
            modal.show();
        };

        // Function to close modal
        window.closeModal = function() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('successModal'));
            if (modal) {
                modal.hide();
            } else {
                // Fallback if Bootstrap modal instance is not available
                const modalElement = document.getElementById('successModal');
                if (modalElement) {
                    modalElement.style.display = 'none';
                    modalElement.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }
            }
        };

        // Helper function to update vehicle pricing with data
        function updateVehiclePricingWithData(packageName, type, pricingData) {
            // This function can be used to update pricing when data is available
            // Updating pricing
        }

        // Function to get fresh availability data for a variant and date
        async function getFreshAvailabilityForVariant(variantId, dateString, slotId) {
            // Always fetch fresh data from API instead of using cached data
            const freshData = await fetchFreshAvailability(variantId, dateString, slotId);
            if (freshData) {
                // Update the cached data with fresh information
                if (!pricingWithAvailability[variantId]) {
                    pricingWithAvailability[variantId] = {};
                }
                if (!pricingWithAvailability[variantId][dateString]) {
                    pricingWithAvailability[variantId][dateString] = {};
                }

                // Preserve the is_day_off field from the original data
                const originalData = pricingWithAvailability[variantId][dateString];
                const isDayOff = originalData ? originalData.is_day_off : false;

                pricingWithAvailability[variantId][dateString] = {
                    ...pricingWithAvailability[variantId][dateString],
                    available_capacity: freshData.available_quantity,
                    total_available: freshData.available_quantity,
                    total_booked: freshData.total_booked,
                    final_price: freshData.final_price,
                    is_available: freshData.available,
                    is_day_off: isDayOff, // Preserve the day off status
                    slot_id: slotId
                };

                // Return the updated data with is_day_off preserved
                return {
                    ...freshData,
                    is_day_off: isDayOff
                };
            }
            return null;
        }

        // Function to check cart status and update modal button visibility
        function checkCartStatusAndUpdateModal() {
            fetch('{{ route('frontend.cart.status') }}')
                .then(response => response.json())
                .then(data => {
                    const checkoutSection = document.getElementById('checkout-section');
                    const cartSection = document.getElementById('cart-section');

                    if (data.has_items) {
                        // Show checkout button if cart has items
                        checkoutSection.style.display = 'block';
                        cartSection.style.display = 'none';
                    } else {
                        // Show cart button if cart is empty
                        checkoutSection.style.display = 'none';
                        cartSection.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error checking cart status:', error);
                    // Default to showing cart button on error
                    const checkoutSection = document.getElementById('checkout-section');
                    const cartSection = document.getElementById('cart-section');
                    checkoutSection.style.display = 'none';
                    cartSection.style.display = 'block';
                });
        }

        // ===== ACKNOWLEDGMENT MODAL FUNCTIONALITY =====

        // Global variables for acknowledgment
        let pendingCartData = [];
        let signatureCanvas = null;
        let signatureCtx = null;
        let isDrawing = false;
        let signatureData = null;

        // Initialize signature canvas when modal is shown
        function initSignatureCanvas() {
            signatureCanvas = document.getElementById('signatureCanvas');
            if (signatureCanvas) {
                signatureCtx = signatureCanvas.getContext('2d');
                signatureCtx.strokeStyle = '#000000';
                signatureCtx.lineWidth = 2;
                signatureCtx.lineCap = 'round';
                signatureCtx.lineJoin = 'round';

                // Mouse events
                signatureCanvas.addEventListener('mousedown', startDrawing);
                signatureCanvas.addEventListener('mousemove', draw);
                signatureCanvas.addEventListener('mouseup', stopDrawing);
                signatureCanvas.addEventListener('mouseout', stopDrawing);

                // Touch events for mobile
                signatureCanvas.addEventListener('touchstart', handleTouch);
                signatureCanvas.addEventListener('touchmove', handleTouch);
                signatureCanvas.addEventListener('touchend', stopDrawing);
            }
        }

        // Touch handling for mobile devices
        function handleTouch(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' :
                                            e.type === 'touchmove' ? 'mousemove' : 'mouseup', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            signatureCanvas.dispatchEvent(mouseEvent);
        }

        function startDrawing(e) {
            isDrawing = true;
            const rect = signatureCanvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            signatureCtx.beginPath();
            signatureCtx.moveTo(x, y);
        }

        function draw(e) {
            if (!isDrawing) return;
            const rect = signatureCanvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            signatureCtx.lineTo(x, y);
            signatureCtx.stroke();
        }

        function stopDrawing() {
            if (isDrawing) {
                isDrawing = false;
                signatureCtx.beginPath();
                // Check if there's any drawing on canvas
                const imageData = signatureCtx.getImageData(0, 0, signatureCanvas.width, signatureCanvas.height);
                const hasContent = imageData.data.some(channel => channel !== 0);
                if (hasContent) {
                    signatureData = signatureCanvas.toDataURL('image/png');
                    updateSignatureStatus(true);
                }
            }
        }

        // Handle signature upload
        function handleSignatureUpload() {
            const fileInput = document.getElementById('signatureUpload');
            const file = fileInput.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    signatureData = e.target.result;
                    // Show preview
                    const preview = document.getElementById('signaturePreview');
                    const previewImg = document.getElementById('signaturePreviewImg');
                    previewImg.src = signatureData;
                    preview.style.display = 'block';
                    updateSignatureStatus(true);
                };
                reader.readAsDataURL(file);
            }
        }

        // Clear signature
        function clearSignature() {
            if (signatureCanvas && signatureCtx) {
                signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
            }
            signatureData = null;
            document.getElementById('signatureUpload').value = '';
            document.getElementById('signaturePreview').style.display = 'none';
            updateSignatureStatus(false);
        }

        // Update signature status display
        function updateSignatureStatus(hasSignature) {
            const statusElement = document.getElementById('signatureStatus');
            if (hasSignature) {
                statusElement.innerHTML = '<small class="text-success"><i class="bi  bi-check me-1"></i>Signature provided</small>';
            } else {
                statusElement.innerHTML = '<small class="text-muted">No signature provided</small>';
            }
            validateAcknowledgment();
        }

        // Validate acknowledgment (checkboxes + signature)
        function validateAcknowledgment() {
            const checkbox1 = document.getElementById('acknowledgment1').checked;
            const checkbox2 = document.getElementById('acknowledgment2').checked;
            const hasSignature = signatureData !== null;

            const submitBtn = document.getElementById('submitAcknowledgmentBtn');
            if (checkbox1 && checkbox2 && hasSignature) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }

        // Submit acknowledgment and add to cart
        function submitAcknowledgment() {
            const acknowledgmentData = {
                driver_license_requirement: document.getElementById('acknowledgment1').checked,
                license_show_requirement: document.getElementById('acknowledgment2').checked
            };

            // Add acknowledgment data to cart items
            const cartDataWithAcknowledgment = pendingCartData.map(item => ({
                ...item,
                acknowledgment_data: acknowledgmentData,
                signature_data: signatureData
            }));

            // Add to cart via AJAX
            fetch('{{ route('frontend.cart.add') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(cartDataWithAcknowledgment)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    if (typeof updateCartCount === 'function') {
                        updateCartCount();
                    }

                    // Close acknowledgment modal
                    const acknowledgmentModal = bootstrap.Modal.getInstance(document.getElementById('acknowledgmentModal'));
                    acknowledgmentModal.hide();

                    // Show success modal
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                } else {
                    toastNotifications.error('Error adding packages to cart: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastNotifications.error('Error adding packages to cart. Please try again.');
            });
        }

        // Close success modal
        function closeSuccessModal() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('successModal'));
            if (modal) {
                modal.hide();
            }
        }

        // Event listeners for acknowledgment checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.acknowledgment-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', validateAcknowledgment);
            });

            // Signature upload handler
            const signatureUpload = document.getElementById('signatureUpload');
            if (signatureUpload) {
                signatureUpload.addEventListener('change', handleSignatureUpload);
            }

            // Initialize signature canvas when acknowledgment modal is shown
            const acknowledgmentModal = document.getElementById('acknowledgmentModal');
            if (acknowledgmentModal) {
                acknowledgmentModal.addEventListener('shown.bs.modal', function() {
                    initSignatureCanvas();
                });
            }
        });
    </script>
@endpush
