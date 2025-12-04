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
                                        <th>SL</th>
                                        <th width="40%">Package</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $subtotal = 0; @endphp
                                    @foreach ($guestCartItems as $key => $item)
                                        @php
                                            $itemTotal = $item->cart_amount * $item->quantity;
                                            $subtotal += $itemTotal;
                                        @endphp
                                        <tr class="border-bottom">
                                            <td>{{ ++$key }}</td>
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
                                                <span class="fw-bold">TK {{ number_format($itemTotal, 2) }}</span>
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

                            <!-- ----------------- Calendar Section ----------------- -->
                            <div class="date-selection mb-4">
                                <h6 class="fw-medium mb-3"><i class="fas fa-calendar-alt me-2"></i>Choose Date</h6>
                                <div class="calendar-container border rounded p-3 d-flex gap-4 flex-wrap">
                                    <!-- Current Month -->
                                    <div class="calendar flex-grow-1">
                                        <div class="calendar-header d-flex justify-content-between align-items-center mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="changeMonth(-1)">
                                                &lt;
                                            </button>
                                            <span class="calendar-title fw-bold" id="currentMonth"></span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="changeMonth(1)">
                                                &gt;
                                            </button>
                                        </div>
                                        <div class="calendar-weekdays d-grid"
                                            style="grid-template-columns: repeat(7, 1fr); text-align:center; font-weight:bold; margin-bottom:4px;">
                                            <div>Sun</div>
                                            <div>Mon</div>
                                            <div>Tue</div>
                                            <div>Wed</div>
                                            <div>Thu</div>
                                            <div>Fri</div>
                                            <div>Sat</div>
                                        </div>
                                        <div class="calendar-grid" id="calendarGridCurrent"></div>
                                    </div>

                                    <!-- Next Month -->
                                    <div class="calendar flex-grow-1">
                                        <div class="calendar-header d-flex justify-content-between align-items-center mb-2">
                                            <span class="calendar-title fw-bold" id="nextMonth"></span>
                                        </div>
                                        <div class="calendar-weekdays d-grid"
                                            style="grid-template-columns: repeat(7, 1fr); text-align:center; font-weight:bold; margin-bottom:4px;">
                                            <div>Sun</div>
                                            <div>Mon</div>
                                            <div>Tue</div>
                                            <div>Wed</div>
                                            <div>Thu</div>
                                            <div>Fri</div>
                                            <div>Sat</div>
                                        </div>
                                        <div class="calendar-grid" id="calendarGridNext"></div>
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

                            <!-- ----------------- Time Slots Section ----------------- -->
                            <div class="time-selection mb-4 d-none" id="timeSelectionContainer">
                                <h6 class="fw-medium mb-3"><i class="fas fa-clock me-2"></i>Choose Preferred Slot</h6>
                                <div class="time-slots-container">
                                    <div class="time-slots" id="timeSlots"></div>
                                    <div id="selectedSlotDisplay" class="mt-3 p-2 bg-light rounded text-center d-none">
                                        <span class="text-primary-color fw-medium">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Selected Slot: <span id="displaySlot"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- ----------------- Price Summary ----------------- -->
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span class="fw-medium">TK {{ number_format($subtotal, 2) }}</span>
                                </div>

                                @php $total = $subtotal; @endphp

                                <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                                    <span class="fw-bold">Total Amount</span>
                                    <span class="fw-bold fs-5">TK {{ number_format($total, 2) }}</span>
                                </div>
                            </div>

                            <!-- ----------------- Buttons ----------------- -->
                            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                                <a href="{{ url('custom-packages') }}" class="btn continue-shopping-btn">
                                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                                </a>

                                <button type="submit" class="checkout-btn">
                                    Proceed to Checkout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-4"><i class="fas fa-shopping-cart fa-4x text-muted"></i></div>
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
            const backendTimeSlots = @json($time_slots ?? []);
            const timeSlots = backendTimeSlots.map(slot => ({
                id: slot.id,
                time: `${slot.start_time} - ${slot.end_time}`,
                start_time: slot.start_time,
                end_time: slot.end_time,
                name: slot.name ?? null
            }));

            let today = new Date();
            let currentMonth = today.getMonth();
            let currentYear = today.getFullYear();
            let selectedDate = null;
            let selectedTimeSlot = null;
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August",
                "September", "October", "November", "December"
            ];

            function formatDate(date) {
                return `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`;
            }

            function formatDateDisplay(date) {
                return date.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }

            function renderCalendarMonth(gridId, month, year) {
                const container = document.getElementById(gridId);
                container.innerHTML = '';
                const firstDay = new Date(year, month, 1);
                const startingDay = firstDay.getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();

                for (let i = 0; i < startingDay; i++) {
                    const emptyCell = document.createElement('div');
                    emptyCell.className = 'calendar-day disabled';
                    container.appendChild(emptyCell);
                }

                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(year, month, day);
                    const dayEl = document.createElement('div');
                    dayEl.className = 'calendar-day';
                    dayEl.textContent = day;

                    if (date < new Date(today.getFullYear(), today.getMonth(), today.getDate())) dayEl.classList
                        .add('disabled');
                    if (date.toDateString() === today.toDateString()) dayEl.classList.add('today');
                    if (selectedDate && date.toDateString() === selectedDate.toDateString()) dayEl.classList.add(
                        'selected');

                    dayEl.addEventListener('click', () => selectDate(date, dayEl));
                    container.appendChild(dayEl);
                }
            }

            function renderTwoMonths() {
                renderCalendarMonth('calendarGridCurrent', currentMonth, currentYear);
                document.getElementById('currentMonth').textContent = monthNames[currentMonth] + ' ' + currentYear;

                let nextMonth = currentMonth + 1,
                    nextYear = currentYear;
                if (nextMonth > 11) {
                    nextMonth = 0;
                    nextYear++;
                }
                renderCalendarMonth('calendarGridNext', nextMonth, nextYear);
                document.getElementById('nextMonth').textContent = monthNames[nextMonth] + ' ' + nextYear;
            }

            function changeMonth(offset) {
                currentMonth += offset;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                renderTwoMonths();
            }

            function selectDate(date, el) {
                document.querySelectorAll('.calendar-day.selected').forEach(d => d.classList.remove('selected'));
                el.classList.add('selected');
                selectedDate = date;
                document.getElementById('selectedDate').value = formatDate(date);
                document.getElementById('displayDate').textContent = formatDateDisplay(date);
                document.getElementById('selectedDateDisplay').classList.remove('d-none');
                showTimeSelection();
            }

            function showTimeSelection() {
                const container = document.getElementById('timeSelectionContainer');
                container.classList.remove('d-none');
                populateTimeSlots();
                container.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }

            function populateTimeSlots() {
                const container = document.getElementById('timeSlots');
                container.innerHTML = '';
                timeSlots.forEach(slot => {
                    const el = document.createElement('div');
                    el.className = 'time-slot';
                    if (selectedTimeSlot && selectedTimeSlot.id === slot.id) el.classList.add('selected');
                    el.dataset.slotId = slot.id;
                    el.innerHTML =
                        `<div class="time-slot-content">${slot.name ? `<strong class="text-muted d-block mt-1">${slot.name}</strong>` : ''}</div>`;

                    el.addEventListener('click', function() {
                        document.querySelectorAll('.time-slot.selected').forEach(s => s.classList
                            .remove('selected'));
                        this.classList.add('selected');
                        selectedTimeSlot = slot;

                        document.getElementById('displaySlot').textContent = slot.name;
                        document.getElementById('selectedSlotDisplay').classList.remove('d-none');

                        document.getElementById('checkout_time_slot_id').value = slot.id;
                        document.getElementById('checkout_time_slot_text').value = slot.time;
                    });

                    container.appendChild(el);
                });
            }

            renderTwoMonths();
            setTimeout(() => {
                const todayEl = document.querySelector('.calendar-day.today:not(.disabled)');
                if (todayEl) todayEl.click();
            }, 100);
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Calendar & Time Slots CSS */
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

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }

        .date-selection {
            max-height: 419px;
            overflow-y: scroll;
        }

        .calendar-day {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 0.9rem;
            cursor: pointer;
            transition: 0.3s;
        }

        .calendar-day:hover:not(.disabled) {
            transform: scale(1.05);
            background-color: #F56E34FF;
        }

        .calendar-day.today {
            background-color: #FC692A;
            color: white;
            font-weight: bold;
        }

        .calendar-day.selected {
            color: #fff;
            background-color: #FC692A;
            font-weight: bold;
            border: 2px solid #FC692A;
            transform: scale(1.1);
        }

        .calendar-day.disabled {
            color: #ccc;
            cursor: not-allowed;
        }

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
            text-align: center;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .time-slot:hover {
            border-color: #FC692A;
            background-color: #FFEAE1;
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

        .checkout-btn {
            display: flex;
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
            transition: 0.3s;
        }

        .checkout-btn:hover {
            background: #e55a22;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(252, 105, 42, 0.3);
        }

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
            transition: 0.3s;
        }

        .continue-shopping-btn:hover {
            background-color: #FC692A;
            color: #fff;
            transform: translateY(-2px);
        }

        @media(max-width:1200px) {
            .time-slots {
                grid-template-columns: repeat(4, 1fr);
            }

            .checkout-btn {
                width: 300px;
            }
        }

        @media(max-width:992px) {
            .time-slots {
                grid-template-columns: repeat(3, 1fr);
            }

            .checkout-btn {
                width: 280px;
                height: 54px;
            }
        }

        @media(max-width:768px) {
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

        @media(max-width:576px) {
            .time-slots {
                grid-template-columns: 1fr;
            }

            .checkout-btn {
                width: 100%;
                height: 50px;
            }
        }
    </style>
@endpush
