@extends('layouts.frontend')

@section('title', 'Profile - ATV/UTV Adventures')

@section('content')
<div class="container-fluid py-5" style="background: linear-gradient(135deg, #fff5f2 0%, #ffe8e0 100%); min-height: 100vh;">
  <div class="container">
    <!-- Header -->
    <div class="row mb-5">
      <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
          <div class="flex-grow-1">
            <h1 class="fw-bold jatio-color mb-2" style="font-size: 2.5rem;">Profile Settings</h1>
            <p class="text-muted mb-0 fs-5">Manage your account information</p>
          </div>
          <div class="d-flex gap-3 flex-shrink-0">
            <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-warning px-4 py-2">
              <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
            <form action="{{ route('customer.logout') }}" method="POST" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-outline-danger px-4 py-2">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
              </button>
            </form>
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

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>Please fix the following errors:
        <ul class="mb-0 mt-2">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="row">
      <!-- Profile Information -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
          <div class="card-header jatio-bg-color text-white" style="border-radius: 15px 15px 0 0; border: none; padding: 1.25rem;">
            <h5 class="mb-0 fw-bold">
              <i class="bi bi-person-circle me-2"></i>Profile Information
            </h5>
          </div>
          <div class="card-body p-4">
            <form action="{{ route('customer.profile.update') }}" method="POST">
              @csrf
              @method('PUT')
              
              <div class="row">
                <div class="col-md-6 mb-4">
                  <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                      <i class="bi bi-person text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0 @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $customer->name) }}" 
                           required>
                  </div>
                  @error('name')
                    <div class="invalid-feedback d-block">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
                
                <div class="col-md-6 mb-4">
                  <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                      <i class="bi bi-envelope text-muted"></i>
                    </span>
                    <input type="email" 
                           class="form-control border-start-0 @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $customer->email) }}" 
                           required>
                  </div>
                  @error('email')
                    <div class="invalid-feedback d-block">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-4">
                  <label for="phone" class="form-label fw-semibold">Phone Number</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                      <i class="bi bi-telephone text-muted"></i>
                    </span>
                    <input type="tel" 
                           class="form-control border-start-0 @error('phone') is-invalid @enderror" 
                           id="phone" 
                           name="phone" 
                           value="{{ old('phone', $customer->phone) }}">
                  </div>
                  @error('phone')
                    <div class="invalid-feedback d-block">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
                
                <div class="col-md-6 mb-4">
                  <label for="address" class="form-label fw-semibold">Address</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                      <i class="bi bi-geo-alt text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0 @error('address') is-invalid @enderror" 
                           id="address" 
                           name="address" 
                           value="{{ old('address', $customer->address) }}">
                  </div>
                  @error('address')
                    <div class="invalid-feedback d-block">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
              </div>

              <div class="d-flex gap-3 mt-4">
                <button type="submit" class="btn btn-orange jatio-bg-color px-4 py-2">
                  <i class="bi bi-check-circle me-2"></i>Update Profile
                </button>
                <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary px-4 py-2">
                  <i class="bi bi-x-circle me-2"></i>Cancel
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Statistics Sidebar -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
          <div class="card-header bg-info text-white" style="border-radius: 15px 15px 0 0; border: none; padding: 1.25rem;">
            <h5 class="mb-0 fw-bold">
              <i class="bi bi-graph-up me-2"></i>Account Statistics
            </h5>
          </div>
          <div class="card-body p-4">
            <div class="row text-center">
              <div class="col-4 mb-3">
                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle mb-2" style="width: 50px; height: 50px;">
                  <i class="bi bi-check-circle text-success"></i>
                </div>
                <h4 class="fw-bold text-success mb-1">{{ $customer->reservations()->where('booking_status', 'completed')->count() }}</h4>
                <small class="text-muted">Completed</small>
              </div>
              <div class="col-4 mb-3">
                <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 rounded-circle mb-2" style="width: 50px; height: 50px;">
                  <i class="bi bi-clock text-warning"></i>
                </div>
                <h4 class="fw-bold text-warning mb-1">{{ $customer->reservations()->where('booking_status', 'pending')->count() }}</h4>
                <small class="text-muted">Pending</small>
              </div>
              <div class="col-4 mb-3">
                <div class="d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 rounded-circle mb-2" style="width: 50px; height: 50px;">
                  <i class="bi bi-calendar-event text-info"></i>
                </div>
                <h4 class="fw-bold text-info mb-1">{{ $customer->reservations()->where('booking_status', 'confirmed')->count() }}</h4>
                <small class="text-muted">Confirmed</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Account Information -->
        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
          <div class="card-header bg-success text-white" style="border-radius: 15px 15px 0 0; border: none; padding: 1.25rem;">
            <h5 class="mb-0 fw-bold">
              <i class="bi bi-info-circle me-2"></i>Account Information
            </h5>
          </div>
          <div class="card-body p-4">
            <div class="mb-3">
              <label class="form-label fw-semibold text-muted small">Member Since</label>
              <p class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($customer->created_at)->format('M d, Y') }}</p>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold text-muted small">Total Reservations</label>
              <p class="mb-0 fw-bold jatio-color">{{ $customer->reservations()->count() }}</p>
            </div>
            <div class="mb-0">
              <label class="form-label fw-semibold text-muted small">Email Verified</label>
              <p class="mb-0">
                @if($customer->email_verified_at)
                  <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Verified</span>
                @else
                  <span class="badge bg-warning"><i class="bi bi-exclamation-triangle me-1"></i>Not Verified</span>
                @endif
              </p>
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

.btn-outline-danger {
  border-color: #dc3545;
  color: #dc3545;
}

.btn-outline-danger:hover {
  background-color: #dc3545;
  border-color: #dc3545;
  color: #fff;
}

.form-control {
  border-radius: 10px;
  border: 1px solid #dee2e6;
  transition: all 0.3s ease;
}

.form-control:focus {
  border-color: #FC692A;
  box-shadow: 0 0 0 0.2rem rgba(252, 105, 42, 0.25);
}

.input-group-text {
  border-radius: 10px 0 0 10px;
  border: 1px solid #dee2e6;
}

.input-group .form-control {
  border-radius: 0 10px 10px 0;
}

.badge {
  font-size: 0.75rem;
  padding: 0.5em 0.75em;
  font-weight: 500;
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
  
  .form-control {
    font-size: 0.9rem;
    padding: 0.75rem 0.75rem;
  }
  
  .input-group-text {
    font-size: 0.9rem;
    padding: 0.75rem 0.75rem;
  }
  
  .btn {
    font-size: 0.9rem;
  }
  
  .badge {
    font-size: 0.7rem;
    padding: 0.4em 0.6em;
  }
  
  /* Statistics sidebar mobile optimization */
  .col-lg-4 .card-body {
    padding: 1rem !important;
  }
  
  .col-lg-4 .d-inline-flex {
    width: 40px !important;
    height: 40px !important;
  }
  
  .col-lg-4 i {
    font-size: 1rem !important;
  }
  
  .col-lg-4 h4 {
    font-size: 1.2rem !important;
  }
  
  .col-lg-4 small {
    font-size: 0.7rem !important;
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
  
  .form-control {
    padding: 0.875rem 0.875rem;
  }
  
  .input-group-text {
    padding: 0.875rem 0.875rem;
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
  
  .form-control {
    font-size: 0.85rem;
    padding: 0.625rem 0.625rem;
  }
  
  .input-group-text {
    font-size: 0.85rem;
    padding: 0.625rem 0.625rem;
  }
  
  .btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
  }
  
  .badge {
    font-size: 0.65rem;
    padding: 0.3em 0.5em;
  }
  
  /* Statistics sidebar small mobile optimization */
  .col-lg-4 .card-body {
    padding: 0.75rem !important;
  }
  
  .col-lg-4 .d-inline-flex {
    width: 35px !important;
    height: 35px !important;
  }
  
  .col-lg-4 i {
    font-size: 0.9rem !important;
  }
  
  .col-lg-4 h4 {
    font-size: 1rem !important;
  }
  
  .col-lg-4 small {
    font-size: 0.65rem !important;
  }
  
  /* Form layout optimization for small screens */
  .row .col-md-6 {
    margin-bottom: 1rem;
  }
  
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
  
  .form-control {
    padding: 1rem 1rem;
  }
  
  .input-group-text {
    padding: 1rem 1rem;
  }
  
  /* Statistics sidebar large screen optimization */
  .col-lg-4 .d-inline-flex {
    width: 55px !important;
    height: 55px !important;
  }
  
  .col-lg-4 i {
    font-size: 1.3rem !important;
  }
  
  .col-lg-4 h4 {
    font-size: 1.5rem !important;
  }
  
  .col-lg-4 small {
    font-size: 0.85rem !important;
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
  
  .form-control {
    padding: 0.5rem 0.5rem;
  }
  
  .input-group-text {
    padding: 0.5rem 0.5rem;
  }
  
  .btn {
    padding: 0.5rem 0.75rem;
  }
}

/* Form-specific responsive improvements */
@media (max-width: 767px) {
  .input-group {
    flex-direction: column;
  }
  
  .input-group-text {
    border-radius: 10px 10px 0 0 !important;
    border-bottom: none;
  }
  
  .input-group .form-control {
    border-radius: 0 0 10px 10px !important;
    border-top: none;
  }
  
  .input-group-text,
  .input-group .form-control {
    width: 100%;
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
  
  .form-control {
    border: 1px solid #000 !important;
    background: white !important;
  }
}
</style>
@endpush
