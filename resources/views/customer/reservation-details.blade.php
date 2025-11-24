@extends('layouts.frontend')

@section('title', 'Reservation Details - ATV/UTV Adventures')

@section('content')
<div class="container-fluid py-5" style="background: linear-gradient(135deg, #fff5f2 0%, #ffe8e0 100%); min-height: 100vh;">
  <div class="container">
    <!-- Header -->
    <div class="row mb-5">
      <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
          <div class="flex-grow-1">
            <h1 class="fw-bold jatio-color mb-2" style="font-size: 2.5rem;">Reservation Details</h1>
            <p class="text-muted mb-0 fs-5">Booking #{{ $reservation->id }}</p>
          </div>
          <div class="d-flex gap-3 flex-shrink-0">
            <a href="{{ route('customer.reservations') }}" class="btn btn-outline-warning px-4 py-2">
              <i class="bi bi-arrow-left me-2"></i>Back to Reservations
            </a>
            <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary px-4 py-2">
              <i class="bi bi-house me-2"></i>Dashboard
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Reservation Details -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
          <div class="card-header jatio-bg-color text-white" style="border-radius: 15px 15px 0 0; border: none; padding: 1.25rem;">
            <h5 class="mb-0 fw-bold">
              <i class="bi bi-calendar-check me-2"></i>Reservation Information
            </h5>
          </div>
          <div class="card-body p-4">
            <div class="row">
              <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold text-muted">Booking ID</label>
                <p class="mb-0 fw-bold jatio-color fs-5">#{{ $reservation->id }}</p>
              </div>
              <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold text-muted">Status</label>
                <div>
                  @php
                    $statusColors = [
                      'pending' => 'warning',
                      'confirmed' => 'success',
                      'cancelled' => 'danger',
                      'completed' => 'info'
                    ];
                    $statusColor = $statusColors[$reservation->booking_status] ?? 'secondary';
                  @endphp
                  <span class="badge bg-{{ $statusColor }} fs-6 px-3 py-2">
                    @if($reservation->booking_status === 'pending')
                      <i class="bi bi-clock me-1"></i>
                    @elseif($reservation->booking_status === 'confirmed')
                      <i class="bi bi-check-circle me-1"></i>
                    @elseif($reservation->booking_status === 'cancelled')
                      <i class="bi bi-x-circle me-1"></i>
                    @elseif($reservation->booking_status === 'completed')
                      <i class="bi bi-flag-checkered me-1"></i>
                    @endif
                    {{ ucfirst($reservation->booking_status) }}
                  </span>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold text-muted">Package</label>
                <p class="mb-0 fw-bold fs-5">{{ $reservation->packageVariant->package->name ?? 'N/A' }}</p>
              </div>
              <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold text-muted">Variant</label>
                <p class="mb-0 fs-5">{{ $reservation->packageVariant->variant_name ?? 'N/A' }}</p>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold text-muted">Date</label>
                <p class="mb-0">
                  @if($reservation->date)
                    <span class="fw-bold fs-5">{{ \Carbon\Carbon::parse($reservation->date)->format('M d, Y') }}</span>
                    <br>
                    <small class="text-muted">{{ \Carbon\Carbon::parse($reservation->date)->format('l') }}</small>
                  @else
                    <span class="text-muted">Not set</span>
                  @endif
                </p>
              </div>
              <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold text-muted">Time Slot</label>
                <p class="mb-0 fw-bold fs-5">{{ $reservation->scheduleSlot->time_slot ?? 'N/A' }}</p>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold text-muted">Number of People</label>
                <p class="mb-0 fw-bold fs-5">{{ $reservation->number_of_people ?? 'N/A' }}</p>
              </div>
              <div class="col-md-6 mb-4">
                <label class="form-label fw-semibold text-muted">Total Amount</label>
                <p class="mb-0 fw-bold text-success fs-5">${{ number_format($reservation->total_amount, 2) }}</p>
              </div>
            </div>

            @if($reservation->notes)
              <div class="row">
                <div class="col-12 mb-4">
                  <label class="form-label fw-semibold text-muted">Notes</label>
                  <p class="mb-0">{{ $reservation->notes }}</p>
                </div>
              </div>
            @endif

            @if($reservation->acknowledgment_data || $reservation->signature_data)
              <div class="row">
                <div class="col-12 mb-4">
                  <label class="form-label fw-semibold text-muted">Booking Acknowledgment</label>
                  <div class="alert alert-info">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>You have agreed to the following terms:</strong>
                  </div>
                  
                  @if($reservation->acknowledgment_data)
                    <div class="acknowledgment-details mb-3">
                      @if(isset($reservation->acknowledgment_data['driver_license_requirement']) && $reservation->acknowledgment_data['driver_license_requirement'])
                        <div class="d-flex align-items-start mb-2">
                          <i class="fas fa-check text-success me-2 mt-1"></i>
                          <small class="text-muted">I understand that for Each vehicle reservation, we will have at least one person have Motorcycle or Car Driver's licenses</small>
                        </div>
                      @endif
                      
                      @if(isset($reservation->acknowledgment_data['license_show_requirement']) && $reservation->acknowledgment_data['license_show_requirement'])
                        <div class="d-flex align-items-start mb-2">
                          <i class="fas fa-check text-success me-2 mt-1"></i>
                          <small class="text-muted">I understand I have to carry show my driver's license before the ride start. Failure the show my physical license, Xadventure will Deny the Ride, and I will not get my money back.</small>
                        </div>
                      @endif
                    </div>
                  @endif

                  @if($reservation->signature_data)
                    <div class="signature-display">
                      <label class="form-label fw-semibold text-muted">Digital Signature</label>
                      <div class="signature-container" style="border: 2px solid #dee2e6; border-radius: 8px; padding: 15px; background-color: #f8f9fa; text-align: center;">
                        <img src="{{ $reservation->signature_data }}" alt="Digital Signature" style="max-width: 100%; max-height: 150px; border-radius: 4px;">
                        <div class="mt-2">
                          <small class="text-muted">
                            <i class="fas fa-signature me-1"></i>
                            Digital signature provided on {{ $reservation->created_at->format('M d, Y \a\t g:i A') }}
                          </small>
                        </div>
                      </div>
                    </div>
                  @endif
                </div>
              </div>
            @endif
          </div>
        </div>

        <!-- Payment Information -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
          <div class="card-header bg-info text-white" style="border-radius: 15px 15px 0 0; border: none; padding: 1.25rem;">
            <h5 class="mb-0 fw-bold">
              <i class="bi bi-credit-card me-2"></i>Payment Information
            </h5>
          </div>
          <div class="card-body p-4">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold text-muted">Payment Status</label>
                <p class="mb-0">
                  @php
                    $paymentColors = [
                      'pending' => 'warning',
                      'partial' => 'info',
                      'paid' => 'success',
                      'refunded' => 'danger'
                    ];
                    $paymentColor = $paymentColors[$reservation->payment_status] ?? 'secondary';
                  @endphp
                  <span class="badge bg-{{ $paymentColor }} fs-6">
                    <i class="bi bi-credit-card me-1"></i>
                    {{ ucfirst($reservation->payment_status) }}
                  </span>
                </p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold text-muted">Payment Method</label>
                <p class="mb-0">{{ $reservation->payment_method ?? 'Not specified' }}</p>
              </div>
            </div>
            @if($reservation->payment_id)
              <div class="row">
                <div class="col-12">
                  <label class="form-label fw-semibold text-muted">Payment ID</label>
                  <p class="mb-0">{{ $reservation->payment_id }}</p>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
          <div class="card-header bg-success text-white" style="border-radius: 15px 15px 0 0; border: none; padding: 1.25rem;">
            <h5 class="mb-0 fw-bold">
              <i class="bi bi-lightning me-2"></i>Quick Actions
            </h5>
          </div>
          <div class="card-body p-4">
            <div class="d-grid gap-3">
              <a href="{{ route('customer.reservations') }}" class="btn btn-outline-warning py-3">
                <i class="bi bi-calendar-check me-2"></i>All Reservations
              </a>
              <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary py-3">
                <i class="bi bi-house me-2"></i>Dashboard
              </a>
              <button type="button" class="btn btn-orange jatio-bg-color py-3" onclick="openPackageSelection()">
                <i class="bi bi-plus-circle me-2"></i>Book New Adventure
              </button>
            </div>
          </div>
        </div>

        <!-- Reservation Timeline -->
        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
          <div class="card-header bg-warning text-dark" style="border-radius: 15px 15px 0 0; border: none; padding: 1.25rem;">
            <h5 class="mb-0 fw-bold">
              <i class="bi bi-clock-history me-2"></i>Reservation Timeline
            </h5>
          </div>
          <div class="card-body p-4">
            <div class="timeline">
              <div class="timeline-item">
                <div class="timeline-marker bg-success"></div>
                <div class="timeline-content">
                  <h6 class="fw-semibold mb-1">Reservation Created</h6>
                  <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($reservation->created_at)->format('M d, Y g:i A') }}</p>
                </div>
              </div>
              
              @if($reservation->booking_status === 'confirmed')
                <div class="timeline-item">
                  <div class="timeline-marker bg-success"></div>
                  <div class="timeline-content">
                    <h6 class="fw-semibold mb-1">Reservation Confirmed</h6>
                    <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($reservation->updated_at)->format('M d, Y g:i A') }}</p>
                  </div>
                </div>
              @endif
              
              @if($reservation->booking_status === 'completed')
                <div class="timeline-item">
                  <div class="timeline-marker bg-success"></div>
                  <div class="timeline-content">
                    <h6 class="fw-semibold mb-1">Adventure Completed</h6>
                    <p class="text-muted small mb-0">Your adventure has been completed successfully!</p>
                  </div>
                </div>
              @endif
              
              @if($reservation->booking_status === 'cancelled')
                <div class="timeline-item">
                  <div class="timeline-marker bg-danger"></div>
                  <div class="timeline-content">
                    <h6 class="fw-semibold mb-1">Reservation Cancelled</h6>
                    <p class="text-muted small mb-0">This reservation has been cancelled</p>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
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

.btn-outline-secondary {
  border-color: #6c757d;
  color: #6c757d;
}

.btn-outline-secondary:hover {
  background-color: #6c757d;
  border-color: #6c757d;
  color: #fff;
}

.badge {
  font-size: 0.75rem;
  padding: 0.5em 0.75em;
  font-weight: 500;
}

.timeline {
  position: relative;
  padding-left: 2rem;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 0.5rem;
  top: 0;
  bottom: 0;
  width: 2px;
  background-color: #dee2e6;
}

.timeline-item {
  position: relative;
  margin-bottom: 1.5rem;
}

.timeline-item:last-child {
  margin-bottom: 0;
}

.timeline-marker {
  position: absolute;
  left: -1.5rem;
  top: 0.25rem;
  width: 1rem;
  height: 1rem;
  border-radius: 50%;
  border: 3px solid #fff;
  box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content h6 {
  margin-bottom: 0.25rem;
  color: #495057;
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
  
  /* Enhanced mobile optimizations */
  .card-body {
    padding: 1.5rem !important;
  }
  
  .form-label {
    font-size: 0.9rem;
  }
  
  .badge {
    font-size: 0.7rem;
    padding: 0.4em 0.6em;
  }
  
  /* Timeline mobile optimization */
  .timeline {
    padding-left: 1.5rem;
  }
  
  .timeline::before {
    left: 0.25rem;
  }
  
  .timeline-marker {
    left: -1.25rem;
    width: 0.75rem;
    height: 0.75rem;
  }
  
  .timeline-content h6 {
    font-size: 0.9rem;
  }
  
  .timeline-content p {
    font-size: 0.8rem;
  }
  
  /* Information layout mobile optimization */
  .col-md-6 {
    margin-bottom: 1rem;
  }
  
  .form-label {
    margin-bottom: 0.25rem;
  }
  
  .fw-bold.fs-5 {
    font-size: 1rem !important;
  }
  
  /* Sidebar mobile optimization */
  .col-lg-4 .card-body {
    padding: 1rem !important;
  }
  
  .d-grid.gap-3 .btn {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
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
  
  .timeline {
    padding-left: 1.75rem;
  }
  
  .timeline-marker {
    left: -1.375rem;
    width: 0.875rem;
    height: 0.875rem;
  }
  
  .fw-bold.fs-5 {
    font-size: 1.1rem !important;
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
  
  .form-label {
    font-size: 0.85rem;
  }
  
  .btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
  }
  
  .badge {
    font-size: 0.65rem;
    padding: 0.3em 0.5em;
  }
  
  /* Timeline small mobile optimization */
  .timeline {
    padding-left: 1.25rem;
  }
  
  .timeline::before {
    left: 0.125rem;
    width: 1px;
  }
  
  .timeline-marker {
    left: -1rem;
    width: 0.625rem;
    height: 0.625rem;
  }
  
  .timeline-content h6 {
    font-size: 0.8rem;
  }
  
  .timeline-content p {
    font-size: 0.7rem;
  }
  
  /* Information layout small mobile optimization */
  .col-md-6 {
    margin-bottom: 0.75rem;
  }
  
  .fw-bold.fs-5 {
    font-size: 0.9rem !important;
  }
  
  /* Sidebar small mobile optimization */
  .col-lg-4 .card-body {
    padding: 0.75rem !important;
  }
  
  .d-grid.gap-3 .btn {
    padding: 0.625rem 0.875rem;
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
  
  .timeline {
    padding-left: 2.5rem;
  }
  
  .timeline-marker {
    left: -1.75rem;
    width: 1.25rem;
    height: 1.25rem;
  }
  
  .timeline-content h6 {
    font-size: 1rem;
  }
  
  .timeline-content p {
    font-size: 0.9rem;
  }
  
  .fw-bold.fs-5 {
    font-size: 1.2rem !important;
  }
  
  .d-grid.gap-3 .btn {
    padding: 1rem 1.25rem;
    font-size: 1rem;
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
  
  .timeline {
    padding-left: 1.25rem;
  }
  
  .timeline-marker {
    left: -1rem;
    width: 0.625rem;
    height: 0.625rem;
  }
  
  .timeline-content h6 {
    font-size: 0.8rem;
  }
  
  .timeline-content p {
    font-size: 0.7rem;
  }
  
  .btn {
    padding: 0.5rem 0.75rem;
  }
  
  .d-grid.gap-3 .btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
  }
}

/* Information layout responsive improvements */
@media (max-width: 767px) {
  /* Stack information fields on mobile */
  .row .col-md-6 {
    width: 100%;
    margin-bottom: 1rem;
  }
  
  /* Optimize label and value spacing */
  .form-label {
    margin-bottom: 0.25rem;
    font-weight: 600;
  }
  
  .fw-bold.fs-5 {
    margin-bottom: 0.5rem;
  }
  
  /* Improve badge display */
  .badge {
    display: inline-block;
    margin-top: 0.25rem;
  }
  
  /* Optimize timeline for mobile */
  .timeline-item {
    margin-bottom: 1rem;
  }
  
  .timeline-item:last-child {
    margin-bottom: 0;
  }
}

/* Print styles */
@media print {
  .btn,
  .form,
  .alert {
    display: none !important;
  }
  
  .card {
    border: 1px solid #000 !important;
    box-shadow: none !important;
  }
  
  .container-fluid {
    background: white !important;
  }
  
  .timeline::before {
    background-color: #000 !important;
  }
  
  .timeline-marker {
    border: 2px solid #000 !important;
    background-color: #fff !important;
  }
  
  .fw-bold.fs-5 {
    color: #000 !important;
  }
}
</style>
@endpush
