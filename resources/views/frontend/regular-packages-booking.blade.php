@extends('layouts.frontend')

@section('title', 'Regular Packages - Date & Time Selection')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('frontEnd/css/csutom.css') }}">
    <link rel="stylesheet" href="{{ asset('frontEnd/css/packege.css') }}">
    <style>
        .jatio-bg-color {
            background-color: #ff782d;
            color: #ffffff;
            border-color: #ff782d;
        }

        .jatio-bg-color:hover {
            background-color: #e66a28 !important;
            color: #ffffff;
        }

        /* Global background and layout styles */
        body {
            background: #f8fafc;
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 120, 45, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(135, 206, 235, 0.05) 0%, transparent 50%);
            z-index: -1;
        }

        .container {
            position: relative;
            z-index: 1;
        }

        /* Enhanced page background */
        .main-content-area {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            min-height: calc(100vh - 120px);
            padding: 20px 0;
        }

        /* Card background enhancements */
        .package-card,
        .date-selection,
        .time-selection,
        .booking-summary {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(229, 231, 235, 0.8);
        }

        /* Enhanced page header */
        .page-header {
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
            padding: 30px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(229, 231, 235, 0.5);
        }

        .page-header h2 {
            color: #1e293b;
            font-weight: 700;
            margin: 0;
            text-align: center;
        }

        /* Enhanced empty cart state */
        .empty-cart {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            padding: 60px 40px;
            text-align: center;
            border: 1px solid rgba(229, 231, 235, 0.5);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .empty-cart i {
            font-size: 64px;
            color: #94a3b8;
            margin-bottom: 20px;
        }

        .empty-cart h3 {
            color: #475569;
            margin-bottom: 15px;
        }

        .empty-cart p {
            color: #64748b;
            margin-bottom: 25px;
        }

        .continue-shopping-btn {
            background: linear-gradient(135deg, #ff782d 0%, #e66a28 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .continue-shopping-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 120, 45, 0.3);
            color: white;
            text-decoration: none;
        }

        /* Enhanced calendar and time selection styling */
        .calendar-container,
        .time-slots {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 25px;
            border: 1px solid rgba(229, 231, 235, 0.5);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .calendar-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(229, 231, 235, 0.5);
        }

        .calendar-nav {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(229, 231, 235, 0.5);
            border-radius: 8px;
            padding: 8px 12px;
            color: #64748b;
            transition: all 0.3s ease;
        }

        .calendar-nav:hover {
            background: rgba(255, 255, 255, 1);
            color: #1e293b;
            transform: translateY(-1px);
        }

        .calendar-title {
            color: #1e293b;
            font-weight: 600;
            font-size: 18px;
        }

        .calendar-grid {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(229, 231, 235, 0.3);
        }

        .time-slot {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(229, 231, 235, 0.5);
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .time-slot:hover {
            background: rgba(255, 255, 255, 1);
            border-color: #ff782d;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 120, 45, 0.15);
        }

        .time-slot.selected {
            background: linear-gradient(135deg, #ff782d 0%, #e66a28 100%);
            color: white;
            border-color: #ff782d;
        }

        /* Floating Cart Icon Styles */
        .floating-cart-icon {
            position: fixed;
            top: 120px;
            right: 30px;
            z-index: 1000;
            background: #87CEEB;
            border-radius: 25px;
            padding: 15px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            color: #333;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: 2px solid #fff;
        }

        .floating-cart-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            color: #333;
            text-decoration: none;
        }

        .floating-cart-icon i {
            font-size: 18px;
            color: #333;
        }

        .floating-cart-icon .cart-count {
            font-weight: bold;
            color: #333;
        }

        .floating-cart-icon .cart-text {
            color: #333;
        }

        /* Success notification styles */
        .success-notification {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            background: #10b981;
            color: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            z-index: 1001;
            opacity: 0;
            transition: all 0.3s ease;
            min-width: 300px;
            text-align: center;
        }

        .success-notification.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        .success-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .success-content i {
            font-size: 24px;
            color: white;
            background: #059669;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-content span {
            font-size: 16px;
            font-weight: 600;
        }

        /* Package card styles */
        .package-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .package-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .package-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }

        .package-info h5 {
            margin: 0;
            color: #1f2937;
            font-weight: 600;
        }

        .package-info p {
            margin: 5px 0 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            background: #ff782d;
            border-radius: 25px;
            padding: 8px 15px;
            color: white;
            font-weight: 600;
        }

        .quantity-btn {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 0 8px;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .quantity-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .quantity-text {
            margin: 0 15px;
            min-width: 100px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .total-price {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            font-size: 18px;
            padding: 5px;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .remove-btn:hover {
            background-color: #fef2f2;
        }

        /* Date selection styles */
        .date-selection {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .date-selection h4 {
            color: #1f2937;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .calendar-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .calendar {
            flex: 1;
            min-width: 300px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .calendar-nav {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.2s;
        }

        .calendar-nav:hover {
            background-color: #f3f4f6;
        }

        .calendar-title {
            font-weight: 600;
            color: #1f2937;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .calendar-day-header {
            text-align: center;
            font-weight: 600;
            color: #6b7280;
            padding: 10px 5px;
            font-size: 12px;
        }

        .calendar-day {
            text-align: center;
            padding: 10px 5px;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.2s;
            border: 2px solid transparent;
        }

        .calendar-day:hover {
            background-color: #f3f4f6;
        }

        .calendar-day.selected {
            background-color: #ff782d;
            color: white;
            border-color: #ff782d;
        }

        .calendar-day.disabled {
            color: #d1d5db;
            cursor: not-allowed;
        }

        .calendar-day.today {
            border-color: #ff782d;
            font-weight: 600;
        }

        /* Time slot styles */
        .time-selection {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .time-selection h4 {
            color: #1f2937;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .time-slots {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .time-slot {
            padding: 12px 24px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            font-weight: 500;
            min-width: 120px;
            text-align: center;
        }

        .time-slot:hover {
            border-color: #ff782d;
            background-color: #fff7ed;
        }

        .time-slot.selected {
            border-color: #ff782d;
            background-color: #ff782d;
            color: white;
        }

        .time-slot.disabled {
            background-color: #f9fafb;
            color: #d1d5db;
            cursor: not-allowed;
            border-color: #e5e7eb;
        }

        /* Summary section */
        .booking-summary {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .summary-row:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 18px;
            color: #1f2937;
        }

        .checkout-btn {
            background: #ff782d;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s;
            width: 100%;
        }

        .checkout-btn:hover {
            background-color: #e66a28;
        }

        .checkout-btn:disabled {
            background-color: #d1d5db;
            cursor: not-allowed;
        }

        /* Empty cart state */
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-cart i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #d1d5db;
        }

        .empty-cart h3 {
            margin-bottom: 10px;
            color: #1f2937;
        }

        .empty-cart p {
            margin-bottom: 30px;
        }

        .continue-shopping-btn {
            background: #ff782d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s;
        }

        .continue-shopping-btn:hover {
            background-color: #e66a28;
            color: white;
            text-decoration: none;
        }
    </style>
@endpush

@section('content')
    <!-- Floating Cart Icon -->
    <a href="{{ route('regular-packages-booking') }}" class="floating-cart-icon" id="floatingCartIcon">
        <i class="fa-solid fa-cart-shopping"></i>
        <span class="cart-count" id="cartCount">{{ $cartCount ?? 0 }}</span>
        <span class="cart-text">Items</span>
    </a>

    <div class="main-content-area">
        <div class="container" style="margin-top: 120px; margin-bottom: 50px;">
            <div class="row">
                <div class="col-lg-12">
                    <div class=" pb-4">
                        <h2>Regular/Archery </h2>
                    </div>

                    @if (count($cartItems) > 0)
                        @foreach ($cartItems as $key => $item)
                            @php
                                $variant = $item['variant'];
                                $package = $variant->package;
                            @endphp
                            <div style="padding: 0;" class="package-card d-flex align-items-stretch justify-content-between"
                                data-cart-key="{{ $key }}">
                                <div class="package-header py-3 ps-3 mb-0 d-flex align-items-center gap-2 ">
                                    <img style="width: 100px; height: 70px; border-radius: 10px; margin-bottom: 0;"
                                        src="{{ $package->display_image_url }}" alt="{{ $package->name }}"
                                        class="package-image">
                                    <div class="package-info">
                                        <h5>{{ $package->name }}</h5>
                                        <p>Base price: TK {{ number_format($variant->prices->first()->amount ?? 0) }}</p>
                                    </div>
                                </div>


                                <div style="margin-bottom: 0;"
                                    class="quantity-controls d-flex justify-content-between gap-0">
                                    <div style="border-radius: 10px; margin-right: 10px;" class="quantity-selector">
                                        <button class="quantity-btn"
                                            onclick="updateQuantity('{{ $key }}', -1)">-</button>
                                        <span class="quantity-text">{{ $item['quantity'] }}
                                            Package{{ $item['quantity'] > 1 ? 's' : '' }}</span>
                                        <button class="quantity-btn"
                                            onclick="updateQuantity('{{ $key }}', 1)">+</button>
                                    </div>
                                    <div style="background-color: #FFF4EF; height: 100%;"
                                        class="total-price d-flex flex-column justify-content-center px-2">
                                        TOTAL
                                        <span>
                                            TK
                                            {{ number_format(($variant->prices->first()->amount ?? 0) * $item['quantity']) }}
                                        </span>

                                    </div>
                                    <button
                                        style="height: 100%; background-color: #FFD2B9; border-radius: 0px 10px 10px 0px"
                                        class="remove-btn" onclick="removeItem('{{ $key }}')" title="Remove item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                            </div>
                        @endforeach

                        <!-- Hidden element to pass cart data to JavaScript -->
                        <div id="cart-data" data-cart-items="{{ json_encode($cartItems) }}" style="display: none;"></div>

                        <!-- Date Selection -->
                        <div class="date-selection">
                            <h4>Choose Date</h4>
                            <div class="calendar-container">
                                <div class="calendar">
                                    <div class="calendar-header">
                                        <button class="calendar-nav" onclick="previousMonth()">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <span class="calendar-title" id="currentMonth">February 2024</span>
                                        <button class="calendar-nav" onclick="nextMonth()">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                    <div class="calendar-grid" id="calendarGrid">
                                        <!-- Calendar will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Time Slot Selection -->
                        <div class="time-selection">
                            <h4>Choose Preferred Slot</h4>
                            <div class="time-slots" id="timeSlots">
                                <!-- Time slots will be populated by JavaScript -->
                            </div>
                        </div>
                    @else
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Your cart is empty</h3>
                            <p>Add some regular packages to get started with your booking.</p>
                            <a href="{{ route('custom-packages') }}" class="continue-shopping-btn">
                                Continue Shopping
                            </a>
                        </div>
                    @endif
                </div>

                <div class="col-lg-12">
                    @if (count($cartItems) > 0)
                        <div style="box-shadow: none; border: none; background-color: transparent;" class="booking-summary">
                            {{-- <h4 class="mb-3">Booking Summary</h4>

                            @php
                                $subtotal = 0;
                                foreach ($cartItems as $item) {
                                    $variant = $item['variant'];
                                    $price = $variant->prices->first()->amount ?? 0;
                                    $subtotal += $price * $item['quantity'];
                                }
                            @endphp

                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span>TK {{ number_format($subtotal) }}</span>
                            </div>

                            @if (isset($promoDiscount) && $promoDiscount > 0)
                                <div class="summary-row">
                                    <span>Discount:</span>
                                    <span>-TK {{ number_format($promoDiscount) }}</span>
                                </div>
                            @endif

                            <div class="summary-row">
                                <span>Total:</span>
                                <span>TK {{ number_format($subtotal - ($promoDiscount ?? 0)) }}</span>
                            </div> --}}

                            <div style="display: flex; justify-content: end;">
                                <button style="width: 300px;" class="checkout-btn mt-3" onclick="proceedToCheckout()"
                                    id="checkoutBtn" disabled>
                                    Proceed to Check out
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentDate = new Date();
            let selectedDate = null;
            let selectedTimeSlot = null;
            let currentMonth = new Date();

            // Initialize the page
            initializePage();

            function initializePage() {
                renderCalendar();
                loadTimeSlots();
                updateCheckoutButton();
            }

            function renderCalendar() {
                const monthNames = ["January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"
                ];

                document.getElementById('currentMonth').textContent =
                    `${monthNames[currentMonth.getMonth()]} ${currentMonth.getFullYear()}`;

                const grid = document.getElementById('calendarGrid');
                if (!grid) return;

                grid.innerHTML = '';

                // Add day headers
                const days = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
                days.forEach(day => {
                    const dayHeader = document.createElement('div');
                    dayHeader.className = 'calendar-day-header';
                    dayHeader.textContent = day;
                    grid.appendChild(dayHeader);
                });

                // Get first day of month and number of days
                const firstDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1);
                const lastDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 0);
                const startDate = new Date(firstDay);
                startDate.setDate(startDate.getDate() - firstDay.getDay());

                // Fill calendar
                for (let i = 0; i < 42; i++) {
                    const date = new Date(startDate);
                    date.setDate(startDate.getDate() + i);

                    const dayElement = document.createElement('div');
                    dayElement.className = 'calendar-day';
                    dayElement.textContent = date.getDate();

                    // Check if this date is in current month
                    if (date.getMonth() !== currentMonth.getMonth()) {
                        dayElement.style.color = '#d1d5db';
                    }

                    // Check if date is in the past
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    if (date < today) {
                        dayElement.classList.add('disabled');
                    } else {
                        dayElement.addEventListener('click', () => selectDate(date));
                    }

                    // Check if it's today
                    if (date.toDateString() === today.toDateString()) {
                        dayElement.classList.add('today');
                    }

                    // Check if it's selected
                    if (selectedDate && date.toDateString() === selectedDate.toDateString()) {
                        dayElement.classList.add('selected');
                    }

                    grid.appendChild(dayElement);
                }
            }

            function selectDate(date) {
                selectedDate = date;
                renderCalendar();
                loadTimeSlots();
                updateCheckoutButton();
            }

            function loadTimeSlots() {
                const timeSlotsContainer = document.getElementById('timeSlots');
                if (!timeSlotsContainer) return;

                timeSlotsContainer.innerHTML =
                    '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading time slots...</div>';

                if (!selectedDate) {
                    timeSlotsContainer.innerHTML = '<p class="text-muted">Please select a date first</p>';
                    return;
                }

                // Get cart items from hidden element
                const cartDataElement = document.getElementById('cart-data');
                if (!cartDataElement) {
                    timeSlotsContainer.innerHTML = '<p class="text-muted">No cart data available</p>';
                    return;
                }

                const cartItems = JSON.parse(cartDataElement.getAttribute('data-cart-items'));
                if (!cartItems || Object.keys(cartItems).length === 0) {
                    timeSlotsContainer.innerHTML = '<p class="text-muted">No packages in cart</p>';
                    return;
                }

                // Get the first variant ID from cart items
                const firstVariantId = Object.values(cartItems)[0].variant.id;

                // Format date for API call (YYYY-MM-DD)
                const formattedDate = selectedDate.toISOString().split('T')[0];

                console.log('Fetching time slots for:', {
                    variantId: firstVariantId,
                    date: formattedDate,
                    url: `/api/schedule-slots/availability?variant_id=${firstVariantId}&date=${formattedDate}`
                });

                // Fetch dynamic time slots from API
                fetch(`/api/schedule-slots/availability?variant_id=${firstVariantId}&date=${formattedDate}`)
                    .then(response => {
                        console.log('API Response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('API Response data:', data);
                        timeSlotsContainer.innerHTML = '';

                        if (!data || data.length === 0) {
                            timeSlotsContainer.innerHTML =
                                '<p class="text-muted">No time slots available for this date</p>';
                            return;
                        }

                        // Sort slots by ID to maintain consistent order
                        data.sort((a, b) => a.id - b.id);

                        data.forEach(slot => {
                            const slotElement = document.createElement('div');
                            slotElement.className = 'time-slot';
                            slotElement.setAttribute('data-slot-id', slot.id);

                            // Check if slot is available
                            if (slot.is_open && slot.available_total > 0) {
                                slotElement.innerHTML = `
              <div><strong>${slot.name}</strong></div>
             
            `;
                                // <div style="font-size: 12px; margin-top: 4px;">${slot.label}</div>
                                // <div style="font-size: 11px; margin-top: 2px; color: #28a745;">Available</div>

                                if (selectedTimeSlot === slot.id.toString()) {
                                    slotElement.classList.add('selected');
                                }

                                slotElement.addEventListener('click', () => selectTimeSlot(slot.id
                                    .toString()));
                            } else {
                                slotElement.innerHTML = `
              <div><strong>${slot.name}</strong></div>
              <div style="font-size: 12px; margin-top: 4px;">${slot.label}</div>
              <div style="font-size: 11px; margin-top: 2px; color: #dc3545;">Sold Out</div>
            `;
                                slotElement.style.opacity = '0.5';
                                slotElement.style.cursor = 'not-allowed';
                            }

                            timeSlotsContainer.appendChild(slotElement);
                        });

                        // Auto-select first available slot if none selected
                        if (!selectedTimeSlot && data.length > 0) {
                            const firstAvailable = data.find(slot => slot.is_open && slot.available_total > 0);
                            if (firstAvailable) {
                                selectTimeSlot(firstAvailable.id.toString());
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error loading time slots:', error);
                        timeSlotsContainer.innerHTML = `
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Error loading time slots:</strong><br>
            ${error.message || 'Please try again.'}
          </div>
        `;
                    });
            }

            function selectTimeSlot(slotId) {
                selectedTimeSlot = slotId;

                // Update visual selection without reloading all slots
                const timeSlotsContainer = document.getElementById('timeSlots');
                if (timeSlotsContainer) {
                    const slotElements = timeSlotsContainer.querySelectorAll('.time-slot');
                    slotElements.forEach(element => {
                        element.classList.remove('selected');
                        if (element.getAttribute('data-slot-id') === slotId) {
                            element.classList.add('selected');
                        }
                    });
                }

                updateCheckoutButton();
            }

            function updateCheckoutButton() {
                const checkoutBtn = document.getElementById('checkoutBtn');
                if (!checkoutBtn) return;

                const isComplete = selectedDate && selectedTimeSlot;
                checkoutBtn.disabled = !isComplete;

                if (isComplete) {
                    checkoutBtn.textContent = 'Proceed to Check out';
                } else {
                    checkoutBtn.textContent = 'Select Date & Time';
                }
            }

            // Calendar navigation
            window.previousMonth = function() {
                currentMonth.setMonth(currentMonth.getMonth() - 1);
                renderCalendar();
            };

            window.nextMonth = function() {
                currentMonth.setMonth(currentMonth.getMonth() + 1);
                renderCalendar();
            };

            // Cart functions
            window.updateQuantity = function(cartKey, change) {
                const quantityElement = document.querySelector(`[data-cart-key="${cartKey}"] .quantity-text`);
                if (!quantityElement) return;

                // Parse quantity from text like "2 Packages" or "1 Package"
                const quantityText = quantityElement.textContent;
                const currentQty = parseInt(quantityText.match(/(\d+)/)[1]) || 1;
                const newQty = Math.max(1, currentQty + change);

                // Update cart via AJAX
                fetch('{{ route('frontend.cart.update') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            cart_key: cartKey,
                            quantity: newQty
                        })
                    })
                    .then(response => {

                        if (response.ok) {
                            toastNotifications.success('Quantity updated successfully!');
                            // Reload page to update totals and availability
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            throw new Error('Server error');
                        }
                    })
                    // .then(response => console.log(response))
                    // .then(data => {
                    //     if (data.success) {
                    //         quantityElement.textContent = `${newQty} Package${newQty > 1 ? 's' : ''}`;

                    //         // Update the total price display
                    //         const packageCard = document.querySelector(`[data-cart-key="${cartKey}"]`);
                    //         const totalPriceElement = packageCard.querySelector('.total-price');
                    //         if (totalPriceElement) {
                    //             const basePriceText = packageCard.querySelector('.package-info p')
                    //                 .textContent;
                    //             const basePrice = parseInt(basePriceText.match(/TK (\d+)/)[1]);
                    //             const totalPrice = basePrice * newQty;
                    //             totalPriceElement.textContent = `TOTAL: TK ${totalPrice.toLocaleString()}`;
                    //         }

                    //         updateCartCount();

                    //         // Update cart count in floating cart
                    //         const cartCountElement = document.getElementById('cartCount');
                    //         if (cartCountElement) {
                    //             const currentCount = parseInt(cartCountElement.textContent) || 0;
                    //             const countChange = newQty - currentQty;
                    //             cartCountElement.textContent = Math.max(0, currentCount + countChange);
                    //         }
                    //     } else {
                    //         toastNotifications.error(data.message || 'Error updating quantity');
                    //     }
                    // })
                    .catch(error => {
                        console.error('Error:', error);
                        toastNotifications.error('Error updating quantity');
                    });
            };

            window.removeItem = function(cartKey) {
                modalSystem.confirm('Are you sure you want to remove this item?', function() {
                    // User confirmed, proceed with removal
                    performRemoveItem(cartKey);
                });
            };

            function performRemoveItem(cartKey) {
                fetch('{{ route('frontend.cart.remove') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            key: cartKey
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the item from the DOM
                            const packageCard = document.querySelector(`[data-cart-key="${cartKey}"]`);
                            if (packageCard) {
                                packageCard.remove();
                            }

                            // Update cart count
                            updateCartCount();

                            // Update floating cart count
                            const cartCountElement = document.getElementById('cartCount');
                            if (cartCountElement) {
                                const currentCount = parseInt(cartCountElement.textContent) || 0;
                                const quantityText = packageCard.querySelector('.quantity-text').textContent;
                                const itemQuantity = parseInt(quantityText.match(/(\d+)/)[1]) || 1;
                                cartCountElement.textContent = Math.max(0, currentCount - itemQuantity);
                            }

                            // Check if cart is empty and show appropriate message
                            const remainingItems = document.querySelectorAll('[data-cart-key]');
                            if (remainingItems.length === 0) {
                                location.reload(); // Reload to show empty cart message
                            }
                        } else {
                            toastNotifications.error(data.message || 'Error removing item');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toastNotifications.error('Error removing item');
                    });
            };

            window.proceedToCheckout = function() {
                if (!selectedDate || !selectedTimeSlot) {
                    toastNotifications.warning('Please select a date and time slot');
                    return;
                }

                // Update cart with selected date and time
                const updates = [];
                const cartKeys = document.querySelectorAll('[data-cart-key]');

                cartKeys.forEach(element => {
                    const key = element.getAttribute('data-cart-key');
                    // Format date in local timezone to avoid timezone issues
                    const year = selectedDate.getFullYear();
                    const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                    const day = String(selectedDate.getDate()).padStart(2, '0');
                    const formattedDate = `${year}-${month}-${day}`;

                    updates.push({
                        cart_key: key,
                        date: formattedDate,
                        time_slot: selectedTimeSlot
                    });
                });

                fetch('{{ route('frontend.cart.updateDateTime') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(updates)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = '{{ route('frontend.cart.index') }}';
                        } else {
                            toastNotifications.error(data.message || 'Error updating booking details');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toastNotifications.error('Error updating booking details');
                    });
            };

            // Function to update cart count
            function updateCartCount() {
                console.log('Updating cart count...');

                // Add loading state to cart icon
                const cartIcon = document.getElementById('floatingCartIcon');
                if (cartIcon) {
                    cartIcon.style.opacity = '0.7';
                }

                fetch('{{ route('frontend.cart.count') }}')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Cart count data:', data);
                        const cartCountElement = document.getElementById('cartCount');
                        if (cartCountElement) {
                            const newCount = data.cart_total_items || 0;
                            cartCountElement.textContent = newCount;
                            console.log('Cart count updated to:', newCount);
                        } else {
                            console.error('Cart count element not found');
                        }

                        // Remove loading state
                        if (cartIcon) {
                            cartIcon.style.opacity = '1';
                        }
                    })
                    .catch(error => {
                        console.error('Error updating cart count:', error);

                        // Remove loading state on error
                        if (cartIcon) {
                            cartIcon.style.opacity = '1';
                        }
                    });
            }

            // Update cart count on page load
            updateCartCount();
        });
    </script>
@endpush
