@extends('layouts.frontend')

@section('title', 'My Reservations - ATV/UTV Adventures')

@section('content')
<div class="container-fluid py-5" style="background: linear-gradient(135deg, #fff5f2 0%, #ffe8e0 100%); min-height: 100vh;">
  <div class="container">
    <!-- Header -->
    <div class="row mb-5">
      <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
          <div class="flex-grow-1">
            <h1 class="fw-bold jatio-color mb-2" style="font-size: 2.5rem;">My Reservations</h1>
            <p class="text-muted mb-0 fs-5">View and manage all your bookings</p>
          </div>
          <div class="d-flex gap-3 flex-shrink-0">
            <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-warning px-4 py-2">
              <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
            <button type="button" class="btn btn-orange jatio-bg-color px-4 py-2" onclick="openPackageSelection()">
              <i class="bi bi-plus-circle me-2"></i>Book New Adventure
            </button>
          </div>
        </div>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <!-- Reservations List -->
    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
      <div class="card-header jatio-bg-color text-white" style="border-radius: 15px 15px 0 0; border: none; padding: 1.25rem;">
        <h5 class="mb-0 fw-bold">
          <i class="bi bi-calendar-check me-2"></i>All Reservations ({{ $groupedReservations->count() }})
        </h5>
      </div>
      <div class="card-body p-0">
        @if($groupedReservations->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th class="border-0 px-4 py-3">Booking ID</th>
                  <th class="border-0 px-4 py-3">Package</th>
                  <th class="border-0 px-4 py-3">Date</th>
                  <th class="border-0 px-4 py-3">Time</th>
                  <th class="border-0 px-4 py-3">Status</th>
                  <th class="border-0 px-4 py-3">Amount</th>
                  <th class="border-0 px-4 py-3">Created</th>
                  <th class="border-0 px-4 py-3">Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($groupedReservations as $group)
                  @php
                    $reservation = $group['primary_reservation'];
                    $isMultiPackage = $group['reservations']->count() > 1;
                  @endphp
                  <tr>
                    <td class="px-4 py-3">
                      <span class="fw-semibold jatio-color">#{{ $reservation->id }}</span>
                      @if($isMultiPackage)
                        <br><small class="text-muted">+{{ $group['reservations']->count() - 1 }} more</small>
                      @endif
                    </td>
                    <td class="px-4 py-3">
                      <div>
                        <strong>{{ $reservation->packageVariant->package->name ?? 'N/A' }}</strong>
                        <br>
                        <small class="text-muted">{{ $reservation->packageVariant->variant_name ?? 'N/A' }}</small>
                        @if($isMultiPackage)
                          <br><small class="text-info">Multi-package booking</small>
                        @endif
                      </div>
                    </td>
                    <td class="px-4 py-3">
                      @if($reservation->date)
                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($reservation->date)->format('M d, Y') }}</span>
                        <br>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($reservation->date)->format('l') }}</small>
                      @else
                        <span class="text-muted">Not set</span>
                      @endif
                    </td>
                    <td class="px-4 py-3">
                      <span class="fw-semibold">{{ $reservation->scheduleSlot->time_slot ?? 'N/A' }}</span>
                    </td>
                    <td class="px-4 py-3">
                      @php
                        $statusColors = [
                          'pending' => 'warning',
                          'confirmed' => 'success',
                          'cancelled' => 'danger',
                          'completed' => 'info'
                        ];
                        $statusColor = $statusColors[$group['booking_status']] ?? 'secondary';
                      @endphp
                      <span class="badge bg-{{ $statusColor }} px-3 py-2">
                        @if($group['booking_status'] === 'pending')
                          <i class="bi bi-clock me-1"></i>
                        @elseif($group['booking_status'] === 'confirmed')
                          <i class="bi bi-check-circle me-1"></i>
                        @elseif($group['booking_status'] === 'cancelled')
                          <i class="bi bi-x-circle me-1"></i>
                        @elseif($group['booking_status'] === 'completed')
                          <i class="bi bi-flag-checkered me-1"></i>
                        @endif
                        {{ ucfirst($group['booking_status']) }}
                      </span>
                    </td>
                    <td class="px-4 py-3">
                      <span class="fw-semibold">${{ number_format($group['total_amount'], 2) }}</span>
                    </td>
                    <td class="px-4 py-3">
                      <div>
                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($group['created_at'])->format('M d, Y') }}</span>
                        <br>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($group['created_at'])->format('g:i A') }}</small>
                      </div>
                    </td>
                    <td class="px-4 py-3">
                      <a href="{{ route('customer.reservations.details', $reservation->id) }}" class="btn btn-sm btn-outline-warning px-3">
                        <i class="bi bi-eye me-1"></i>View
                      </a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-5">
            <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
            <h5 class="text-muted mt-3 mb-2">No reservations found</h5>
            <p class="text-muted mb-4">Start your adventure by booking your first trip!</p>
            <button type="button" class="btn btn-orange jatio-bg-color px-4 py-2" onclick="openPackageSelection()">
              <i class="bi bi-plus-circle me-2"></i>Book Now
            </button>
          </div>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection

@push('styles')
<style>
.card {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}

.btn {
  border-radius: 10px;
  transition: all 0.3s ease;
  font-weight: 500;
}

.btn:hover {
  transform: translateY(-1px);
}

.btn-outline-warning {
  border-color: #ffc107;
  color: #ffc107;
}

.btn-outline-warning:hover {
  background-color: #ffc107;
  border-color: #ffc107;
  color: #000;
}

.table th {
  border-top: none;
  font-weight: 600;
  color: #495057;
  font-size: 0.9rem;
}

.table td {
  vertical-align: middle;
}

.badge {
  font-size: 0.75rem;
  padding: 0.5em 0.75em;
  font-weight: 500;
}

.pagination {
  gap: 0.25rem;
}

.page-link {
  border-radius: 8px;
  border: 1px solid #dee2e6;
  color: #495057;
  transition: all 0.3s ease;
}

.page-link:hover {
  background-color: #FC692A;
  border-color: #FC692A;
  color: white;
}

.page-item.active .page-link {
  background-color: #FC692A;
  border-color: #FC692A;
}

@media (max-width: 768px) {
  .container-fluid {
    padding-top: 2rem !important;
    padding-bottom: 2rem !important;
  }
  
  h1 {
    font-size: 2rem !important;
  }
  
  .btn {
    padding: 0.75rem 1rem;
  }
  
  .table-responsive {
    font-size: 0.875rem;
  }
  
  /* Enhanced mobile optimizations */
  .card-body {
    padding: 1.5rem !important;
  }
  
  .table th,
  .table td {
    padding: 0.75rem 0.5rem !important;
  }
  
  .badge {
    font-size: 0.7rem;
    padding: 0.4em 0.6em;
  }
  
  /* Mobile table optimization */
  .table-responsive {
    border-radius: 0;
  }
  
  .table th,
  .table td {
    white-space: nowrap;
  }
  
  /* Hide less important columns on mobile */
  .table th:nth-child(6),
  .table td:nth-child(6) {
    display: none;
  }
  
  .table th:nth-child(7),
  .table td:nth-child(7) {
    display: none;
  }
  
  /* Optimize action buttons */
  .btn-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
  }
  
  /* Pagination mobile optimization */
  .pagination {
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.25rem;
  }
  
  .page-link {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
  }
}

/* Tablet optimizations */
@media (max-width: 991px) and (min-width: 769px) {
  .container-fluid {
    padding-top: 3rem !important;
    padding-bottom: 3rem !important;
  }
  
  h1 {
    font-size: 2.2rem !important;
  }
  
  .card-body {
    padding: 1.25rem !important;
  }
  
  .table th,
  .table td {
    padding: 0.875rem 0.625rem !important;
  }
  
  /* Show more columns on tablet */
  .table th:nth-child(6),
  .table td:nth-child(6) {
    display: table-cell;
  }
  
  .table th:nth-child(7),
  .table td:nth-child(7) {
    display: none;
  }
}

/* Small mobile optimizations */
@media (max-width: 575px) {
  .container-fluid {
    padding-top: 1.5rem !important;
    padding-bottom: 1.5rem !important;
  }
  
  h1 {
    font-size: 1.8rem !important;
  }
  
  .card-body {
    padding: 1rem !important;
  }
  
  .table-responsive {
    font-size: 0.8rem;
  }
  
  .table th,
  .table td {
    padding: 0.5rem 0.25rem !important;
  }
  
  .btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
  }
  
  .btn-sm {
    padding: 0.375rem 0.5rem;
    font-size: 0.75rem;
  }
  
  .badge {
    font-size: 0.65rem;
    padding: 0.3em 0.5em;
  }
  
  /* Further hide columns on small mobile */
  .table th:nth-child(3),
  .table td:nth-child(3) {
    display: none;
  }
  
  .table th:nth-child(4),
  .table td:nth-child(4) {
    display: none;
  }
  
  .table th:nth-child(5),
  .table td:nth-child(5) {
    display: none;
  }
  
  /* Optimize pagination for small screens */
  .pagination {
    gap: 0.125rem;
  }
  
  .page-link {
    padding: 0.375rem 0.5rem;
    font-size: 0.8rem;
  }
  
  /* Stack header buttons on small mobile */
  .d-flex.gap-3 {
    flex-direction: column;
    gap: 0.75rem !important;
  }
  
  .d-flex.gap-3 .btn {
    width: 100%;
  }
}

/* Large screen optimizations */
@media (min-width: 1200px) {
  .container-fluid {
    padding-top: 4rem !important;
    padding-bottom: 4rem !important;
  }
  
  h1 {
    font-size: 2.8rem !important;
  }
  
  .card-body {
    padding: 2rem !important;
  }
  
  .table th,
  .table td {
    padding: 1rem 0.75rem !important;
  }
  
  /* Show all columns on large screens */
  .table th,
  .table td {
    display: table-cell !important;
  }
  
  .btn-sm {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
  }
}

/* Landscape mobile optimizations */
@media (max-width: 768px) and (orientation: landscape) {
  .container-fluid {
    padding-top: 1rem !important;
    padding-bottom: 1rem !important;
  }
  
  h1 {
    font-size: 1.5rem !important;
  }
  
  .card-body {
    padding: 1rem !important;
  }
  
  .table-responsive {
    font-size: 0.75rem;
  }
  
  .table th,
  .table td {
    padding: 0.5rem 0.25rem !important;
  }
  
  .btn {
    padding: 0.5rem 0.75rem;
  }
  
  .btn-sm {
    padding: 0.375rem 0.5rem;
    font-size: 0.7rem;
  }
}

/* Table-specific responsive improvements */
@media (max-width: 767px) {
  /* Make table more mobile-friendly */
  .table-responsive {
    border: none;
  }
  
  .table {
    margin-bottom: 0;
  }
  
  .table th {
    background-color: #f8f9fa;
    font-weight: 600;
    font-size: 0.8rem;
  }
  
  .table td {
    border-top: 1px solid #dee2e6;
  }
  
  /* Improve text wrapping */
  .table td {
    word-wrap: break-word;
    max-width: 120px;
  }
  
  /* Optimize package name display */
  .table td strong {
    font-size: 0.85rem;
  }
  
  .table td small {
    font-size: 0.75rem;
  }
}

/* Print styles */
@media print {
  .btn,
  .form,
  .alert,
  .pagination {
    display: none !important;
  }
  
  .card {
    border: 1px solid #000 !important;
    box-shadow: none !important;
  }
  
  .container-fluid {
    background: white !important;
  }
  
  .table {
    border: 1px solid #000 !important;
  }
  
  .table th,
  .table td {
    border: 1px solid #000 !important;
    padding: 0.5rem !important;
  }
}
</style>
@endpush
