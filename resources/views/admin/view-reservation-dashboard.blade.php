@extends('layouts.admin')

@section('title', 'Reservation History - Past Dates')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/reservation-view-reservation.css') }}">
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Debug form submission
            const form = document.querySelector('.filter-controls');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('Form submitted with data:', {
                        date_from: form.querySelector('input[name="date_from"]').value,
                        date_to: form.querySelector('input[name="date_to"]').value,
                        vehicle_type: form.querySelector('select[name="vehicle_type"]').value,
                        booking_status: form.querySelector('select[name="booking_status"]').value,
                        payment_status: form.querySelector('select[name="payment_status"]').value,
                        search: form.querySelector('input[name="search"]').value
                    });
                });
            }

            // Date range filtering enhancements
            const dateFromInput = document.getElementById('date_from');
            const dateToInput = document.getElementById('date_to');

            // Validate date range
            function validateDateRange() {
                if (dateFromInput.value && dateToInput.value) {
                    if (dateFromInput.value > dateToInput.value) {
                        alert('From date cannot be later than To date');
                        dateToInput.value = dateFromInput.value;
                    }
                }
            }

            // Add event listeners for date validation
            if (dateFromInput) {
                dateFromInput.addEventListener('change', function() {
                    if (dateToInput && !dateToInput.value) {
                        dateToInput.min = this.value;
                    }
                    validateDateRange();
                });
            }

            if (dateToInput) {
                dateToInput.addEventListener('change', function() {
                    if (dateFromInput && !dateFromInput.value) {
                        dateFromInput.max = this.value;
                    }
                    validateDateRange();
                });
            }

            // Handle export button click
            const exportBtn = document.querySelector('a[href*="export/history"]');
            if (exportBtn) {
                exportBtn.addEventListener('click', function(e) {
                    // Show loading state
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Exporting...';
                    this.classList.add('disabled');

                    // Re-enable after a short delay (in case of error)
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.classList.remove('disabled');
                    }, 5000);

                    // Show success message after download starts
                    setTimeout(() => {
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
                        alertDiv.innerHTML = `
                    <i class="fas fa-check-circle"></i> Export completed successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                        document.querySelector('.dashboard-area').appendChild(alertDiv);

                        // Auto-remove after 5 seconds
                        setTimeout(() => {
                            if (alertDiv.parentNode) {
                                alertDiv.remove();
                            }
                        }, 5000);
                    }, 1000);
                });
            }
        });
    </script>
@endpush

@section('content')
    <div class="dashboard-area">
        <div class="dashboard-header">
            <div class="mb-3">
                <div>
                    <h1>Reservation History</h1>
                    <p class="text-muted mb-0">View all past reservations including confirmed, cancelled, and completed (Past
                        dates only)</p>
                </div>
            </div>
            <form method="GET" action="{{ route('admin.view-reservation-dashboard') }}" class="filter-controls">
                <div style="display: flex; gap: 10px;" class="filter-controls-container">
                    <div class="">
                        <label for="date_from" class="form-label small text-muted">From Date</label>
                        <input type="date" name="date_from" id="date_from" class="form-select"
                            value="{{ request('date_from') }}">
                    </div>
                    <div class="">
                        <label for="date_to" class="form-label small text-muted">To Date</label>
                        <input type="date" name="date_to" id="date_to" class="form-select"
                            value="{{ request('date_to') }}">
                    </div>
                    <div class="">
                        <label for="vehicle_type" class="form-label small text-muted">Vehicle Type</label>
                        <select name="vehicle_type" id="vehicle_type" class="form-select">
                            <option value="">All Vehicle Types</option>
                            @foreach ($vehicleTypes ?? [] as $vehicleType)
                                <option value="{{ $vehicleType->name }}"
                                    {{ request('vehicle_type') == $vehicleType->name ? 'selected' : '' }}>
                                    {{ $vehicleType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="">
                        <label for="booking_status" class="form-label small text-muted">Booking Status</label>
                        <select name="booking_status" id="booking_status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('booking_status') == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="confirmed" {{ request('booking_status') == 'confirmed' ? 'selected' : '' }}>
                                Confirmed</option>
                            <option value="cancelled" {{ request('booking_status') == 'cancelled' ? 'selected' : '' }}>
                                Cancelled</option>
                            <option value="completed" {{ request('booking_status') == 'completed' ? 'selected' : '' }}>
                                Completed</option>
                        </select>
                    </div>
                    <div class="">
                        <label for="payment_status" class="form-label small text-muted">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <option value="">All Payment Statuses</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial
                            </option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid
                            </option>
                            <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>
                                Refunded</option>
                        </select>
                    </div>
                    <div class="">
                        <label for="search" class="form-label small text-muted">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Search booking code, customer, or vehicle type" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        <a href="{{ route('admin.view-reservation-dashboard') }}"
                            class="btn btn-secondary btn-sm">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Date Range Info -->
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bi bi-clock-history me-2"></i>
            <div>
                @if (request('date_from') || request('date_to'))
                    <strong>Showing:</strong>
                    @if (request('date_from') && request('date_to'))
                        Past bookings from {{ \Carbon\Carbon::parse(request('date_from'))->format('M j, Y') }} to
                        {{ \Carbon\Carbon::parse(request('date_to'))->format('M j, Y') }}
                    @elseif(request('date_from'))
                        Past bookings from {{ \Carbon\Carbon::parse(request('date_from'))->format('M j, Y') }} onwards
                    @elseif(request('date_to'))
                        Past bookings up to {{ \Carbon\Carbon::parse(request('date_to'))->format('M j, Y') }}
                    @endif
                @else
                    <strong>Showing:</strong> Past dates only
                @endif
                <br>
                <small class="text-muted">Current date: {{ now()->format('F j, Y') }}</small>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-container">
            <div class="table-responsive">
                <table class="table align-middle responsive-stacked">
                    <thead class="table-light fw-bold">
                        <tr>
                            <th>Booking Code</th>
                            <th>Date</th>
                            <th>Package Name</th>
                            <th>Vehicle Type</th>
                            <th>Report Time</th>
                            <th>Schedule Slot</th>
                            <th>Guest Name</th>
                            <th>Guest Phone</th>
                            <th>Party Size</th>
                            <th>Total</th>
                            <th>Deposit</th>
                            <th>Booking Status</th>
                            <th>Payment Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-secondary">
                        @forelse($groupedReservations ?? [] as $group)
                            @php
                                $primaryReservation = $group['primary_reservation'];
                                $reservations = $group['reservations'];
                                $isMultiPackage = $reservations->count() > 1;
                            @endphp
                            <tr class="{{ $isMultiPackage ? 'table-info' : '' }}">
                                <td data-label="Booking Code">
                                    <strong>{{ $primaryReservation->booking_code }}</strong>
                                    @if ($isMultiPackage)
                                        <br><small class="text-muted">+{{ $reservations->count() - 1 }} more
                                            package(s)</small>
                                    @endif
                                </td>
                                <td data-label="Date">{{ date('m/d/Y', strtotime($primaryReservation->date)) }}</td>
                                <td data-label="Package Name">
                                    @if ($isMultiPackage)
                                        <div class="fw-bold">Multi-Package Booking</div>
                                        <div class="small text-muted">
                                            @foreach ($reservations as $reservation)
                                                <div>{{ $reservation->packageVariant->package->name ?? 'N/A' }}</div>
                                            @endforeach
                                        </div>
                                    @else
                                        {{ $primaryReservation->packageVariant->package->name ?? 'N/A' }}
                                    @endif
                                </td>
                                <td data-label="Vehicle Type">
                                    @if ($isMultiPackage)
                                        <div class="small">
                                            @foreach ($reservations as $reservation)
                                                @if ($reservation->packageVariant->package->vehicleTypes->isNotEmpty())
                                                    @foreach ($reservation->packageVariant->package->vehicleTypes as $vehicleType)
                                                        <span
                                                            class="badge bg-primary me-1 mb-1">{{ $vehicleType->name }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        @if ($primaryReservation->packageVariant->package->vehicleTypes->isNotEmpty())
                                            @foreach ($primaryReservation->packageVariant->package->vehicleTypes as $vehicleType)
                                                <span class="badge bg-primary me-1">{{ $vehicleType->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    @endif
                                </td>
                                <td data-label="Report Time">
                                    {{ $primaryReservation->report_time ? date('g:i A', strtotime($primaryReservation->report_time)) : 'N/A' }}
                                </td>
                                <td data-label="Schedule Slot">
                                    @if ($isMultiPackage)
                                        <div class="small">
                                            @foreach ($reservations as $reservation)
                                                @if ($reservation->scheduleSlot)
                                                    <div>{{ $reservation->scheduleSlot->name }}</div>
                                                    <div class="text-muted">
                                                        {{ \Carbon\Carbon::parse($reservation->scheduleSlot->start_time)->format('g A') }}
                                                        -
                                                        {{ \Carbon\Carbon::parse($reservation->scheduleSlot->end_time)->format('g A') }}
                                                    </div>
                                                @else
                                                    <div>N/A</div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        @if ($primaryReservation->scheduleSlot)
                                            {{ $primaryReservation->scheduleSlot->name }}
                                            <div class="small text-muted">
                                                {{ \Carbon\Carbon::parse($primaryReservation->scheduleSlot->start_time)->format('g A') }}
                                                -
                                                {{ \Carbon\Carbon::parse($primaryReservation->scheduleSlot->end_time)->format('g A') }}
                                            </div>
                                        @else
                                            N/A
                                        @endif
                                    @endif
                                </td>
                                <td data-label="Guest Name">{{ $primaryReservation->customer->name ?? 'N/A' }}</td>
                                <td data-label="Guest Phone">
                                    {{-- prettier-ignore --}}
                                    {{ $primaryReservation->customer && $primaryReservation->customer->phone
                                    ? (new
                                    \App\Services\PhoneNumberService())->formatForDisplayWithoutPrefix($primaryReservation->customer->phone)
                                    : 'N/A' }}
                                </td>
                                <td data-label="Party Size">{{ $reservations->sum('party_size') }}</td>
                                <td data-label="Total">৳{{ number_format($group['total_amount'], 2) }}</td>
                                <td data-label="Deposit">৳{{ number_format($reservations->sum('deposit_amount'), 2) }}
                                </td>
                                <td data-label="Booking Status">
                                    @php
                                        $statusClass = '';
                                        $statusIcon = '';
                                        switch ($primaryReservation->booking_status) {
                                            case 'confirmed':
                                                $statusClass = 'status-confirmed';
                                                $statusIcon = 'bi-check-circle-fill text-success';
                                                break;
                                            case 'pending':
                                                $statusClass = 'status-pending';
                                                $statusIcon = 'bi-clock-fill text-warning';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'status-cancelled';
                                                $statusIcon = 'bi-x-circle-fill text-danger';
                                                break;
                                            case 'completed':
                                                $statusClass = 'status-completed';
                                                $statusIcon = 'bi-check-circle-fill text-success';
                                                break;
                                        }
                                    @endphp
                                    <span class="status-badge {{ $statusClass }} d-flex align-items-center gap-1">
                                        <i class="bi {{ $statusIcon }}"></i>
                                        {{ ucfirst($primaryReservation->booking_status) }}
                                    </span>
                                </td>
                                <td data-label="Payment Status">
                                    @php
                                        $paymentStatusClass = '';
                                        $paymentStatusIcon = '';
                                        switch ($primaryReservation->payment_status) {
                                            case 'paid':
                                                $paymentStatusClass = 'status-confirmed';
                                                $paymentStatusIcon = 'bi-check-circle-fill text-success';
                                                break;
                                            case 'partial':
                                                $paymentStatusClass = 'status-pending';
                                                $paymentStatusIcon = 'bi-clock-fill text-warning';
                                                break;
                                            case 'pending':
                                                $paymentStatusClass = 'status-pending';
                                                $paymentStatusIcon = 'bi-clock-fill text-warning';
                                                break;
                                            case 'refunded':
                                                $paymentStatusClass = 'status-cancelled';
                                                $paymentStatusIcon = 'bi-x-circle-fill text-danger';
                                                break;
                                        }
                                    @endphp
                                    <span class="status-badge {{ $paymentStatusClass }} d-flex align-items-center gap-1">
                                        <i class="bi {{ $paymentStatusIcon }}"></i>
                                        {{ ucfirst($primaryReservation->payment_status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center py-4">
                                    <i class="fas fa-calendar-alt fa-3x text-muted mb-3 d-block"></i>
                                    <h5>No Past Reservations Found</h5>
                                    <p class="text-muted">No past reservations found matching your filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-controls d-flex flex-wrap justify-content-between align-items-center mt-3">
                <div class="d-flex align-items-center gap-2">
                    <span>Total Past Reservations: {{ ($reservations ?? collect())->count() }}</span>
                    @if (request()->hasAny(['vehicle_type', 'search', 'booking_status', 'payment_status', 'date_from', 'date_to']))
                        <span class="text-muted">(Filtered results)</span>
                    @endif
                    <span class="text-muted">(Past dates only)</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span>Status Summary:</span>
                        <span
                            class="badge bg-warning">{{ ($reservations ?? collect())->where('booking_status', 'pending')->count() }}
                            Pending</span>
                        <span
                            class="badge bg-success">{{ ($reservations ?? collect())->where('booking_status', 'confirmed')->count() }}
                            Confirmed</span>
                        <span
                            class="badge bg-info">{{ ($reservations ?? collect())->where('booking_status', 'completed')->count() }}
                            Completed</span>
                        <span
                            class="badge bg-danger">{{ ($reservations ?? collect())->where('booking_status', 'cancelled')->count() }}
                            Cancelled</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span>Vehicle Types:</span>
                        @php
                            $atvCount = ($reservations ?? collect())
                                ->filter(function ($reservation) {
                                    return $reservation->packageVariant->package->vehicleTypes->contains('name', 'ATV');
                                })
                                ->count();
                            $utvCount = ($reservations ?? collect())
                                ->filter(function ($reservation) {
                                    return $reservation->packageVariant->package->vehicleTypes->contains('name', 'UTV');
                                })
                                ->count();
                            $regularCount = ($reservations ?? collect())
                                ->filter(function ($reservation) {
                                    return $reservation->packageVariant->package->type === 'regular';
                                })
                                ->count();
                        @endphp
                        <span class="badge bg-primary">{{ $atvCount }} ATV</span>
                        <span class="badge bg-secondary">{{ $utvCount }} UTV</span>
                        <span class="badge bg-info">{{ $regularCount }} Regular</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('admin.reservations.export.history', request()->query()) }}"
                            class="btn btn-success btn-sm">
                            <i class="bi bi-download me-1"></i>Export CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        @media (max-width: 768px) {
            .filter-controls-container {
                flex-wrap: wrap;
            }

            .filter-controls-container div {
                width: 47% !important;
            }

            .filter-controls .row .col-md-12 {
                flex-direction: row !important;
            }
        }
    </style>
@endsection
