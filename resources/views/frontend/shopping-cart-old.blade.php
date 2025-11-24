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
            align-items: center;
            gap: 1rem;
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
            background-color: #fce7f3;
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
            gap: 1rem;
            justify-content: flex-end;
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

        /* Exact Image Match Design */
        .booking-page-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .booking-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .booking-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .package-selection-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .package-selection-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .package-header {
            padding: 1.5rem 2rem 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .package-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .package-content {
            display: flex;
            align-items: center;

            gap: 2rem;
            justify-content: space-between;
        }

        .package-image {
            flex-shrink: 0;

        }

        .package-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #dee2e6;

            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: block;
        }

        .package-img:not([src]),
        .package-img[src=""] {
            background: linear-gradient(135deg, #ff6f00, #ff8c00);
            display: flex;
            align-items: center;
            justify-content: center;
            color: red;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .package-img:not([src]):after,
        .package-img[src=""]:after {
            content: "📦";
            font-size: 2rem;
        }

        .package-details {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .price-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .price-label {
            color: #666;
            font-size: 0.9rem;
        }

        .price-value {
            color: #ff6f00;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            background: #ff6f00;
            border-radius: 10px;
            padding: 0.5rem;
            gap: 0.5rem;
            width: fit-content;
        }

        .quantity-btn {
            width: 35px;
            height: 35px;
            border: none;
            color: white;
            background: #ff6f00;
            border-radius: 50%;
            font-weight: bold;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-btn:hover:not(:disabled) {

            transform: scale(1.1);
        }

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .quantity-display {
            color: white;
            font-weight: 600;
            padding: 0 1rem;
            white-space: nowrap;
        }

        .package-total {
            flex-shrink: 0;
            text-align: right;
        }

        .total-info {
            display: flex;

            align-items: flex-end;
            gap: 0.5rem;
            background-color: #ff6f0012;

        }

        .total-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 600;
        }

        .total-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
        }

        .remove-btn {


            width: 25px;
            height: 25px;
            border: none;
            background: black;
            color: white;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background: black;
            transform: scale(1.1);
        }

        /* Date Selection Section */
        .date-selection-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1.5rem;
        }

        .calendar-widget {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid #e9ecef;
        }

        .calendar-month h5 {
            color: #333;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
        }

        .calendar-header {
            display: contents;
        }

        .calendar-header span {
            text-align: center;
            font-weight: 600;
            color: #666;
            padding: 0.5rem;
            font-size: 0.9rem;
        }

        .calendar-days {
            display: contents;
        }

        .calendar-days .day {
            text-align: center;
            padding: 0.75rem;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 500;
            color: #333;
        }

        .calendar-days .day:hover {
            background: #f8f9fa;
        }

        .calendar-days .day.selected {
            background: #ff6f00;
            color: white;
        }

        /* Time Slot Selection */
        .time-slot-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .time-slot-buttons {
            display: flex;
            gap: 1rem;
        }

        .time-slot-btn {
            flex: 1;
            padding: 1rem 2rem;
            border: 2px solid #e9ecef;
            background: white;
            border-radius: 8px;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .time-slot-btn:hover {
            border-color: #ff6f00;
            color: #ff6f00;
        }

        .time-slot-btn.active {
            border-color: #ff6f00;
            background: #ff6f00;
            color: white;
        }

        /* Checkout Button */
        .checkout-section {
            text-align: center;
            margin-top: 3rem;
        }

        .checkout-btn {
            background: #ff6f00;
            color: white;
            border: none;
            padding: 1rem 3rem;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .checkout-btn:hover {
            background: #e66000;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 111, 0, 0.3);
            color: white;
            text-decoration: none;
        }

        /* Responsive Design - Exact Image Match */
        @media (max-width: 768px) {
            .booking-title {
                font-size: 2rem;
            }

            .booking-container {
                padding: 0 0.5rem;
            }

            .package-selection-card {
                margin-bottom: 1.5rem;
            }

            .package-header {
                padding: 1rem 1.5rem 0.5rem;
            }

            .package-title {
                font-size: 1.3rem;
            }

            .package-content {
                flex-direction: column;
                text-align: center;

                gap: 1.5rem;
            }

            .package-details {
                width: 100%;
                align-items: center;
            }

            .quantity-selector {
                justify-content: center;
            }

            .package-total {
                width: 100%;
                text-align: center;
            }

            .time-slot-buttons {
                flex-direction: column;
            }

            .calendar-widget {
                padding: 1rem;
            }

            .calendar-grid {
                gap: 0.25rem;
            }

            .calendar-days .day {
                padding: 0.5rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .booking-title {
                font-size: 1.8rem;
            }

            .package-content {
                padding: 1rem;
            }

            .package-img {
                width: 60px;
                height: 60px;
                border: 2px solid #dee2e6;
            }

            .quantity-btn {
                width: 30px;
                height: 30px;
                font-size: 1rem;
            }

            .quantity-display {
                padding: 0 0.5rem;
                font-size: 0.9rem;
            }

            .total-amount {
                font-size: 1.3rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container maincontent-margin" style="margin-top: 8%;">
        <div class="booking-page-header mb-4">
            <h1 class="booking-title">Regular/Archery</h1>
        </div>

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
            <div class="booking-container">
                <!-- Package Selection Card -->
                @foreach ($packages as $packageData)
                    @php
                        $variant = $packageData['variant'];
                        $quantity = $packageData['quantity'];
                        $date = $packageData['date'];

                        $priceService = app(\App\Services\PriceCalculationService::class);
                        $unitPrice = $priceService->getPriceForDate($variant, $date);
                        $itemTotal = $unitPrice * $quantity;
                    @endphp

                    <div class="package-selection-card ">



                        <div class="package-content ">
                            <div class="py-3 px-3">
                                <h3 class="package-title mb-1">{{ $variant->package->name }}</h3>
                                <div class="d-flex align-items-center gap-2">
                                    <div>

                                        <img src="{{ $variant->package->display_image_url ?? asset('frontEnd/images/archery.svg') }}"
                                            alt="{{ $variant->package->name }}" style="width: 100px; height: 80px;">
                                    </div>

                                    <div>
                                        <div class="price-info d-flex flex-column align-items-start">
                                            <span class="price-label">Base price</span>
                                            <span class="price-value">Tk {{ number_format($unitPrice) }}</span>
                                        </div>

                                    </div>
                                </div>
                            </div>


                            <div class="d-flex align-items-center gap-2  h-100">
                                <div class="package-details">

                                    <div class="quantity-selector">
                                        <button type="button" class="quantity-btn quantity-decrease"
                                            onclick="updateQuantity('{{ $packageData['key'] }}', {{ $quantity }} - 1)"
                                            {{ $quantity <= 1 ? 'disabled' : '' }}>-</button>
                                        <span class="quantity-display">{{ $quantity }} Package</span>
                                        <button type="button" class="quantity-btn quantity-increase"
                                            onclick="updateQuantity('{{ $packageData['key'] }}', {{ $quantity }} + 1)">+</button>
                                    </div>

                                    <!-- Hidden form for quantity update fallback -->
                                    <form action="{{ route('frontend.cart.update.get') }}" method="GET"
                                        style="display: none;" id="quantity-form-{{ $packageData['key'] }}">
                                        <input type="hidden" name="cart_key" value="{{ $packageData['key'] }}">
                                        <input type="hidden" name="quantity" value="{{ $quantity }}">
                                    </form>
                                </div>

                                <div class="package-total">
                                    <div class="total-info d-flex align-items-center justify-content-between h-100">
                                        <div class="d-flex align-items-center flex-column gap-2 pl">
                                            <span class="total-label">TOTAL</span>
                                            <span class="total-label">Tk {{ number_format($itemTotal) }}</span>
                                        </div>
                                        <div
                                            style="background-color: #ff6f0044; height: 100px;  width: 30px; padding:80px 2px">
                                            <button type="button" class="remove-btn "
                                                onclick="confirmRemove('{{ $packageData['key'] }}')" title="Remove Item">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('frontend.cart.remove') }}" method="POST" style="display: none;"
                            id="remove-form-{{ $packageData['key'] }}">
                            @csrf
                            <input type="hidden" name="key" value="{{ $packageData['key'] }}">
                        </form>
                    </div>
                @endforeach

                <!-- Date Selection Section -->
                <div class="date-selection-section">
                    <h3 class="section-title"
                        style="background-color: #ff6f00; color: white; padding: 10px; border-radius: 5px;">Choose Date</h3>
                    <div class="calendar-widget">
                        <div class="text-center">
                            <p class="text-muted mb-3">Select your preferred date for the adventure</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="calendar-month">
                                        <h5 class="mb-3">February 2022</h5>
                                        <div class="calendar-grid">
                                            <div class="calendar-header">
                                                <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                            </div>
                                            <div class="calendar-days">
                                                <span class="day">1</span><span class="day">2</span><span
                                                    class="day">3</span><span class="day">4</span><span
                                                    class="day">5</span><span class="day">6</span><span
                                                    class="day">7</span>
                                                <span class="day">8</span><span class="day">9</span><span
                                                    class="day">10</span><span class="day">11</span><span
                                                    class="day">12</span><span class="day">13</span><span
                                                    class="day">14</span>
                                                <span class="day">15</span><span class="day">16</span><span
                                                    class="day">17</span><span class="day">18</span><span
                                                    class="day">19</span><span class="day">20</span><span
                                                    class="day">21</span>
                                                <span class="day">22</span><span class="day">23</span><span
                                                    class="day">24</span><span class="day">25</span><span
                                                    class="day">26</span><span class="day">27</span><span
                                                    class="day">28</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="calendar-month">
                                        <h5 class="mb-3">March 2022</h5>
                                        <div class="calendar-grid">
                                            <div class="calendar-header">
                                                <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                            </div>
                                            <div class="calendar-days">
                                                <span class="day">1</span><span class="day">2</span><span
                                                    class="day">3</span><span class="day">4</span><span
                                                    class="day">5</span><span class="day">6</span><span
                                                    class="day">7</span>
                                                <span class="day">8</span><span class="day">9</span><span
                                                    class="day">10</span><span class="day">11</span><span
                                                    class="day">12</span><span class="day">13</span><span
                                                    class="day">14</span>
                                                <span class="day">15</span><span class="day">16</span><span
                                                    class="day">17</span><span class="day">18</span><span
                                                    class="day">19</span><span class="day">20</span><span
                                                    class="day">21</span>
                                                <span class="day">22</span><span class="day">23</span><span
                                                    class="day">24</span><span class="day">25</span><span
                                                    class="day">26</span><span class="day">27</span><span
                                                    class="day">28</span>
                                                <span class="day">29</span><span class="day">30</span><span
                                                    class="day">31</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Time Slot Selection -->
                <div class="time-slot-section">
                    <h3 class="section-title">Choose Preferred Slot</h3>
                    <div class="time-slot-buttons">
                        <button class="time-slot-btn" data-slot="morning">Morning</button>
                        <button class="time-slot-btn" data-slot="evening">Evening</button>
                    </div>
                </div>

                <!-- Promo Section - Fully Dynamic -->
                {{-- <div class="promo-section mt-3 d-flex">
                    <input type="text" placeholder="Promo Code" class="form-control me-2"
                        value="{{ $appliedPromoCode ? $appliedPromoCode->code : '' }}" id="promo-code"
                        {{ $appliedPromoCode ? 'readonly' : '' }}>
                    @if ($appliedPromoCode)
                        <button class="btn btn-danger me-2" onclick="removePromo()" id="remove-promo-btn"
                            title="Remove Promo Code">
                            <i class="fas fa-times"></i>
                        </button>
                    @endif
                    <button class="btn btn-apply" onclick="applyPromo()" id="apply-promo-btn"
                        {{ $appliedPromoCode ? 'disabled' : '' }}>
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
                </div> --}}
            </div>

            <!-- Checkout Button -->
            <div class="checkout-section">
                <a href="{{ route('frontend.checkout.index') }}" class="checkout-btn">
                    Proceed to Check out
                </a>
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
                const form = document.getElementById(`remove-form-${key}`);
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
            console.log('updateQuantity called with key:', key, 'quantity:', quantity);

            if (quantity < 1) {
                alert('Quantity must be at least 1');
                return;
            }

            // Find the quantity display element for this cart key
            const button = document.querySelector(`button[onclick*="updateQuantity('${key}'"]`);
            if (!button) {
                console.error('Button not found for cart key:', key);
                return;
            }

            const quantityDisplay = button.closest('.quantity-selector').querySelector('.quantity-display');
            if (!quantityDisplay) {
                console.error('Quantity display element not found for cart key:', key);
                return;
            }

            console.log('Found quantity display:', quantityDisplay);

            const originalValue = quantityDisplay.textContent;

            // Disable all quantity buttons for this item
            const quantityControls = quantityDisplay.closest('.quantity-selector');
            const buttons = quantityControls.querySelectorAll('.quantity-btn');
            buttons.forEach(btn => btn.disabled = true);

            // Show loading state
            quantityDisplay.style.opacity = '0.6';
            quantityDisplay.textContent = 'Updating...';

            // Simple approach: Update display immediately and reload page
            // This ensures the UI is responsive while the backend processes the update

            // Update the quantity display immediately
            quantityDisplay.textContent = `${quantity} Package`;

            // Update button states and onclick handlers
            const decreaseBtn = quantityControls.querySelector('.quantity-decrease');
            const increaseBtn = quantityControls.querySelector('.quantity-increase');

            if (decreaseBtn) {
                decreaseBtn.disabled = quantity <= 1;
                decreaseBtn.setAttribute('onclick', `updateQuantity('${key}', ${quantity - 1})`);
            }
            if (increaseBtn) {
                increaseBtn.setAttribute('onclick', `updateQuantity('${key}', ${quantity + 1})`);
            }

            // Show loading message
            quantityDisplay.textContent = 'Updating...';

            // Try AJAX request, fallback to page reload
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
                        showNotification('Quantity updated successfully!', 'success');
                        // Reload page to update totals and availability
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        throw new Error('Server error');
                    }
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                    // Fallback: Use form submission
                    const form = document.getElementById(`quantity-form-${key}`);
                    if (form) {
                        form.querySelector('input[name="quantity"]').value = quantity;
                        form.submit();
                    } else {
                        // Last resort: Reload page with URL parameters
                        const url = new URL(window.location);
                        url.searchParams.set('update_quantity', '1');
                        url.searchParams.set('cart_key', key);
                        url.searchParams.set('quantity', quantity);
                        window.location.href = url.toString();
                    }
                })
                .finally(() => {
                    // Re-enable controls
                    quantityDisplay.style.opacity = '1';
                    buttons.forEach(btn => btn.disabled = false);
                });
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

        // Package image error handling
        document.addEventListener('DOMContentLoaded', function() {
            const packageImages = document.querySelectorAll('.package-img');

            packageImages.forEach(img => {
                img.addEventListener('error', function() {
                    this.style.background = 'linear-gradient(135deg, #ff6f00, #ff8c00)';
                    this.style.display = 'flex';
                    this.style.alignItems = 'center';
                    this.style.justifyContent = 'center';
                    this.style.color = 'white';
                    this.style.fontWeight = 'bold';
                    this.style.fontSize = '1.2rem';
                    this.innerHTML = '📦';
                });

                img.addEventListener('load', function() {
                    this.style.opacity = '1';
                });
            });
        });

        // Time slot selection functionality
        document.addEventListener('DOMContentLoaded', function() {
            const timeSlotButtons = document.querySelectorAll('.time-slot-btn');

            timeSlotButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    timeSlotButtons.forEach(btn => btn.classList.remove('active'));

                    // Add active class to clicked button
                    this.classList.add('active');

                    // Store selected slot (you can use this for form submission)
                    const selectedSlot = this.getAttribute('data-slot');
                    console.log('Selected time slot:', selectedSlot);
                });
            });

            // Calendar day selection functionality
            const calendarDays = document.querySelectorAll('.calendar-days .day');

            calendarDays.forEach(day => {
                day.addEventListener('click', function() {
                    // Remove selected class from all days
                    calendarDays.forEach(d => d.classList.remove('selected'));

                    // Add selected class to clicked day
                    this.classList.add('selected');

                    // Store selected date (you can use this for form submission)
                    const selectedDate = this.textContent;
                    console.log('Selected date:', selectedDate);
                });
            });
        });
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
