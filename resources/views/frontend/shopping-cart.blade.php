@extends('layouts.frontend')

@section('title', 'Continue Purchase')

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontEnd/css/shopping-cart.css') }}">
    <style>
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-close {
            background: none;
            border: none;
            color: inherit;
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }

        .btn-close:hover {
            opacity: 1;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart i {
            font-size: 4em;
            color: #ddd;
            margin-bottom: 20px;
        }

        /* ATV Purchase Container - Following cart-2.html exactly */
        .atv-purchase-container {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .atv-purchase-container:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .atv-header {
            background-color: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .atv-header .report {
            color: #ff6f00;
            font-weight: 600;
        }

        .atv-body {
            padding: 1.5rem;
        }

        .atv-image-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .atv-image-container h5 {
            color: #333;
            margin-bottom: 1rem;
        }

        .atv-details-container {
            margin-bottom: 1.5rem;
        }

        .atv-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .atv-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        .atv-option:hover {
            background-color: #e9ecef;
        }

        .atv-option-info-left {
            display: flex;
            align-items: flex-start;
            gap: 2px;
        }

        .icons {
            display: flex;
            gap: 0.5rem;
        }

        .text-content {
            font-weight: 600;
            color: #333;
        }

        .atv-option-info-right {
            text-align: right;
        }

        .atv-option-info-right small {
            color: #6c7280;
            display: block;
            margin-bottom: 0.5rem;
        }

        .price {
            font-weight: 700;
            color: #059669;
            font-size: 1.1rem;
        }

        .atv-total-section {
            background-color: #FFF4EF;
            padding: 1rem;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .atv-total-section strong {
            color: #333;
        }

        .total-price {
            font-weight: 700;
            color: #dc2626;
            font-size: 1.2rem;
        }

        .atv-action-icons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .atv-action-icons i:hover:nth-child(1) {
            background-color: transparent;
        }

        .atv-action-icons i:hover:nth-child(2) {
            background-color: #000;
        }

        .edit-icon,
        .remove-icon {
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .edit-icon {
            color: #007bff;
        }

        .edit-icon:hover {
            background-color: #e3f2fd;
        }

        .remove-icon {
            color: #dc3545;
        }

        .remove-icon:hover {
            background-color: #fce7f3;
        }

        /* Cart Item - Following cart-2.html exactly */
        .cart-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .cart-item img {
            border-radius: 4px;
        }

        .cart-item .text-danger {
            color: #dc2626 !important;
        }

        .cart-item input[type="number"] {
            width: 60px !important;
            display: inline-block !important;
        }

        /* Quantity Controls Styling */
        .quantity-controls {
            display: inline-flex;
            align-items: center;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            overflow: hidden;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            padding: 0;
            border: none;
            background-color: #f8f9fa;
            color: #495057;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-btn:hover:not(:disabled) {
            background-color: #e9ecef;
            color: #212529;
        }

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .quantity-input {
            border: none !important;
            border-left: 1px solid #dee2e6 !important;
            border-right: 1px solid #dee2e6 !important;
            border-radius: 0 !important;
            height: 30px;
            margin: 0;
            padding: 0 8px;
            text-align: center;
            font-weight: 600;
        }

        .quantity-input:focus {
            box-shadow: none !important;
            border-color: #007bff !important;
        }

        /* Modal Quantity Controls Styling */
        .quantity-controls-large {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        .quantity-btn-large {
            width: 40px;
            height: 40px;
            padding: 0;
            border: none;
            background-color: #ffffff;
            color: #495057;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }

        .quantity-btn-large:hover:not(:disabled) {
            background-color: #e9ecef;
            color: #212529;
            transform: scale(1.05);
        }

        .quantity-btn-large:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .quantity-input-large {
            border: none !important;
            border-left: 2px solid #dee2e6 !important;
            border-right: 2px solid #dee2e6 !important;
            border-radius: 0 !important;
            height: 40px;
            margin: 0;
            padding: 0 12px;
            text-align: center;
            font-weight: 600;
            font-size: 1.1rem;
            background-color: #ffffff;
        }

        .quantity-input-large:focus {
            box-shadow: none !important;
            border-color: #007bff !important;
        }

        .total-preview {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }

        /* Promo Section - Following cart-2.html exactly */
        .promo-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .btn-apply {
            background-color: #ff6f00;
            border-color: #ff6f00;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-apply:hover {
            background-color: #e66000;
            border-color: #e66000;
            transform: scale(1.05);
        }

        /* Order Summary - Following cart-2.html exactly */
        .order-summary {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .order-summary h4 {
            color: #333;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        .order-summary .border-bottom {
            border-color: #e5e7eb !important;
        }

        .promo-discount {
            color: #ff6f00;
            font-weight: 600;
        }

        .total-amount {
            color: #ff6f00;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .btn-checkout {
            background-color: #ff6f00;
            border-color: #ff6f00;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-checkout:hover {
            background-color: #e66000;
            border-color: #e66000;
            transform: scale(1.02);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .atv-option {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .atv-total-section {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .cart-item {
                flex-direction: column;
                text-align: center;
            }

            .cart-item .text-center {
                margin-top: 1rem;
            }
        }

        /* Package Card */
        .package-card {
            flex-direction: row;
        }

        .atv-header,
        .package-card,
        .atv-purchase-container .row {
            width: 100% !important;
        }

        .atv-purchase-container .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        @media (max-width: 576px) {
            .package-card {
                flex-direction: column;
                height: 300px;
            }

            .package-card .quantity-controls {
                align-items: stretch;

            }




        }

        @media (max-width: 768px) {
            .package-card .quantity-controls input {
                width: 90px !important;
            }

            .gap-button-cart {
                gap: 10px !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container maincontent-margin" style="margin-top: 10%;">
        <h2 class="cart-title">Your Cart</h2>
        <h2 style="font-size: 20px;" class="cart-title">Continue Purchase</h2>

        @if (session('error'))
            <div class="error-message" id="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session('success'))
            <div class="success-message" id="success-message">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (isset($errors) && is_object($errors) && method_exists($errors, 'any') && $errors->any())
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (isset($cartErrors) && is_array($cartErrors) && !empty($cartErrors))
            <div class="error-message">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Cart Issues:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($cartErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (empty($packages))
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p class="text-muted">Add some adventure packages to get started!</p>
                <a href="{{ route('adventure') }}" class="btn btn-orange jatio-bg-color">
                    Browse Packages
                </a>
            </div>
        @else
            <div class="row mt-4">
                <div class="col-lg-8">
                    <!-- All Packages Section - Grouped by Date -->
                    @foreach ($packagesByDate as $dateGroup)
                        @php
                            $date = $dateGroup['date'];
                            $slots = $dateGroup['slots'];
                            $packagesInGroup = $dateGroup['packages'];

                            // Separate ATV/UTV packages from regular packages
                            $atvUtvPackages = array_filter($packagesInGroup, function ($package) {
                                $packageName = strtolower($package['variant']->package->name);
                                return str_contains($packageName, 'atv') ||
                                    str_contains($packageName, 'utv') ||
                                    str_contains($packageName, 'trail');
                            });

                            $regularPackages = array_filter($packagesInGroup, function ($package) {
                                $packageName = strtolower($package['variant']->package->name);
                                return !str_contains($packageName, 'atv') &&
                                    !str_contains($packageName, 'utv') &&
                                    !str_contains($packageName, 'trail');
                            });

                            // Group ATV/UTV packages by package type (ATV vs UTV)
                            $packagesByType = [];
                            foreach ($atvUtvPackages as $package) {
                                $packageName = $package['variant']->package->name;
                                $packageType = str_contains(strtolower($packageName), 'utv') ? 'UTV' : 'ATV';

                                if (!isset($packagesByType[$packageType])) {
                                    $packagesByType[$packageType] = [];
                                }
                                $packagesByType[$packageType][] = $package;
                            }
                        @endphp

                        <!-- Date Container -->
                        <div style="padding: 0; margin-bottom: 2rem;" class="atv-purchase-container">
                            <div style="padding: 0;" class="atv-body">
                                <!-- Date Header with Multiple Time Slots -->
                                <div style="padding: 15px 30px; background-color: #f8f9fa; border-bottom: 1px solid #e5e7eb;"
                                    class="atv-header">
                                    <span>Schedule: <span class="report">{{ date('d M, Y', strtotime($date)) }}</span>
                                        @if (count($slots) > 1)
                                            <br><small style="color: #666; font-size: 0.9em;">
                                                Time Slots:
                                                @foreach ($slots as $index => $slot)
                                                    {{ date('h:iA', strtotime($slot->start_time)) }}@if ($index < count($slots) - 1)
                                                        ,
                                                    @endif
                                                @endforeach
                                            </small>
                                        @else
                                            - <span
                                                class="report">{{ $slots[0] ? date('h:iA', strtotime($slots[0]->start_time)) : 'N/A' }}</span>
                                        @endif
                                        <br>Report Times:
                                        @foreach ($slots as $index => $slot)
                                            <span class="report">{{ date('h:iA', strtotime($slot->report_time)) }}</span>
                                            @if ($index < count($slots) - 1)
                                                ,
                                            @endif
                                        @endforeach
                                    </span>
                                </div>

                                <!-- ATV/UTV Packages Section -->
                                @if (!empty($atvUtvPackages))
                                    @foreach ($packagesByType as $packageType => $atvPackages)
                                        <div style="padding: 20px 30px; border-bottom: 1px solid #e5e7eb;" class="row">
                                            <div style="display: flex; flex-direction: column; gap: 10px;" class="col-xl-9">
                                                <div style="gap: 10px;" class="row">
                                                    <div style="margin-bottom: 0; background-color: #F8F9FA; padding: 20px; border-radius: 8px;"
                                                        class="atv-image-container col-xl-4">
                                                        <div class="text-center">
                                                            <h5 style="font-size: 14px;" class="fw-bold mb-3">
                                                                {{ $atvPackages[array_key_first($atvPackages)]['variant']->package->name }}
                                                            </h5>
                                                            <img src="{{ $atvPackages[array_key_first($atvPackages)]['variant']->package->display_image_url ?? asset('frontEnd/images/atv-trial.svg') }}"
                                                                alt="{{ $atvPackages[array_key_first($atvPackages)]['variant']->package->name }}"
                                                                class="img-fluid" style="width: 150px; height: auto;">
                                                        </div>
                                                    </div>

                                                    <div class="atv-details-container col-xl-8"
                                                        style="padding:0; margin-bottom: 0;">
                                                        <div class="atv-options">
                                                            @foreach ($atvPackages as $packageData)
                                                                @php
                                                                    $variant = $packageData['variant'];
                                                                    $quantity = $packageData['quantity'];

                                                                    // Calculate price using availability-based pricing
                                                                    $priceService = app(
                                                                        \App\Services\PriceCalculationService::class,
                                                                    );
                                                                    $unitPrice = $priceService->getPriceForDate(
                                                                        $variant,
                                                                        $date,
                                                                    );

                                                                    // Determine if it's double rider variant
$isDoubleRider = str_contains(
    strtolower($variant->variant_name),
    'double',
                                                                    );
                                                                @endphp

                                                                <div
                                                                    class="atv-option {{ $isDoubleRider ? 'atv-option-double' : 'atv-option-single' }}">
                                                                    <div class="atv-option-info-left">
                                                                        <div class="icons">
                                                                            <img src="{{ asset('frontEnd/images/Helmet.svg') }}"
                                                                                width="20px" alt="Helmet">
                                                                            @if ($isDoubleRider)
                                                                                <img src="{{ asset('frontEnd/images/Helmet.svg') }}"
                                                                                    width="20px" alt="Helmet">
                                                                            @endif
                                                                        </div>
                                                                        <div class="text-content">
                                                                            {{ $variant->variant_name }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="atv-option-info-right">
                                                                        <small class="d-block"># of {{ $packageType }}
                                                                            {{ $quantity }}</small>
                                                                        <span class="price">TK
                                                                            {{ number_format($unitPrice, 2) }}</span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div style="display: flex !important; align-items: stretch;"
                                                class="col-xl-3 gap-button-cart">
                                                @php
                                                    $totalQuantity = 0;
                                                    $totalAmount = 0;
                                                    foreach ($atvPackages as $packageData) {
                                                        $totalQuantity += $packageData['quantity'];
                                                        $priceService = app(
                                                            \App\Services\PriceCalculationService::class,
                                                        );
                                                        $unitPrice = $priceService->getPriceForDate(
                                                            $packageData['variant'],
                                                            $date,
                                                        );
                                                        $totalAmount += $unitPrice * $packageData['quantity'];
                                                    }
                                                @endphp

                                                <div style="margin-bottom: 0; border-radius:0px; display: flex; flex-direction: column; justify-content: center; align-items: center;"
                                                    class="atv-total-section ">
                                                    <strong>TOTAL</strong>
                                                    <span>{{ $totalQuantity }} {{ $packageType }}</span>
                                                    <span style="color: #000" class="total-price">TK
                                                        {{ number_format($totalAmount, 2) }}</span>
                                                </div>

                                                <div style="padding: 0; border-radius: 0px; background-color: #FFD2B9"
                                                    class="atv-action-icons">
                                                    @php
                                                        // Get the first package to access its key
                                                        $firstPackage = $atvPackages[array_key_first($atvPackages)];
                                                    @endphp
                                                    <i style="color: #000; align-self: center; "
                                                        class="fas fa-pen edit-icon "
                                                        onclick="editItem('{{ $firstPackage['key'] }}')"
                                                        title="Edit Item"></i>
                                                    <form
                                                        style="display: flex; align-items: center; justify-content: center;"
                                                        action="{{ route('frontend.cart.remove') }}" method="POST"
                                                        style="display: inline;">
                                                        @csrf
                                                        <input type="hidden" name="key"
                                                            value="{{ $firstPackage['key'] }}">
                                                        <button
                                                            style="background-color: #000; color: #fff; border-radius: 50%; border: none;"
                                                            type="submit" class="btn-remove"
                                                            onclick="confirmRemove('{{ $firstPackage['key'] }}')"
                                                            title="Remove Item">
                                                            <i style="color: #fff; padding: 0 1px;"
                                                                class="fas fa-times remove-icon"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                <!-- Regular Packages Section -->
                                @if (!empty($regularPackages))
                                    @foreach ($regularPackages as $packageData)
                                        @php
                                            $variant = $packageData['variant'];
                                            $quantity = $packageData['quantity'];

                                            $priceService = app(\App\Services\PriceCalculationService::class);
                                            $unitPrice = $priceService->getPriceForDate($variant, $date);
                                            $itemTotal = $unitPrice * $quantity;
                                        @endphp
                                        <div style="padding: 20px 30px; border-bottom: 1px solid #e5e7eb;"
                                            class="package-card shadow-sm border rounded-3 d-flex align-items-stretch justify-content-between"
                                            data-cart-key="{{ $packageData['key'] }}">
                                            <div
                                                class="package-header py-3 ps-3 mb-0 d-flex align-items-center gap-2 col-lg-6 ">
                                                <img style="width: 100px; height: 70px; border-radius: 10px; margin-bottom: 0;"
                                                    src="{{ $variant->package->display_image_url ?? asset('frontEnd/images/archery.svg') }}"
                                                    alt="{{ $variant->package->name }}" class="package-image">
                                                <div class="package-info">
                                                    <h5>{{ $variant->package->name }}</h5>
                                                    <p>Base price: TK {{ number_format($unitPrice) }}</p>
                                                </div>
                                            </div>

                                            <div style="margin-bottom: 0; border: none; col-lg-6"
                                                class="quantity-controls d-flex justify-content-between gap-0">
                                                <div style="padding: 0 10px;" class="flex-grow-1">
                                                    <strong>{{ $variant->package->name }}</strong><br>
                                                    <small>Base price <span class="text-danger">TK
                                                            {{ number_format($unitPrice) }}</span></small><br>
                                                    <small style="padding: 15px 0;"
                                                        class="d-flex align-items-start flex-column">Quantity
                                                        <div style="background-color: #FF6F00; color: #fff; border-radius: 10px;"
                                                            class="quantity-controls d-flex">
                                                            <button
                                                                style="background-color: #FF6F00; color: #fff; font-size: 20px;"
                                                                type="button"
                                                                class="btn btn-sm btn-outline-secondary quantity-btn"
                                                                onclick="updateQuantity('{{ $packageData['key'] }}', {{ $quantity }} - 1)"
                                                                {{ $quantity <= 1 ? 'disabled' : '' }}>-</button>
                                                            <input
                                                                style="background-color: #FF6F00; color: #fff; font-size: 20px; width: 150px; height: 50px; border: 0 !important;"
                                                                type="number" value="{{ $quantity }}"
                                                                min="1"
                                                                class="form-control d-inline-block quantity-input"
                                                                style="width: 60px; text-align: center;"
                                                                onchange="updateQuantity('{{ $packageData['key'] }}', this.value)"
                                                                data-cart-key="{{ $packageData['key'] }}">
                                                            <button
                                                                style="background-color: #FF6F00; color: #fff; font-size: 20px;"
                                                                type="button"
                                                                class="btn btn-sm btn-outline-secondary quantity-btn"
                                                                onclick="updateQuantity('{{ $packageData['key'] }}', {{ $quantity }} + 1)">+</button>
                                                        </div>
                                                    </small>
                                                </div>
                                                <div style="background-color: #FFF4EF; height: 100%; color: #000;"
                                                    class="total-price d-flex flex-column justify-content-center px-2">
                                                    <strong>TOTAL</strong>
                                                    <strong>TK {{ number_format($itemTotal) }}</strong>
                                                </div>
                                                <form action="{{ route('frontend.cart.remove') }}" method="POST"
                                                    style="height: 100%; background-color: #FFD2B9; border-radius: 0px 10px 10px 0px; display: flex; align-items: center; justify-content: center;"
                                                    class="remove-btn">
                                                    @csrf
                                                    <input type="hidden" name="key"
                                                        value="{{ $packageData['key'] }}">
                                                    <button type="submit" class="btn-remove"
                                                        style="background-color: #FFD2B9; border-radius: 0px 10px 10px 0px; display: flex; align-items: center; justify-content: center; border: none;"
                                                        onclick="confirmRemove('{{ $packageData['key'] }}')"
                                                        title="Remove Item">
                                                        <i class="fas fa-times remove-icon"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach


                    <!-- Promo Section - Fully Dynamic -->
                    <div class="promo-section mt-3 d-flex">
                        <input type="text" placeholder="Promo Code" class="form-control me-2"
                            value="{{ $appliedPromoCode ? $appliedPromoCode->code : '' }}" id="promo-code"
                            {{ $appliedPromoCode ? 'readonly' : '' }}>
                        @if ($appliedPromoCode)
                            <button class="btn btn-danger me-2" onclick="removePromo()" id="remove-promo-btn"
                                title="Remove Promo Code">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                        <button style="min-width: 150px;" class="btn btn-apply" onclick="applyPromo()"
                            id="apply-promo-btn" {{ $appliedPromoCode ? 'disabled' : '' }}>
                            {{ $appliedPromoCode ? 'Applied!' : 'Apply' }}
                        </button>
                    </div>

                    <!-- Promo Message Display -->
                    <div id="promo-message" class="mt-2">
                        @if ($appliedPromoCode)
                            <div class="text-success">
                                <i class="fas fa-check-circle"></i>
                                Promo code "{{ $appliedPromoCode->code }}" applied! Discount: TK
                                {{ number_format($promoDiscount) }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-4 mt-4 mt-lg-0">
                    <div class="order-summary">
                        <h4>Order Summary</h4>

                        @php
                            $subtotal = 0;
                            $priceService = app(\App\Services\PriceCalculationService::class);

                            foreach ($packages as $packageData) {
                                $variant = $packageData['variant'];
                                $quantity = $packageData['quantity'];
                                $date = $packageData['date'];

                                $unitPrice = $priceService->getPriceForDate($variant, $date);
                                $subtotal += $unitPrice * $quantity;
                            }

                            // Get applied promo code discount from session
                            $promoDiscount = session()->get('promo_discount', 0);
                            $appliedPromoCode = session()->get('applied_promo_code');

                            $tax = ($subtotal - $promoDiscount) * 0.15; // 15% VAT on amount after discount
                            $total = $subtotal - $promoDiscount + $tax;
                        @endphp

                        @foreach ($packages as $packageData)
                            @php
                                $variant = $packageData['variant'];
                                $quantity = $packageData['quantity'];
                                $date = $packageData['date'];

                                $unitPrice = $priceService->getPriceForDate($variant, $date);
                                $itemTotal = $unitPrice * $quantity;
                            @endphp
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span>{{ $variant->package->name }} - {{ $variant->variant_name }}</span>
                                <span>TK {{ number_format($itemTotal) }}</span>
                            </div>
                        @endforeach

                        <div class="d-flex justify-content-between py-2 promo-discount" id="discount-row"
                            style="{{ $promoDiscount > 0 ? '' : 'display: none;' }}">
                            <span>Promo Discount @if ($appliedPromoCode)
                                    ({{ $appliedPromoCode->code }})
                                @endif
                            </span>
                            <span id="discount-amount">- TK {{ number_format($promoDiscount) }}</span>
                        </div>

                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span>VAT (15%)</span>
                            <span>TK {{ number_format($tax) }}</span>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <span class="total-amount">Total</span>
                            <span class="total-amount" id="total-amount">TK {{ number_format($total) }}</span>
                        </div>

                        <div class="terms-conditions mt-3 mb-3">
                            <p class="text-muted small">
                                By placing your order, you agree to Adventour Adventure Bandarban's
                                <a href="{{ route('frontend.privacy-policy') }}" target="_blank"
                                    class="text-decoration-none">privacy policy</a>
                                and
                                <a href="{{ route('frontend.terms-conditions') }}" target="_blank"
                                    class="text-decoration-none">conditions of use</a>.
                            </p>
                        </div>

                        <a href="{{ route('frontend.checkout.index') }}" class="btn btn-checkout mt-3">
                            <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                        </a>

                        <a href="{{ route('frontend.packages.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Quantity Edit Modal -->
    <div class="modal fade" id="quantityEditModal" tabindex="-1" aria-labelledby="quantityEditModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quantityEditModalLabel">Edit Quantity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="editModalImage" src="" alt="Package" class="img-fluid"
                            style="max-width: 200px; border-radius: 8px;">
                    </div>
                    <h6 id="editModalTitle" class="text-center mb-3"></h6>
                    <div class="text-center mb-3">
                        <small class="text-muted">Current Price: <span id="editModalPrice"
                                class="fw-bold text-success"></span></small>
                    </div>
                    <div class="text-center mb-3">
                        <small class="text-muted">Available: <span id="editModalAvailable"
                                class="fw-bold text-info"></span> vehicles</small>
                    </div>

                    <div class="quantity-edit-controls text-center">
                        <label class="form-label fw-bold">Quantity:</label>
                        <div class="quantity-controls-large d-inline-flex align-items-center border rounded p-2">
                            <button type="button" class="btn btn-outline-secondary quantity-btn-large" id="decreaseBtn">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" id="quantityInput" class="form-control quantity-input-large"
                                min="1" value="1" style="width: 80px; text-align: center; border: none;">
                            <button type="button" class="btn btn-outline-secondary quantity-btn-large" id="increaseBtn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <div class="total-preview">
                            <strong>Total: <span id="totalPreview" class="text-success"></span></strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveQuantityBtn">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Auto-hide messages with better UX
        function autoHideMessages() {
            const messages = document.querySelectorAll('.success-message, .error-message');
            messages.forEach(message => {
                setTimeout(() => {
                    if (message && message.parentNode) {
                        message.style.opacity = '0';
                        message.style.transform = 'translateY(-10px)';
                        setTimeout(() => {
                            if (message && message.parentNode) {
                                message.remove();
                            }
                        }, 300);
                    }
                }, 5000);
            });
        }

        // Initialize auto-hide
        autoHideMessages();

        // Edit item function - Redirect to packages page
        function editItem(key) {
            // Redirect to packages page for editing
            window.location.href = '{{ route('frontend.packages.index') }}';
        }

        // Remove item confirmation - Real functionality
        function confirmRemove(key) {
            const result = confirm('Are you sure you want to remove this item from your cart?');
            if (result) {
                // Find the form by the key value
                const form = document.querySelector(`input[name="key"][value="${key}"]`).closest('form');
                if (form) {
                    // Show loading state
                    const removeBtn = event.target;
                    const originalContent = removeBtn.innerHTML;
                    removeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    removeBtn.disabled = true;

                    // Submit the form - this will redirect back to cart page
                    form.submit();
                } else {
                    console.error('Form not found for key:', key);
                    alert('Error: Could not find the item to remove. Please refresh the page and try again.');
                }
            }
        }

        // Apply promo code - Real functionality
        function applyPromo() {
            const promoCode = document.getElementById('promo-code').value.trim();
            if (!promoCode) {
                showNotification('Please enter a promo code', 'error');
                return;
            }

            // Show loading state
            const applyBtn = document.getElementById('apply-promo-btn');
            const promoInput = document.getElementById('promo-code');
            const promoMessage = document.getElementById('promo-message');

            if (applyBtn) {
                const originalText = applyBtn.textContent;
                applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying...';
                applyBtn.disabled = true;
                promoInput.disabled = true;

                // Make real API call
                fetch('{{ route('frontend.cart.validate-promo') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            promo_code: promoCode
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            promoMessage.innerHTML = `
            <div class="text-success">
              <i class="fas fa-check-circle"></i>
              Promo code "${promoCode}" applied! Discount: ${data.discount_formatted}
            </div>
          `;

                            // Update button state
                            applyBtn.innerHTML = 'Applied!';
                            applyBtn.disabled = true;
                            promoInput.readOnly = true;

                            // Add remove button
                            const removeBtn = document.createElement('button');
                            removeBtn.className = 'btn btn-danger me-2';
                            removeBtn.id = 'remove-promo-btn';
                            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                            removeBtn.onclick = removePromo;
                            removeBtn.title = 'Remove Promo Code';

                            applyBtn.parentNode.insertBefore(removeBtn, applyBtn);

                            // Reload page to update totals
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);

                            showNotification(data.message, 'success');
                        } else {
                            // Show error message
                            promoMessage.innerHTML = `
            <div class="text-danger">
              <i class="fas fa-exclamation-circle"></i>
              ${data.message}
            </div>
          `;

                            // Reset button state
                            applyBtn.innerHTML = originalText;
                            applyBtn.disabled = false;
                            promoInput.disabled = false;

                            showNotification(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error applying promo code:', error);
                        promoMessage.innerHTML = `
          <div class="text-danger">
            <i class="fas fa-exclamation-circle"></i>
            Error applying promo code. Please try again.
          </div>
        `;

                        // Reset button state
                        applyBtn.innerHTML = originalText;
                        applyBtn.disabled = false;
                        promoInput.disabled = false;

                        showNotification('Error applying promo code. Please try again.', 'error');
                    });
            }
        }

        // Remove promo code - Real functionality
        function removePromo() {
            const result = confirm('Are you sure you want to remove this promo code?');
            if (result) {
                // Show loading state
                const removeBtn = document.getElementById('remove-promo-btn');
                const applyBtn = document.getElementById('apply-promo-btn');
                const promoInput = document.getElementById('promo-code');
                const promoMessage = document.getElementById('promo-message');

                if (removeBtn) {
                    removeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    removeBtn.disabled = true;

                    // Make real API call
                    fetch('{{ route('frontend.cart.remove-promo') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Clear promo message
                                promoMessage.innerHTML = '';

                                // Reset form state
                                promoInput.value = '';
                                promoInput.readOnly = false;
                                promoInput.disabled = false;

                                // Reset buttons
                                applyBtn.innerHTML = 'Apply';
                                applyBtn.disabled = false;

                                // Remove remove button
                                removeBtn.remove();

                                // Reload page to update totals
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);

                                showNotification(data.message, 'success');
                            } else {
                                // Reset button state
                                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                                removeBtn.disabled = false;

                                showNotification('Error removing promo code. Please try again.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error removing promo code:', error);

                            // Reset button state
                            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                            removeBtn.disabled = false;

                            showNotification('Error removing promo code. Please try again.', 'error');
                        });
                }
            }
        }

        // Update quantity function for cart items
        function updateQuantity(key, quantity) {
            if (quantity < 1) {
                alert('Quantity must be at least 1');
                return;
            }

            // Find the input element for this cart key
            const input = document.querySelector(`input[data-cart-key="${key}"]`);
            if (!input) {
                console.error('Input element not found for cart key:', key);
                return;
            }

            const originalValue = input.value;
            input.disabled = true;

            // Disable all quantity buttons for this item
            const quantityControls = input.closest('.quantity-controls');
            const buttons = quantityControls.querySelectorAll('.quantity-btn');
            buttons.forEach(btn => btn.disabled = true);

            // Show loading state
            input.style.opacity = '0.6';

            // Make AJAX request to update quantity
            fetch('{{ route('frontend.cart.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        cart_key: key,
                        quantity: quantity
                    })
                })
                .then(response => {
                    if (response.ok) {
                        // Update the input value
                        input.value = quantity;

                        // Update button states
                        const decreaseBtn = quantityControls.querySelector('button[onclick*="' + (quantity - 1) + '"]');
                        const increaseBtn = quantityControls.querySelector('button[onclick*="' + (quantity + 1) + '"]');

                        if (decreaseBtn) {
                            decreaseBtn.disabled = quantity <= 1;
                            decreaseBtn.setAttribute('onclick', `updateQuantity('${key}', ${quantity - 1})`);
                        }
                        if (increaseBtn) {
                            increaseBtn.setAttribute('onclick', `updateQuantity('${key}', ${quantity + 1})`);
                        }

                        // Show success message
                        showNotification('Quantity updated successfully!', 'success');

                        // Reload page to update totals and availability
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        // Show error message
                        showNotification('Error updating quantity: ' + data.message, 'error');
                        input.value = originalValue;
                    }
                })
                // .then(response => response.json())
                // .then(data => {
                //     if (data.success) {
                //         // Update the input value
                //         input.value = quantity;

                //         // Update button states
                //         const decreaseBtn = quantityControls.querySelector('button[onclick*="' + (quantity - 1) + '"]');
                //         const increaseBtn = quantityControls.querySelector('button[onclick*="' + (quantity + 1) + '"]');

                //         if (decreaseBtn) {
                //             decreaseBtn.disabled = quantity <= 1;
                //             decreaseBtn.setAttribute('onclick', `updateQuantity('${key}', ${quantity - 1})`);
                //         }
                //         if (increaseBtn) {
                //             increaseBtn.setAttribute('onclick', `updateQuantity('${key}', ${quantity + 1})`);
                //         }

                //         // Show success message
                //         showNotification('Quantity updated successfully!', 'success');

                //         // Reload page to update totals and availability
                //         setTimeout(() => {
                //             window.location.reload();
                //         }, 1000);
                //     } else {
                //         // Show error message
                //         showNotification('Error updating quantity: ' + data.message, 'error');
                //         input.value = originalValue;
                //     }
                // })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error updating quantity. Please try again.', 'error');
                    input.value = originalValue;
                })
                .finally(() => {
                    // Re-enable controls
                    input.disabled = false;
                    input.style.opacity = '1';
                    buttons.forEach(btn => btn.disabled = false);
                });

            // TODO: Implement real AJAX request when route is available
            // fetch('{{ route('frontend.cart.update') }}', {
            //   method: 'POST',
            //   headers: {
            //     'Content-Type': 'application/json',
            //     'X-CSRF-TOKEN': '{{ csrf_token() }}'
            //   },
            //   body: JSON.stringify({
            //     key: key,
            //     quantity: quantity
            //   })
            // })
            // .then(response => response.json())
            // .then(data => {
            //   if (data.success) {
            //     window.location.reload();
            //   } else {
            //     alert('Error updating quantity: ' + data.message);
            //     input.value = originalValue;
            //   }
            // })
            // .catch(error => {
            //   console.error('Error:', error);
            //   alert('Error updating quantity. Please try again.');
            //   input.value = originalValue;
            // })
            // .finally(() => {
            //   input.disabled = false;
            // });
        }

        // Edit item function - shows the quantity edit modal
        function editItem(cartKey) {
            // Show loading state
            const modal = new bootstrap.Modal(document.getElementById('quantityEditModal'));
            modal.show();

            // Show loading in modal
            document.getElementById('editModalTitle').textContent = 'Loading...';
            document.getElementById('editModalPrice').textContent = 'Loading...';
            document.getElementById('editModalAvailable').textContent = 'Loading...';

            // Fetch real availability data from server
            fetch(`{{ route('frontend.cart.availability') }}?cart_key=${cartKey}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate modal with real data
                        document.getElementById('editModalImage').src = data.data.image;
                        document.getElementById('editModalTitle').textContent = data.data.title;

                        // Show price with discount if applicable
                        let priceText = `TK ${data.data.unit_price.toLocaleString()}`;
                        if (data.data.has_discount && data.data.default_price > data.data.unit_price) {
                            priceText =
                                `<span class="text-decoration-line-through text-muted">TK ${data.data.default_price.toLocaleString()}</span> <span class="text-success fw-bold">TK ${data.data.unit_price.toLocaleString()}</span>`;
                        }
                        document.getElementById('editModalPrice').innerHTML = priceText;

                        // Show real availability
                        document.getElementById('editModalAvailable').textContent = data.data.available_vehicles;

                        // Set current quantity
                        const quantityInput = document.getElementById('quantityInput');
                        quantityInput.value = data.data.current_quantity;
                        quantityInput.setAttribute('data-cart-key', cartKey);
                        quantityInput.setAttribute('data-max-available', data.data.available_vehicles);

                        // Update total preview
                        updateTotalPreview();

                        // Update button states
                        updateButtonStates();
                    } else {
                        showNotification(data.message || 'Failed to load item data', 'error');
                        modal.hide();
                    }
                })
                .catch(error => {
                    console.error('Error fetching availability:', error);
                    showNotification('Failed to load item data', 'error');
                    modal.hide();
                });
        }


        // Update total preview in modal
        function updateTotalPreview() {
            const quantityInput = document.getElementById('quantityInput');
            const quantity = parseInt(quantityInput.value) || 1;
            const priceElement = document.getElementById('editModalPrice');

            // Extract price from the element (handle both simple and discount formats)
            let unitPrice = 0;
            const priceText = priceElement.textContent || priceElement.innerText;

            // Try to extract the final price (after discount if applicable)
            const priceMatches = priceText.match(/TK\s*([\d,]+)/g);
            if (priceMatches && priceMatches.length > 0) {
                // Get the last price match (which should be the final price)
                const finalPriceText = priceMatches[priceMatches.length - 1];
                unitPrice = parseFloat(finalPriceText.replace(/[^\d.]/g, '')) || 1000;
            } else {
                unitPrice = 1000; // fallback
            }

            const total = quantity * unitPrice;
            document.getElementById('totalPreview').textContent = `TK ${total.toLocaleString()}`;
        }

        // Notification function
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.quantity-notification');
            existingNotifications.forEach(notification => notification.remove());

            // Create notification element
            const notification = document.createElement('div');
            notification.className =
                `quantity-notification alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      min-width: 300px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;

            notification.innerHTML = `
      ${message}
      <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;

            document.body.appendChild(notification);

            // Auto remove after 3 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 3000);
        }

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }
  `;
        document.head.appendChild(style);

        // Initialize modal event handlers
        document.addEventListener('DOMContentLoaded', function() {
            // Modal quantity controls
            const decreaseBtn = document.getElementById('decreaseBtn');
            const increaseBtn = document.getElementById('increaseBtn');
            const quantityInput = document.getElementById('quantityInput');
            const saveBtn = document.getElementById('saveQuantityBtn');

            if (decreaseBtn) {
                decreaseBtn.addEventListener('click', function() {
                    const currentValue = parseInt(quantityInput.value) || 1;
                    if (currentValue > 1) {
                        quantityInput.value = currentValue - 1;
                        updateTotalPreview();
                        updateButtonStates();
                    }
                });
            }

            if (increaseBtn) {
                increaseBtn.addEventListener('click', function() {
                    const currentValue = parseInt(quantityInput.value) || 1;
                    const maxAvailable = parseInt(quantityInput.getAttribute('data-max-available')) || 999;
                    if (currentValue < maxAvailable) {
                        quantityInput.value = currentValue + 1;
                        updateTotalPreview();
                        updateButtonStates();
                    } else {
                        showNotification(`Maximum ${maxAvailable} vehicles available`, 'warning');
                    }
                });
            }

            if (quantityInput) {
                quantityInput.addEventListener('input', function() {
                    const value = parseInt(this.value) || 1;
                    const maxAvailable = parseInt(this.getAttribute('data-max-available')) || 999;

                    if (value < 1) {
                        this.value = 1;
                    } else if (value > maxAvailable) {
                        this.value = maxAvailable;
                        showNotification(`Maximum ${maxAvailable} vehicles available`, 'warning');
                    }

                    updateTotalPreview();
                    updateButtonStates();
                });
            }

            if (saveBtn) {
                saveBtn.addEventListener('click', function() {
                    const cartKey = quantityInput.getAttribute('data-cart-key');
                    const newQuantity = parseInt(quantityInput.value) || 1;

                    if (cartKey) {
                        // Simple save - just reload the page with updated quantity
                        // This is the simplest approach that works reliably
                        window.location.href =
                            `{{ route('frontend.cart.update.get') }}?cart_key=${cartKey}&quantity=${newQuantity}`;
                    }
                });
            }

            // Update button states function
            function updateButtonStates() {
                const currentValue = parseInt(quantityInput.value) || 1;
                const maxAvailable = parseInt(quantityInput.getAttribute('data-max-available')) || 999;

                if (decreaseBtn) {
                    decreaseBtn.disabled = currentValue <= 1;
                }
                if (increaseBtn) {
                    increaseBtn.disabled = currentValue >= maxAvailable;
                }
            }

            // Initialize button states when modal is shown
            document.getElementById('quantityEditModal').addEventListener('shown.bs.modal', function() {
                updateButtonStates();
            });
        });

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            // Add tooltips to action buttons
            const editIcons = document.querySelectorAll('.edit-icon');
            const removeButtons = document.querySelectorAll('.remove-icon');

            editIcons.forEach(icon => {
                icon.setAttribute('title', 'Edit this item');
            });

            removeButtons.forEach(btn => {
                btn.setAttribute('title', 'Remove this item');
            });

            // Add loading states to images
            const images = document.querySelectorAll('.package-image');
            images.forEach(img => {
                img.addEventListener('load', function() {
                    this.style.opacity = '1';
                });

                img.addEventListener('error', function() {
                    this.src = '{{ asset('admin/images/package.svg') }}';
                    this.style.opacity = '1';
                });
            });
        });

        // Enhanced form validation for promo code
        const promoCodeInput = document.getElementById('promo-code');
        if (promoCodeInput) {
            promoCodeInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyPromo();
                }
            });
        }
    </script>

    @if (session('success') && str_contains(session('success'), 'removed'))
        <script>
            setTimeout(function() {
                if (typeof updateCartCount === 'function') {
                    updateCartCount();
                }
            }, 1000);
        </script>
    @endif
@endpush
