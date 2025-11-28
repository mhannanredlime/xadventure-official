@extends('layouts.admin')

@section('title', 'Reservations - Today & Future')

@push('styles')
  <link rel="stylesheet" href="{{ asset('admin/css/reservation-view-reservation.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle export button click
    const exportBtn = document.querySelector('a[href*="export/pending"]');
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
                    <i class="bi  bi-check-circle"></i> Export completed successfully!
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

    // Date range filtering enhancements
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');

    // Set today as default for date_from if not set
    if (dateFromInput && !dateFromInput.value) {
        const today = new Date().toISOString().split('T')[0];
        dateFromInput.value = today;
    }

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

});
</script>
@endpush

@section('content')
  <div class="dashboard-area">
    <div class="dashboard-header">
      <div class="mb-3">
        <div>
          <h1>Reservations Dashboard</h1>
          <p class="text-muted mb-0">Manage all reservations from current date onwards (Today and Future dates only)</p>
        </div>
      </div>
      <form method="GET" action="{{ route('admin.reservations.index') }}" class="filter-controls">
        <div class="row g-3">
          <div class="col-md-3">
            <label for="date_from" class="form-label small text-muted">From Date</label>
            <input type="date" name="date_from" id="date_from" class="form-select" value="{{ request('date_from') }}">
          </div>
          <div class="col-md-3">
            <label for="date_to" class="form-label small text-muted">To Date</label>
            <input type="date" name="date_to" id="date_to" class="form-select" value="{{ request('date_to') }}">
          </div>
          <div class="col-md-2">
            <label for="vehicle_type" class="form-label small text-muted">Vehicle Type</label>
            <select name="vehicle_type" id="vehicle_type" class="form-select">
              <option value="">All Vehicle Types</option>
              @foreach($vehicleTypes ?? [] as $vehicleType)
                <option value="{{ $vehicleType->name }}" {{ request('vehicle_type') == $vehicleType->name ? 'selected' : '' }}>
                  {{ $vehicleType->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label for="booking_status" class="form-label small text-muted">Booking Status</label>
            <select name="booking_status" id="booking_status" class="form-select">
              <option value="">All Statuses</option>
              <option value="pending" {{ request('booking_status') == 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="confirmed" {{ request('booking_status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
              <option value="cancelled" {{ request('booking_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
              <option value="completed" {{ request('booking_status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
          </div>
          <div class="col-md-2">
            <label for="payment_status" class="form-label small text-muted">Payment Status</label>
            <select name="payment_status" id="payment_status" class="form-select">
              <option value="">All Payment Statuses</option>
              <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
              <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
              <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
            </select>
          </div>
          <div class="col-md-2">
            <label for="search" class="form-label small text-muted">Search</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Booking code, customer, or vehicle type" value="{{ request('search') }}">
          </div>
          <div class="col-md-1 d-flex align-items-end">
            <div class="d-flex gap-1">
              <button type="submit" class="btn btn-primary btn-sm">Filter</button>
              <a href="{{ route('admin.reservations.index') }}" class="btn btn-secondary btn-sm">Clear</a>
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- Date Range Info -->
    <div class="alert alert-info d-flex align-items-center" role="alert">
      <i class="bi bi-calendar-event me-2"></i>
      <div>
        @if(request('date_from') || request('date_to'))
          <strong>Showing:</strong>
          @if(request('date_from') && request('date_to'))
            Bookings from {{ \Carbon\Carbon::parse(request('date_from'))->format('M j, Y') }} to {{ \Carbon\Carbon::parse(request('date_to'))->format('M j, Y') }}
          @elseif(request('date_from'))
            Bookings from {{ \Carbon\Carbon::parse(request('date_from'))->format('M j, Y') }} onwards
          @elseif(request('date_to'))
            Bookings up to {{ \Carbon\Carbon::parse(request('date_to'))->format('M j, Y') }}
          @endif
        @else
          <strong>Showing:</strong> All bookings from current date onwards
        @endif
        <br>
        <small class="text-muted">Current date: {{ now()->format('F j, Y') }}</small>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi  bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi  bi-exclamation-triangle"></i> {{ session('error') }}
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
            <th>Actions</th>
          </tr>
          </thead>
          <tbody class="text-secondary">
            @forelse($groupedReservations as $group)
              @php
                $primaryReservation = $group['primary_reservation'];
                $reservations = $group['reservations'];
                $isMultiPackage = $reservations->count() > 1;
              @endphp
              <tr class="{{ $isMultiPackage ? 'table-info' : '' }}">
                <td data-label="Booking Code">
                  <strong>{{ $primaryReservation->booking_code }}</strong>
                  @if($isMultiPackage)
                    <br><small class="text-muted">+{{ $reservations->count() - 1 }} more package(s)</small>
                  @endif
                </td>
                <td data-label="Date">{{ date('m/d/Y', strtotime($primaryReservation->date)) }}</td>
                <td data-label="Package Name">
                  @if($isMultiPackage)
                    <div class="fw-bold">Multi-Package Booking</div>
                    <div class="small text-muted">
                      @foreach($reservations as $reservation)
                        <div>{{ $reservation->packageVariant->package->name ?? 'N/A' }}</div>
                      @endforeach
                    </div>
                  @else
                    {{ $primaryReservation->packageVariant->package->name ?? 'N/A' }}
                  @endif
                </td>
                <td data-label="Vehicle Type">
                  @if($isMultiPackage)
                    <div class="small">
                      @foreach($reservations as $reservation)
                        @if($reservation->packageVariant->package->vehicleTypes->isNotEmpty())
                          @foreach($reservation->packageVariant->package->vehicleTypes as $vehicleType)
                            <span class="badge bg-primary me-1 mb-1">{{ $vehicleType->name }}</span>
                          @endforeach
                        @else
                          <span class="text-muted">N/A</span>
                        @endif
                      @endforeach
                    </div>
                  @else
                    @if($primaryReservation->packageVariant->package->vehicleTypes->isNotEmpty())
                      @foreach($primaryReservation->packageVariant->package->vehicleTypes as $vehicleType)
                        <span class="badge bg-primary me-1">{{ $vehicleType->name }}</span>
                      @endforeach
                    @else
                      <span class="text-muted">N/A</span>
                    @endif
                  @endif
                </td>
                <td data-label="Report Time">{{ $primaryReservation->report_time ? date('g:i A', strtotime($primaryReservation->report_time)) : 'N/A' }}</td>
                <td data-label="Schedule Slot">
                  @if($isMultiPackage)
                    <div class="small">
                      @foreach($reservations as $reservation)
                        @if($reservation->scheduleSlot)
                          <div>{{ $reservation->scheduleSlot->name }}</div>
                          <div class="text-muted">
                            {{ \Carbon\Carbon::parse($reservation->scheduleSlot->start_time)->format('g A') }} - {{ \Carbon\Carbon::parse($reservation->scheduleSlot->end_time)->format('g A') }}
                          </div>
                        @else
                          <div>N/A</div>
                        @endif
                      @endforeach
                    </div>
                  @else
                    @if($primaryReservation->scheduleSlot)
                      {{ $primaryReservation->scheduleSlot->name }}
                      <div class="small text-muted">
                        {{ \Carbon\Carbon::parse($primaryReservation->scheduleSlot->start_time)->format('g A') }} - {{ \Carbon\Carbon::parse($primaryReservation->scheduleSlot->end_time)->format('g A') }}
                      </div>
                    @else
                      N/A
                    @endif
                  @endif
                </td>
                <td data-label="Guest Name">{{ $primaryReservation->customer->name ?? 'N/A' }}</td>
                <td data-label="Guest Phone">{{ $primaryReservation->customer->phone ? (new \App\Services\PhoneNumberService())->formatForDisplayWithoutPrefix($primaryReservation->customer->phone) : 'N/A' }}</td>
                <td data-label="Party Size">{{ $reservations->sum('party_size') }}</td>
                <td data-label="Total">৳{{ number_format($group['total_amount'], 2) }}</td>
                <td data-label="Deposit">৳{{ number_format($reservations->sum('deposit_amount'), 2) }}</td>
                <td data-label="Booking Status">
                  @php
                    $statusClass = '';
                    $statusIcon = '';
                    switch($primaryReservation->booking_status) {
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
                    switch($primaryReservation->payment_status) {
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
                <td data-label="Actions">
                  <div class="btn-group" role="group">
                    @can('reservations.view')
                    <button type="button" class="btn btn-sm btn-outline-primary" title="View" data-bs-toggle="modal" data-bs-target="#viewModal{{ $primaryReservation->id }}">
                      <i class="bi bi-eye"></i>
                    </button>
                    @endcan
                    @can('reservations.edit')
                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $primaryReservation->id }}">
                      <i class="bi bi-pencil"></i>
                    </button>
                    @endcan
                    @can('reservations.view')
                    @if($isMultiPackage)
                      <button type="button" class="btn btn-sm btn-outline-info" title="View All Packages" data-bs-toggle="modal" data-bs-target="#multiPackageModal{{ $primaryReservation->id }}">
                        <i class="bi bi-list-ul"></i>
                      </button>
                    @endif
                    @endcan
                    @can('reservations.edit')
                    <form method="POST" action="{{ route('admin.reservations.destroy', $primaryReservation) }}" class="d-inline delete-form" data-is-multi="{{ $isMultiPackage ? 'true' : 'false' }}" data-count="{{ $isMultiPackage ? $reservations->count() : 1 }}">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                    @endcan
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="14" class="text-center py-4">
                  <i class="bi  bi-calendar-alt fa-3x text-muted mb-3 d-block"></i>
                  <h5>No Reservations Found</h5>
                  <p class="text-muted">No reservations found for today or future dates.</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="pagination-controls d-flex flex-wrap justify-content-between align-items-center mt-3">
        <div class="d-flex align-items-center gap-2">
          <span>Total Reservations: {{ $reservations->count() }}</span>
          @if(request()->hasAny(['vehicle_type', 'search', 'date_from', 'date_to', 'booking_status', 'payment_status']))
            <span class="text-muted">(Filtered results)</span>
          @endif
          @if(request('date_from') || request('date_to'))
            <span class="text-muted">(Custom date range)</span>
          @else
            <span class="text-muted">(Current date onwards)</span>
          @endif
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center gap-2">
            <span>Status Summary:</span>
            <span class="badge bg-warning">{{ $reservations->where('booking_status', 'pending')->count() }} Pending</span>
            <span class="badge bg-success">{{ $reservations->where('booking_status', 'confirmed')->count() }} Confirmed</span>
            <span class="badge bg-info">{{ $reservations->where('booking_status', 'completed')->count() }} Completed</span>
            <span class="badge bg-danger">{{ $reservations->where('booking_status', 'cancelled')->count() }} Cancelled</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span>Vehicle Types:</span>
            @php
              $atvCount = $reservations->filter(function($reservation) {
                return $reservation->packageVariant->package->vehicleTypes->contains('name', 'ATV');
              })->count();
              $utvCount = $reservations->filter(function($reservation) {
                return $reservation->packageVariant->package->vehicleTypes->contains('name', 'UTV');
              })->count();
              $regularCount = $reservations->filter(function($reservation) {
                return $reservation->packageVariant->package->type === 'regular';
              })->count();
            @endphp
            <span class="badge bg-primary">{{ $atvCount }} ATV</span>
            <span class="badge bg-secondary">{{ $utvCount }} UTV</span>
            <span class="badge bg-info">{{ $regularCount }} Regular</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.reservations.export.pending', request()->query()) }}" class="btn btn-success btn-sm">
              <i class="bi bi-download me-1"></i>Export CSV
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Create Modal -->
  <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content modal-elevated">
        <div class="modal-header modal-header-gradient">
          <h5 class="modal-title" id="createModalLabel">
            <i class="bi bi-plus-circle me-2"></i>Create New Reservation
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
                            <form method="POST" action="{{ route('admin.reservations.store') }}">
            @csrf
            <div class="modal-body modal-body-spacious">
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="card h-100 card-elevated">
                    <div class="card-header card-header-muted">
                      <h6 class="mb-0">
                        <i class="bi bi-person-circle me-2"></i>Customer & Package Details
                      </h6>
                    </div>
                    <div class="card-body">
                      <div class="mb-3">
                        <label for="customer_id" class="form-label fw-semibold">Customer</label>
                        <select class="form-select form-control-rounded" id="customer_id" name="customer_id" required>
                          <option value="">Select Customer</option>
                          @foreach($customers ?? [] as $customer)
                            <option value="{{ $customer->id }}">
                              {{ $customer->name }} ({{ $customer->email }})
                            </option>
                          @endforeach
                        </select>
                      </div>

                      <div class="mb-3">
                        <label for="package_variant_id" class="form-label fw-semibold">Package Variant</label>
                        <select class="form-select form-control-rounded" id="package_variant_id" name="package_variant_id" required>
                          <option value="">Select Package Variant</option>
                          @foreach($packageVariants ?? [] as $variant)
                            <option value="{{ $variant->id }}">
                              {{ $variant->package->name }} - {{ $variant->variant_name }}
                            </option>
                          @endforeach
                        </select>
                      </div>

                      <div class="mb-3">
                        <label for="schedule_slot_id" class="form-label fw-semibold">Schedule Slot</label>
                        <select class="form-select form-control-rounded" id="schedule_slot_id" name="schedule_slot_id" required>
                          <option value="">Select Schedule Slot</option>
                          @foreach($scheduleSlots ?? [] as $slot)
                            <option value="{{ $slot->id }}">
                              {{ $slot->name }}
                            </option>
                          @endforeach
                        </select>
                      </div>

                      <div class="mb-3">
                        <label for="date" class="form-label fw-semibold">Date</label>
                        <input type="date" class="form-control form-control-rounded" id="date" name="date" required>
                      </div>

                      <div class="mb-3">
                        <label for="party_size" class="form-label fw-semibold">Party Size</label>
                        <input type="number" class="form-control form-control-rounded" id="party_size" name="party_size" min="1" required>
                      </div>
                    </div>
                  </div>
                </div>

                              <div class="col-md-6">
                  <div class="card h-100 card-elevated">
                    <div class="card-header card-header-muted">
                      <h6 class="mb-0">
                        <i class="bi bi-currency-exchange me-2"></i>Financial Details
                      </h6>
                    </div>
                    <div class="card-body">
                      <div class="mb-3">
                        <label for="subtotal" class="form-label fw-semibold">Subtotal</label>
                        <div class="input-group">
                          <span class="input-group-text input-group-text-muted">৳</span>
                          <input type="number" step="0.01" class="form-control form-control-rounded-right" id="subtotal" name="subtotal" required>
                        </div>
                      </div>

                      <div class="mb-3">
                        <label for="discount_amount" class="form-label fw-semibold">Discount Amount</label>
                        <div class="input-group">
                          <span class="input-group-text input-group-text-muted">৳</span>
                          <input type="number" step="0.01" class="form-control form-control-rounded-right" id="discount_amount" name="discount_amount" value="0">
                        </div>
                      </div>

                      <div class="mb-3">
                        <label for="tax_amount" class="form-label fw-semibold">Tax Amount</label>
                        <div class="input-group">
                          <span class="input-group-text input-group-text-muted">৳</span>
                          <input type="number" step="0.01" class="form-control form-control-rounded-right" id="tax_amount" name="tax_amount" value="0">
                        </div>
                      </div>

                      <div class="mb-3">
                        <label for="total_amount" class="form-label fw-semibold">Total Amount</label>
                        <div class="input-group">
                          <span class="input-group-text input-group-text-muted">৳</span>
                          <input type="number" step="0.01" class="form-control form-control-rounded-right" id="total_amount" name="total_amount" required>
                        </div>
                      </div>

                      <div class="mb-3">
                        <label for="deposit_amount" class="form-label fw-semibold">Deposit Amount</label>
                        <div class="input-group">
                          <span class="input-group-text input-group-text-muted">৳</span>
                          <input type="number" step="0.01" class="form-control form-control-rounded-right" id="deposit_amount" name="deposit_amount" value="0">
                        </div>
                      </div>

                      <div class="mb-3">
                        <label for="balance_amount" class="form-label fw-semibold">Balance Amount</label>
                        <div class="input-group">
                          <span class="input-group-text input-group-text-muted">৳</span>
                          <input type="number" step="0.01" class="form-control form-control-rounded-right" id="balance_amount" name="balance_amount" value="0">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                          </div>

              <div class="row mt-4">
                <div class="col-md-6">
                  <div class="card card-elevated">
                    <div class="card-header card-header-muted">
                      <h6 class="mb-0">
                        <i class="bi bi-calendar-check me-2"></i>Status Information
                      </h6>
                    </div>
                    <div class="card-body">
                      <div class="mb-3">
                        <label for="booking_status" class="form-label fw-semibold">Booking Status</label>
                        <select class="form-select form-control-rounded" id="booking_status" name="booking_status" required>
                          <option value="pending">Pending</option>
                          <option value="confirmed">Confirmed</option>
                          <option value="cancelled">Cancelled</option>
                          <option value="completed">Completed</option>
                        </select>
                      </div>

                      <div class="mb-3">
                        <label for="payment_status" class="form-label fw-semibold">Payment Status</label>
                        <select class="form-select form-control-rounded" id="payment_status" name="payment_status" required>
                          <option value="pending">Pending</option>
                          <option value="partial">Partial</option>
                          <option value="paid">Paid</option>
                          <option value="refunded">Refunded</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="card card-elevated">
                    <div class="card-header card-header-muted">
                      <h6 class="mb-0">
                        <i class="bi bi-chat-text me-2"></i>Additional Information
                      </h6>
                    </div>
                    <div class="card-body">
                      <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control form-control-rounded" id="notes" name="notes" rows="4" style="resize: vertical;"></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
                      </div>
            <div class="modal-footer" style="background: #f8f9fa; border-radius: 0 0 15px 15px; border: none; padding: 1.5rem;">
              <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 8px; padding: 0.5rem 1.5rem;">
                <i class="bi bi-x-circle me-2"></i>Cancel
              </button>
              <button type="submit" class="btn btn-primary" style="border-radius: 8px; padding: 0.5rem 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                <i class="bi bi-check-circle me-2"></i>Create Reservation
              </button>
            </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Modals -->
  @foreach($groupedReservations as $group)
    @php
      $primaryReservation = $group['primary_reservation'];
      $reservations = $group['reservations'];
      $isMultiPackage = $reservations->count() > 1;
    @endphp
    @foreach($reservations as $reservation)
  <div class="modal fade" id="viewModal{{ $reservation->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $reservation->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content modal-elevated">
        <div class="modal-header modal-header-gradient">
          <h5 class="modal-title" id="viewModalLabel{{ $reservation->id }}" style="font-weight: 600;">
            <i class="bi bi-eye me-2"></i>View {{ $isMultiPackage ? 'Multi-Package Booking' : 'Reservation' }} - {{ $reservation->booking_code }}
            @if($isMultiPackage)
              <span class="badge bg-info ms-2">{{ $reservations->count() }} packages</span>
            @endif
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body modal-body-spacious">
          @if($isMultiPackage)
            <!-- Multi-Package Overview -->
            <div class="row g-3 g-md-4 mb-4">
              <div class="col-12">
                <div class="card h-100 card-elevated border-info">
                  <div class="card-header card-header-muted bg-info bg-opacity-10">
                    <h6 class="mb-0 text-info">
                      <i class="bi bi-list-ul me-2"></i>Multi-Package Booking Overview
                    </h6>
                  </div>
                  <div class="card-body">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <div class="info-item">
                          <label class="info-label">Total Packages:</label>
                          <span class="info-value fw-bold text-info">{{ $reservations->count() }}</span>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="info-item">
                          <label class="info-label">Total Amount:</label>
                          <span class="info-value fw-bold text-success">৳{{ number_format($reservations->sum('total_amount'), 2) }}</span>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="info-item">
                          <label class="info-label">Total Party Size:</label>
                          <span class="info-value fw-bold">{{ $reservations->sum('party_size') }}</span>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="info-item">
                          <label class="info-label">Booking Status:</label>
                          <span class="badge bg-{{ $primaryReservation->booking_status === 'confirmed' ? 'success' : ($primaryReservation->booking_status === 'pending' ? 'warning' : 'secondary') }} px-3 py-2">
                            {{ ucfirst($primaryReservation->booking_status) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endif

          <div class="row g-3 g-md-4">
            <div class="col-12">
              <div class="card h-100 card-elevated">
                <div class="card-header card-header-muted">
                  <h6 class="mb-0">
                    <i class="bi bi-calendar-event me-2"></i>{{ $isMultiPackage ? 'Package Details' : 'Booking Information' }}
                    @if($isMultiPackage)
                      <span class="badge bg-primary ms-2">Package {{ $loop->iteration }} of {{ $reservations->count() }}</span>
                    @endif
                  </h6>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                      <tbody>
                        <tr>
                          <td class="fw-semibold text-muted" style="width: 40%;">Booking Code:</td>
                          <td class="text-break">{{ $reservation->booking_code }}</td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Date:</td>
                          <td>{{ date('m/d/Y', strtotime($reservation->date)) }}</td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Report Time:</td>
                          <td>{{ $reservation->report_time ? date('g:i A', strtotime($reservation->report_time)) : 'N/A' }}</td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Schedule Slot:</td>
                          <td class="text-break">{{ $reservation->scheduleSlot->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Party Size:</td>
                          <td>{{ $reservation->party_size }}</td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Booking Status:</td>
                          <td>
                            @php
                              $statusClass = '';
                              $statusIcon = '';
                              switch($reservation->booking_status) {
                                case 'confirmed':
                                  $statusClass = 'text-success';
                                  $statusIcon = 'bi-check-circle-fill';
                                  break;
                                case 'pending':
                                  $statusClass = 'text-warning';
                                  $statusIcon = 'bi-clock-fill';
                                  break;
                                case 'cancelled':
                                  $statusClass = 'text-danger';
                                  $statusIcon = 'bi-x-circle-fill';
                                  break;
                                case 'completed':
                                  $statusClass = 'text-success';
                                  $statusIcon = 'bi-check-circle-fill';
                                  break;
                              }
                            @endphp
                            <span class="{{ $statusClass }}">
                              <i class="bi {{ $statusIcon }}"></i>
                              {{ ucfirst($reservation->booking_status) }}
                            </span>
                          </td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Payment Status:</td>
                          <td>
                            @php
                              $paymentStatusClass = '';
                              $paymentStatusIcon = '';
                              switch($reservation->payment_status) {
                                case 'paid':
                                  $paymentStatusClass = 'text-success';
                                  $paymentStatusIcon = 'bi-check-circle-fill';
                                  break;
                                case 'partial':
                                  $paymentStatusClass = 'text-warning';
                                  $paymentStatusIcon = 'bi-clock-fill';
                                  break;
                                case 'pending':
                                  $paymentStatusClass = 'text-warning';
                                  $paymentStatusIcon = 'bi-clock-fill';
                                  break;
                                case 'refunded':
                                  $paymentStatusClass = 'text-danger';
                                  $paymentStatusIcon = 'bi-x-circle-fill';
                                  break;
                              }
                            @endphp
                            <span class="{{ $paymentStatusClass }}">
                              <i class="bi {{ $paymentStatusIcon }}"></i>
                              {{ ucfirst($reservation->payment_status) }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card h-100 card-elevated">
                <div class="card-header card-header-muted">
                  <h6 class="mb-0">
                    <i class="bi bi-person-circle me-2"></i>Customer & Package Information
                  </h6>
                </div>
                <div class="card-body">
                  <h6 class="text-primary mb-3">Customer Details</h6>
                  <div class="table-responsive mb-4">
                    <table class="table table-sm table-borderless mb-0">
                      <tbody>
                        <tr>
                          <td class="fw-semibold text-muted" style="width: 30%;">Name:</td>
                          <td class="text-break">{{ $reservation->customer->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Email:</td>
                          <td class="text-break">{{ $reservation->customer->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Phone:</td>
                          <td class="text-break">{{ $reservation->customer->phone ? (new \App\Services\PhoneNumberService())->formatForDisplayWithoutPrefix($reservation->customer->phone) : 'N/A' }}</td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Address:</td>
                          <td class="text-break">{{ $reservation->customer->address ?? 'N/A' }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <h6 class="text-primary mb-3">Package Details</h6>
                  <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                      <tbody>
                        <tr>
                          <td class="fw-semibold text-muted" style="width: 30%;">Package:</td>
                          <td class="text-break">{{ $reservation->packageVariant->package->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Variant:</td>
                          <td class="text-break">{{ $reservation->packageVariant->variant_name ?? 'N/A' }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-3 mt-md-4">
            <div class="col-12">
              <div class="card card-elevated">
                <div class="card-header card-header-muted">
                  <h6 class="mb-0">
                    <i class="bi bi-currency-exchange me-2"></i>Financial Information
                  </h6>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                      <tbody>
                        <tr>
                          <td class="fw-semibold text-muted" style="width: 30%;">Subtotal:</td>
                          <td>৳{{ number_format($reservation->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Discount:</td>
                          <td>৳{{ number_format($reservation->discount_amount ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Tax:</td>
                          <td>৳{{ number_format($reservation->tax_amount ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Total Amount:</td>
                          <td><strong class="text-primary">৳{{ number_format($reservation->total_amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Deposit:</td>
                          <td>৳{{ number_format($reservation->deposit_amount ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                          <td class="fw-semibold text-muted">Balance:</td>
                          <td>৳{{ number_format($reservation->balance_amount ?? 0, 2) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          @if($reservation->notes)
          <div class="row mt-4">
            <div class="col-12">
              <div class="card" style="border-radius: 10px; border: 1px solid #e9ecef; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <div class="card-header" style="background: #f8f9fa; border-radius: 10px 10px 0 0; border: none;">
                  <h6 class="mb-0" style="color: #495057; font-weight: 600;">
                    <i class="bi bi-chat-text me-2"></i>Notes
                  </h6>
                </div>
                <div class="card-body">
                  <p class="text-muted mb-0">{{ $reservation->notes }}</p>
                </div>
              </div>
            </div>
          </div>
          @endif

          @if($reservation->acknowledgment_data || $reservation->signature_data)
          <div class="row mt-4">
            <div class="col-12">
              <div class="card" style="border-radius: 10px; border: 2px solid #28a745; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 10px 10px 0 0; border: none;">
                  <h6 class="mb-0" style="color: white; font-weight: 600;">
                    <i class="bi bi-handshake me-2"></i>Booking Acknowledgment & Signature
                  </h6>
                </div>
                <div class="card-body">
                  @if($reservation->acknowledgment_data)
                    <div class="mb-3">
                      <h6 class="text-success mb-3">
                        <i class="bi bi-check-circle me-2"></i>Customer Acknowledged:
                      </h6>
                      @if(isset($reservation->acknowledgment_data['driver_license_requirement']) && $reservation->acknowledgment_data['driver_license_requirement'])
                        <div class="d-flex align-items-start mb-2 p-2" style="background: #f8f9fa; border-radius: 6px; border-left: 4px solid #28a745;">
                          <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                          <small class="text-muted">Driver's License Requirement: At least one person must have Motorcycle or Car Driver's license</small>
                        </div>
                      @endif

                      @if(isset($reservation->acknowledgment_data['license_show_requirement']) && $reservation->acknowledgment_data['license_show_requirement'])
                        <div class="d-flex align-items-start mb-2 p-2" style="background: #f8f9fa; border-radius: 6px; border-left: 4px solid #28a745;">
                          <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                          <small class="text-muted">License Show Requirement: Must carry and show physical driver's license before ride starts</small>
                        </div>
                      @endif
                    </div>
                  @endif

                  @if($reservation->signature_data)
                    <div class="signature-display">
                      <h6 class="text-success mb-3">
                        <i class="bi bi-pen me-2"></i>Digital Signature:
                      </h6>
                      <div class="text-center p-3" style="background: #f8f9fa; border-radius: 8px; border: 2px solid #dee2e6;">
                        <img src="{{ $reservation->signature_data }}" alt="Customer Digital Signature" style="max-width: 100%; max-height: 120px; border-radius: 4px;">
                        <div class="mt-2">
                          <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            Signed on {{ $reservation->created_at->format('M d, Y \a\t g:i A') }}
                          </small>
                        </div>
                      </div>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
          @endif
        </div>
        <div class="modal-footer modal-footer-muted">
          @if($isMultiPackage)
            <div class="alert alert-info d-flex align-items-center me-auto mb-0" style="padding: 0.5rem 1rem;">
              <i class="bi bi-info-circle me-2"></i>
              <small>This is part of a multi-package booking. Use the "View All Packages" button to see all packages together.</small>
            </div>
          @endif
          <button type="button" class="btn btn-light btn-rounded" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>Close
          </button>
        </div>
      </div>
    </div>
  </div>
    @endforeach
  @endforeach

  <!-- Edit Modals -->
  @foreach($groupedReservations as $group)
    @php
      $primaryReservation = $group['primary_reservation'];
      $reservations = $group['reservations'];
      $isMultiPackage = $reservations->count() > 1;
    @endphp
    @foreach($reservations as $reservation)
  <div class="modal fade" id="editModal{{ $reservation->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $reservation->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0; border: none;">
          <h5 class="modal-title" id="editModalLabel{{ $reservation->id }}" style="font-weight: 600;">
            <i class="bi bi-pencil-square me-2"></i>Edit {{ $isMultiPackage ? 'Multi-Package Booking' : 'Reservation' }} - {{ $reservation->booking_code }}
            @if($isMultiPackage)
              <span class="badge bg-warning ms-2">{{ $reservations->count() }} packages</span>
            @endif
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('admin.reservations.update', $reservation) }}">
          @csrf
          @method('PUT')
          <div class="modal-body" style="padding: 2rem;">
            @if($isMultiPackage)
              <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>
                  <strong>Multi-Package Booking:</strong> This booking contains {{ $reservations->count() }} packages.
                  You are editing Package {{ $loop->iteration }} of {{ $reservations->count() }}.
                  Changes will only affect this specific package.
                </div>
              </div>
            @endif
            <div class="row g-4">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="customer_id{{ $reservation->id }}" class="form-label">Customer</label>
                  <select class="form-select" id="customer_id{{ $reservation->id }}" name="customer_id" required>
                    @foreach($customers ?? [] as $customer)
                      <option value="{{ $customer->id }}" {{ $reservation->customer_id == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} ({{ $customer->email }})
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label for="package_variant_id{{ $reservation->id }}" class="form-label">Package Variant</label>
                  <select class="form-select" id="package_variant_id{{ $reservation->id }}" name="package_variant_id" required>
                    @foreach($packageVariants ?? [] as $variant)
                      <option value="{{ $variant->id }}" {{ $reservation->package_variant_id == $variant->id ? 'selected' : '' }}>
                        {{ $variant->package->name }} - {{ $variant->variant_name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label for="schedule_slot_id{{ $reservation->id }}" class="form-label">Schedule Slot</label>
                  <select class="form-select" id="schedule_slot_id{{ $reservation->id }}" name="schedule_slot_id" required>
                    @foreach($scheduleSlots ?? [] as $slot)
                      <option value="{{ $slot->id }}" {{ $reservation->schedule_slot_id == $slot->id ? 'selected' : '' }}>
                        {{ $slot->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label for="date{{ $reservation->id }}" class="form-label">Date</label>
                  <input type="date" class="form-control" id="date{{ $reservation->id }}" name="date" value="{{ $reservation->date ? $reservation->date->format('Y-m-d') : '' }}" required>
                </div>

                <div class="mb-3">
                  <label for="party_size{{ $reservation->id }}" class="form-label">Party Size</label>
                  <input type="number" class="form-control" id="party_size{{ $reservation->id }}" name="party_size" value="{{ $reservation->party_size }}" min="1" required>
                </div>
              </div>

              <div class="col-md-6">
                <div class="mb-3">
                  <label for="subtotal{{ $reservation->id }}" class="form-label">Subtotal</label>
                  <input type="number" step="0.01" class="form-control" id="subtotal{{ $reservation->id }}" name="subtotal" value="{{ $reservation->subtotal }}" required>
                </div>

                <div class="mb-3">
                  <label for="discount_amount{{ $reservation->id }}" class="form-label">Discount Amount</label>
                  <input type="number" step="0.01" class="form-control" id="discount_amount{{ $reservation->id }}" name="discount_amount" value="{{ $reservation->discount_amount ?? 0 }}">
                </div>

                <div class="mb-3">
                  <label for="tax_amount{{ $reservation->id }}" class="form-label">Tax Amount</label>
                  <input type="number" step="0.01" class="form-control" id="tax_amount{{ $reservation->id }}" name="tax_amount" value="{{ $reservation->tax_amount ?? 0 }}">
                </div>

                <div class="mb-3">
                  <label for="total_amount{{ $reservation->id }}" class="form-label">Total Amount</label>
                  <input type="number" step="0.01" class="form-control" id="total_amount{{ $reservation->id }}" name="total_amount" value="{{ $reservation->total_amount }}" required>
                </div>

                <div class="mb-3">
                  <label for="deposit_amount{{ $reservation->id }}" class="form-label">Deposit Amount</label>
                  <input type="number" step="0.01" class="form-control" id="deposit_amount{{ $reservation->id }}" name="deposit_amount" value="{{ $reservation->deposit_amount ?? 0 }}">
                </div>

                <div class="mb-3">
                  <label for="balance_amount{{ $reservation->id }}" class="form-label">Balance Amount</label>
                  <input type="number" step="0.01" class="form-control" id="balance_amount{{ $reservation->id }}" name="balance_amount" value="{{ $reservation->balance_amount ?? 0 }}">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="booking_status{{ $reservation->id }}" class="form-label">Booking Status</label>
                  <select class="form-select" id="booking_status{{ $reservation->id }}" name="booking_status" required>
                    <option value="pending" {{ $reservation->booking_status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $reservation->booking_status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="cancelled" {{ $reservation->booking_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="completed" {{ $reservation->booking_status == 'completed' ? 'selected' : '' }}>Completed</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6">
                <div class="mb-3">
                  <label for="payment_status{{ $reservation->id }}" class="form-label">Payment Status</label>
                  <select class="form-select" id="payment_status{{ $reservation->id }}" name="payment_status" required>
                    <option value="pending" {{ $reservation->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ $reservation->payment_status == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ $reservation->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="refunded" {{ $reservation->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label for="notes{{ $reservation->id }}" class="form-label">Notes</label>
              <textarea class="form-control" id="notes{{ $reservation->id }}" name="notes" rows="3">{{ $reservation->notes }}</textarea>
            </div>
          </div>
          <div class="modal-footer" style="background: #f8f9fa; border-radius: 0 0 15px 15px; border: none; padding: 1.5rem;">
          <button type="button" class="btn btn-light btn-rounded" data-bs-dismiss="modal">
              <i class="bi bi-x-circle me-2"></i>Cancel
            </button>
          <button type="submit" class="btn btn-primary btn-gradient">
              <i class="bi bi-check-circle me-2"></i>Update Reservation
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
    @endforeach
  @endforeach

  <!-- Multi-Package View Modals -->
  @foreach($groupedReservations as $group)
    @php
      $primaryReservation = $group['primary_reservation'];
      $reservations = $group['reservations'];
      $isMultiPackage = $reservations->count() > 1;
    @endphp
    @if($isMultiPackage)
  <div class="modal fade" id="multiPackageModal{{ $primaryReservation->id }}" tabindex="-1" aria-labelledby="multiPackageModalLabel{{ $primaryReservation->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content modal-elevated">
        <div class="modal-header modal-header-gradient">
          <h5 class="modal-title" id="multiPackageModalLabel{{ $primaryReservation->id }}" style="font-weight: 600;">
            <i class="bi bi-list-ul me-2"></i>Multi-Package Booking - {{ $primaryReservation->booking_code }}
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body modal-body-spacious">
          <div class="row g-3 g-md-4">
            @foreach($reservations as $index => $reservation)
            <div class="col-12">
              <div class="card h-100 card-elevated">
                <div class="card-header card-header-muted">
                  <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-box me-2"></i>Package {{ $index + 1 }}: {{ $reservation->packageVariant->package->name ?? 'N/A' }}
                  </h6>
                </div>
                <div class="card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <div class="info-item">
                        <label class="info-label">Package Variant:</label>
                        <span class="info-value">{{ $reservation->packageVariant->variant_name ?? 'N/A' }}</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="info-item">
                        <label class="info-label">Date:</label>
                        <span class="info-value">{{ $reservation->date ? \Carbon\Carbon::parse($reservation->date)->format('M d, Y') : 'N/A' }}</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="info-item">
                        <label class="info-label">Time Slot:</label>
                        <span class="info-value">{{ $reservation->scheduleSlot->name ?? 'N/A' }}</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="info-item">
                        <label class="info-label">Report Time:</label>
                        <span class="info-value">{{ $reservation->report_time ? \Carbon\Carbon::parse($reservation->report_time)->format('g:i A') : 'N/A' }}</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="info-item">
                        <label class="info-label">Party Size:</label>
                        <span class="info-value">{{ $reservation->party_size }}</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="info-item">
                        <label class="info-label">Amount:</label>
                        <span class="info-value fw-bold text-success">৳{{ number_format($reservation->total_amount, 2) }}</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="info-item">
                        <label class="info-label">Booking Status:</label>
                        <span class="badge bg-{{ $reservation->booking_status === 'confirmed' ? 'success' : ($reservation->booking_status === 'pending' ? 'warning' : 'secondary') }} px-3 py-2">
                          {{ ucfirst($reservation->booking_status) }}
                        </span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="info-item">
                        <label class="info-label">Payment Status:</label>
                        <span class="badge bg-{{ $reservation->payment_status === 'paid' ? 'success' : ($reservation->payment_status === 'pending' ? 'warning' : 'secondary') }} px-3 py-2">
                          {{ ucfirst($reservation->payment_status) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        <div class="modal-footer modal-footer-muted">
          <button type="button" class="btn btn-light btn-rounded" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>Close
          </button>
        </div>
      </div>
    </div>
  </div>
    @endif
  @endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete form submissions
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const isMulti = this.dataset.isMulti === 'true';
            const count = parseInt(this.dataset.count);

            if (isMulti && count > 1) {
                if (!confirm('Are you sure you want to delete this multi-package booking? This will delete all ' + count + ' packages in this booking.')) {
                    e.preventDefault();
                }
            } else {
                if (!confirm('Are you sure you want to delete this reservation?')) {
                    e.preventDefault();
                }
            }
        });
    });
    // Initialize date fields when modals are shown
    const editModals = document.querySelectorAll('[id^="editModal"]');

    editModals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const dateInput = this.querySelector('input[type="date"]');
            if (dateInput && !dateInput.value) {
                // Set current date if no value is set
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                dateInput.value = `${year}-${month}-${day}`;
            }
        });
    });

    // Ensure all date inputs have proper format
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (input.value) {
            // Ensure the date is in YYYY-MM-DD format
            const date = new Date(input.value);
            if (!isNaN(date.getTime())) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                input.value = `${year}-${month}-${day}`;
            }
        }
    });
});
</script>
@endpush


