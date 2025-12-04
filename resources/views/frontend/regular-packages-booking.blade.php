@extends('layouts.frontend')

@section('title', 'Your Cart')

@section('content')
    <div class="container mt-5 default-page-marign-top">
        <h2 class="mb-4">Your Shopping Cart</h2>

        @if ($guestCartItems->count() > 0)
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <table class="table table-borderless align-middle">
                                <thead class="border-bottom">
                                    <tr>
                                        <th width="40%">Package</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $subtotal = 0;
                                    @endphp
                                    @foreach ($guestCartItems as $item)
                                        @php
                                            $itemTotal = $item->cart_amount * $item->quantity;
                                            $subtotal += $itemTotal;
                                        @endphp
                                        <tr class="border-bottom">
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded overflow-hidden" style="width: 80px; height: 60px;">
                                                        <img src="{{ $item->package->display_image_url ?? asset('images/default-package.jpg') }}"
                                                            alt="{{ $item->package->name ?? '' }}"
                                                            class="w-100 h-100 object-fit-cover">
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">{{ $item->package->name ?? 'Package Name' }}</h6>
                                                        <small
                                                            class="text-muted">{{ $item->package->category->name ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-medium">TK {{ number_format($item->cart_amount, 2) }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <form action="{{ route('frontend.cart.update') }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="cart_uuid"
                                                            value="{{ $item->cart_uuid }}">
                                                        <input type="hidden" name="change" value="minus">
                                                        <button type="submit"
                                                            class="btn btn-outline-secondary btn-sm px-3 py-1 @if ($item->quantity <= 1) disabled @endif">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    </form>

                                                    <span class="mx-3 fw-bold">{{ $item->quantity }}</span>

                                                    <form action="{{ route('frontend.cart.update') }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="cart_uuid"
                                                            value="{{ $item->cart_uuid }}">
                                                        <input type="hidden" name="change" value="addition">
                                                        <button type="submit"
                                                            class="btn btn-outline-secondary btn-sm px-3 py-1">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold ">TK
                                                    {{ number_format($itemTotal, 2) }}</span>
                                            </td>
                                            <td>
                                                <form action="{{ route('frontend.cart.remove', $item->cart_uuid) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm"
                                                        onclick="return confirm('Remove this item from cart?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order Summary & Date/Time Selection -->
                <div class="col-lg-12">
                    <div class="card shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-body">
                            <!-- Custom Calendar Date Selection -->
                            <div class="date-selection mb-4">
                                <h6 class="fw-medium mb-3">
                                    <i class="fas fa-calendar-alt me-2"></i>Choose Date
                                </h6>
                                <div class="calendar-container border rounded p-3">
                                    <div class="calendar">
                                        <div class="calendar-header d-flex justify-content-between align-items-center mb-3">
                                            <button class="calendar-nav btn btn-outline-secondary btn-sm"
                                                onclick="previousMonth()">
                                                <i class="fas fa-chevron-left"></i>
                                            </button>
                                            <span class="calendar-title fw-bold" id="currentMonth">December 2025</span>
                                            <button class="calendar-nav btn btn-outline-secondary btn-sm"
                                                onclick="nextMonth()">
                                                <i class="fas fa-chevron-right"></i>
                                            </button>
                                        </div>
                                        <div class="calendar-grid" id="calendarGrid">
                                            <!-- Calendar headers -->
                                            <div class="calendar-day-header text-center text-muted small py-2">Su</div>
                                            <div class="calendar-day-header text-center text-muted small py-2">Mo</div>
                                            <div class="calendar-day-header text-center text-muted small py-2">Tu</div>
                                            <div class="calendar-day-header text-center text-muted small py-2">We</div>
                                            <div class="calendar-day-header text-center text-muted small py-2">Th</div>
                                            <div class="calendar-day-header text-center text-muted small py-2">Fr</div>
                                            <div class="calendar-day-header text-center text-muted small py-2">Sa</div>

                                            <!-- Calendar days will be generated by JavaScript -->
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="selectedDate" name="selected_date">
                                <div id="selectedDateDisplay" class="mt-3 p-2 bg-light rounded text-center d-none">
                                    <span class="text-primary-color fw-medium">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Selected: <span id="displayDate"></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Time Slot Selection (Initially Hidden) -->
                            <div class="time-selection mb-4 d-none" id="timeSelectionContainer">
                                <h6 class="fw-medium mb-3">
                                    <i class="fas fa-clock me-2"></i>Choose Preferred Slot
                                </h6>
                                <div class="time-slots-container">
                                    <div class="time-slots" id="timeSlots">
                                        <!-- Time slots will be populated by JavaScript -->
                                    </div>
                                    <div id="selectedSlotDisplay" class="mt-3 p-2 bg-light rounded text-center d-none">
                                        <span class="text-primary-color fw-medium">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Selected Slot: <span id="displaySlot"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- Price Summary -->
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span class="fw-medium">TK {{ number_format($subtotal, 2) }}</span>
                                </div>

                                @php
                                    $total = $subtotal;
                                @endphp

                                <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                                    <span class="fw-bold">Total Amount</span>
                                    <span class="fw-bold fs-5">TK {{ number_format($total, 2) }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- Buttons Section -->
                <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                    <!-- Left side - Continue Shopping -->
                    <a href="{{ url('custom-packages') }}" class="btn continue-shopping-btn">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>

                    <!-- Right side - Checkout Button -->
                    <button type="submit" class="checkout-btn">
                        Proceed to Checkout
                    </button>
                </div>

            </div>
        @else
            <!-- Empty Cart State -->
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-shopping-cart fa-4x text-muted"></i>
                </div>
                <h3 class="mb-3">Your cart is empty</h3>
                <p class="text-muted mb-4">Looks like you haven't added any packages to your cart yet.</p>
                <a href="{{ route('frontend.packages.index') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-store me-2"></i>Browse Packages
                </a>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Calendar functionality
            let currentDate = new Date();
            let currentMonth = currentDate.getMonth();
            let currentYear = currentDate.getFullYear();
            let selectedDate = null;
            let selectedTimeSlot = null;

            // Get time slots from backend
            const backendTimeSlots = @json($time_slots);

            // Format time slots for frontend use
            const timeSlots = backendTimeSlots.map(slot => ({
                id: slot.id,
                time: formatTimeSlot(slot.start_time, slot.end_time),
                start_time: slot.start_time,
                end_time: slot.end_time,
                name: slot.name || null
            }));

            // Helper function to format time slot - show hours only in bold
            function formatTimeSlot(startTime, endTime) {
                const formatTime = (timeStr) => {
                    if (!timeStr) return '';
                    const [hours, minutes] = timeStr.split(':').map(Number);
                    // Just return the hours in bold without AM/PM
                    const displayHours = hours % 12 || 12;
                    return `<strong>${displayHours}</strong>${minutes ? ':' + String(minutes).padStart(2, '0') : ''}`;
                };

                const start = formatTime(startTime);
                const end = formatTime(endTime);

                return start && end ? `${start} - ${end}` : 'Time Slot';
            }

            // Format date as YYYY-MM-DD
            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            // Format date for display
            function formatDateDisplay(date) {
                return date.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }

            // Generate calendar
            function generateCalendar(month, year) {
                const calendarGrid = document.getElementById('calendarGrid');
                const currentMonthElement = document.getElementById('currentMonth');

                // Clear existing days (keep headers)
                const headers = calendarGrid.querySelectorAll('.calendar-day-header');
                const daysToRemove = calendarGrid.querySelectorAll('.calendar-day');
                daysToRemove.forEach(day => day.remove());

                // Update month title
                const monthNames = ["January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"
                ];
                currentMonthElement.textContent = `${monthNames[month]} ${year}`;

                // Get first day of month
                const firstDay = new Date(year, month, 1);
                const startingDay = firstDay.getDay(); // 0 = Sunday

                // Get number of days in month
                const daysInMonth = new Date(year, month + 1, 0).getDate();

                // Get number of days in previous month
                const prevMonthDays = new Date(year, month, 0).getDate();

                // Add previous month's trailing days
                for (let i = 0; i < startingDay; i++) {
                    const day = document.createElement('div');
                    day.className = 'calendar-day disabled text-center py-2 text-muted';
                    day.textContent = prevMonthDays - startingDay + i + 1;
                    calendarGrid.appendChild(day);
                }

                // Add current month's days
                const today = new Date();
                const todayFormatted = formatDate(today);

                for (let day = 1; day <= daysInMonth; day++) {
                    const dayElement = document.createElement('div');
                    const date = new Date(year, month, day);
                    const dateFormatted = formatDate(date);

                    dayElement.className = 'calendar-day text-center py-2';

                    // Disable past dates
                    if (date < today.setHours(0, 0, 0, 0)) {
                        dayElement.classList.add('disabled');
                        dayElement.classList.add('text-muted');
                    }

                    // Highlight today
                    if (dateFormatted === todayFormatted) {
                        dayElement.classList.add('today');
                    }

                    // Highlight selected date
                    if (selectedDate && formatDate(selectedDate) === dateFormatted) {
                        dayElement.classList.add('selected');
                    }

                    dayElement.textContent = day;
                    dayElement.dataset.date = dateFormatted;

                    // Add click event
                    dayElement.addEventListener('click', function() {
                        if (this.classList.contains('disabled')) return;

                        // Remove previous selection
                        document.querySelectorAll('.calendar-day.selected').forEach(el => {
                            el.classList.remove('selected');
                        });

                        // Add current selection
                        this.classList.add('selected');

                        // Update selected date
                        selectedDate = date;
                        const dateFormatted = formatDate(date);
                        document.getElementById('selectedDate').value = dateFormatted;

                        // Show selected date display
                        const displayDiv = document.getElementById('selectedDateDisplay');
                        const displaySpan = document.getElementById('displayDate');
                        displaySpan.textContent = formatDateDisplay(date);
                        displayDiv.classList.remove('d-none');

                        // Show time selection
                        showTimeSelection();
                    });

                    calendarGrid.appendChild(dayElement);
                }

                // Calculate how many next month days to show (to fill 42 slots)
                const totalCells = 42; // 6 rows * 7 days
                const cellsUsed = startingDay + daysInMonth;
                const remainingCells = totalCells - cellsUsed;

                // Add next month's leading days
                for (let i = 1; i <= remainingCells; i++) {
                    const day = document.createElement('div');
                    day.className = 'calendar-day disabled text-center py-2 text-muted';
                    day.textContent = i;
                    calendarGrid.appendChild(day);
                }
            }

            // Show time selection
            function showTimeSelection() {
                const timeSelectionContainer = document.getElementById('timeSelectionContainer');
                timeSelectionContainer.classList.remove('d-none');

                // Populate time slots
                populateTimeSlots();

                // Scroll to time selection
                timeSelectionContainer.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }

            // Populate time slots
            function populateTimeSlots() {
                const timeSlotsContainer = document.getElementById('timeSlots');
                timeSlotsContainer.innerHTML = '';

                timeSlots.forEach(slot => {
                    const slotElement = document.createElement('div');
                    slotElement.className = 'time-slot';
                    if (selectedTimeSlot && selectedTimeSlot.id === slot.id) {
                        slotElement.classList.add('selected');
                    }
                    slotElement.dataset.slotId = slot.id;

                    // Time slot content with hours in bold
                    slotElement.innerHTML = `
                    <div class="time-slot-content">
                        <div>${slot.time}</div>
                        ${slot.name ? `<small class="text-muted d-block mt-1">${slot.name}</small>` : ''}
                    </div>
                `;

                    // Add click event
                    slotElement.addEventListener('click', function() {
                        // Remove previous selection
                        document.querySelectorAll('.time-slot.selected').forEach(el => {
                            el.classList.remove('selected');
                        });

                        // Add current selection
                        this.classList.add('selected');

                        // Update selected time slot
                        selectedTimeSlot = slot;

                        // Show selected slot display
                        const displayDiv = document.getElementById('selectedSlotDisplay');
                        const displaySpan = document.getElementById('displaySlot');
                        displaySpan.textContent = slot.time.replace(/<[^>]*>/g,
                            ''); // Remove HTML tags for display
                        displayDiv.classList.remove('d-none');

                        // Update hidden inputs
                        document.getElementById('checkout_time_slot_id').value = slot.id;
                        document.getElementById('checkout_time_slot_text').value = slot.time
                            .replace(/<[^>]*>/g, '');
                    });

                    timeSlotsContainer.appendChild(slotElement);
                });
            }

            // Navigation functions
            window.previousMonth = function() {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                generateCalendar(currentMonth, currentYear);
            }

            window.nextMonth = function() {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                generateCalendar(currentMonth, currentYear);
            }

            // Initialize calendar
            generateCalendar(currentDate.getMonth(), currentDate.getFullYear());

            // Form submission validation
            const checkoutForm = document.getElementById('checkoutForm');
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const deliveryDate = document.getElementById('selectedDate').value;
                    const timeSlotId = document.getElementById('checkout_time_slot_id').value;

                    if (!deliveryDate) {
                        alert('Please select a delivery date');
                        return;
                    }

                    if (!timeSlotId) {
                        alert('Please select a time slot');
                        return;
                    }

                    // Set hidden form values
                    document.getElementById('checkout_date').value = deliveryDate;
                    document.getElementById('checkout_instructions').value =
                        document.getElementById('special_instructions').value;

                    // Submit the form
                    this.submit();
                });
            }

            // Auto-select today's date
            setTimeout(() => {
                const todayElement = document.querySelector('.calendar-day.today');
                if (todayElement && !todayElement.classList.contains('disabled')) {
                    todayElement.click();
                }
            }, 100);
        });
    </script>
@endpush

@push('styles')
    <style>
        /* ================================
                           General Utilities
                        ================================= */
        .object-fit-cover {
            object-fit: cover;
        }

        .sticky-top {
            position: sticky;
            top: 20px;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* ================================
                           Calendar Styles
                        ================================= */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }

        .calendar-day {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            padding: 0;
            /* ensures circle is perfect */
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease, border 0.3s ease, transform 0.2s ease;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .calendar-day:hover:not(.disabled) {
            transform: scale(1.05);
            background-color: #F56E34FF;
        }

        .calendar-day.today {
            background-color: #FC692A;
            color: white !important;
            font-weight: bold;
        }

        .calendar-day.selected {
            color: #fff !important;
            background-color: #FC692A;
            font-weight: bold;
            border: 2px solid #FC692A;
            box-sizing: border-box;
            transform: scale(1.1);
        }

        .calendar-day.disabled {
            color: #ccc;
            cursor: not-allowed;
        }

        .calendar-day-header {
            font-weight: 600;
            font-size: 0.85rem;
            color: #666;
        }

        .calendar-header {
            min-height: 40px;
        }

        .calendar-title {
            font-size: 1rem;
        }

        .calendar-nav {
            padding: 4px 12px;
            border-radius: 4px;
        }

        .text-primary-color {
            color: #FC692A;
        }

        /* ================================
                           Time Slot Styles
                        ================================= */
        .time-slots {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .time-slot {
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .time-slot:hover {
            border-color: #FC692A;
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .time-slot.selected {
            border-color: #FC692A;
            background-color: #e7f1ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
        }

        .time-slot .time-slot-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        /* ================================
                           Buttons
                        ================================= */
        /* Checkout Button */
        .checkout-btn {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            padding: 12px 16px;
            gap: 10px;
            color: #e7f1ff;
            width: 325px;
            height: 57px;
            background: #FC692A;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .checkout-btn:hover {
            background: #e55a22;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(252, 105, 42, 0.3);
        }

        .checkout-btn-text {
            font-size: 24px;
            color: #FFFFFF;
            text-align: center;
        }

        /* Continue Shopping Button */
        .continue-shopping-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            gap: 10px;
            border-radius: 12px;
            border: 2px solid #FC692A;
            color: #FC692A;
            background-color: #fff;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .continue-shopping-btn:hover {
            background-color: #FC692A;
            color: #fff;
            transform: translateY(-2px);
        }

        /* ================================
                           Responsive Media Queries
                        ================================= */
        @media (max-width: 1200px) {
            .time-slots {
                grid-template-columns: repeat(4, 1fr);
            }

            .checkout-btn {
                width: 300px;
            }

            .checkout-btn-text {
                font-size: 22px;
            }
        }

        @media (max-width: 992px) {
            .time-slots {
                grid-template-columns: repeat(3, 1fr);
            }

            .checkout-btn {
                width: 280px;
                height: 54px;
            }

            .checkout-btn-text {
                font-size: 20px;
                line-height: 30px;
            }
        }

        @media (max-width: 768px) {
            .time-slots {
                grid-template-columns: repeat(2, 1fr);
            }

            .calendar-grid {
                gap: 2px;
            }

            .calendar-day {
                padding: 6px 2px;
                font-size: 0.8rem;
            }

            .checkout-btn {
                width: 100%;
                max-width: 325px;
            }
        }

        @media (max-width: 576px) {
            .time-slots {
                grid-template-columns: 1fr;
            }

            .checkout-btn {
                width: 100%;
                height: 50px;
            }

            .checkout-btn-text {
                font-size: 18px;
                line-height: 28px;
            }
        }
    </style>
@endpush
