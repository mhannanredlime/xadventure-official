@extends('layouts.frontend')

@section('title', 'Payment Options')

@push('styles')
  <link rel="stylesheet" href="{{ asset('frontEnd/css/payment.css') }}">
  <style>
    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
    .success-message {
      background: #d4edda;
      color: #155724;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
    .payment-form {
      background: white;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      border: 1px solid #ddd;
    }
  </style>
@endpush

@section('content')
  <div class="container mt-4" style="margin-top: 20% !important;">

    @if(session('success'))
      <div class="success-message">
        <i class="bi  bi-check-circle"></i>
        {{ session('success') }}
      </div>

      <!-- Order Summary Section -->
      @if(isset($payment) && $payment)
        <div class="row">
          <div class="col-lg-8">
            <div class="payment-form">
              <h2>Order Summary</h2>
              <div class="row">
                <div class="col-md-6">
                  <h5>Customer Information</h5>
                  <p><strong>Name:</strong> {{ $payment->reservation->customer->name }}</p>
                  <p><strong>Email:</strong> {{ $payment->reservation->customer->email }}</p>
                  <p><strong>Phone:</strong> {{ $payment->reservation->customer->phone }}</p>
                  @if($payment->reservation->customer->address)
                    <p><strong>Address:</strong> {{ $payment->reservation->customer->address }}</p>
                  @endif
                </div>
                <div class="col-md-6">
                  <h5>Booking Details</h5>
                  <p><strong>Booking Code:</strong> {{ $payment->reservation->booking_code }}</p>
                  <p><strong>Transaction ID:</strong> {{ $payment->transaction_id }}</p>
                  <p><strong>Date:</strong> {{ date('F j, Y', strtotime($payment->reservation->date)) }}</p>
                  <p><strong>Status:</strong>
                    <span class="badge {{ $payment->status === 'completed' ? 'bg-success' : 'bg-warning' }}">
                      {{ ucfirst($payment->status) }}
                    </span>
                  </p>
                </div>
              </div>

              <div class="mt-4">
                <h5>Package Details</h5>
                <div class="card">
                  <div class="card-body">
                    <h6>{{ $payment->reservation->packageVariant->package->name }}</h6>
                    <p class="text-muted">{{ $payment->reservation->packageVariant->variant_name }}</p>
                    <p><strong>Party Size:</strong> {{ $payment->reservation->party_size }}</p>
                    <p><strong>Total Amount:</strong> TK {{ number_format($payment->amount) }}</p>
                  </div>
                </div>
              </div>

              @if($payment->payment_method === 'check_payment')
                <div class="mt-4 alert alert-info">
                  <h5>Check Payment Instructions</h5>
                  <ul>
                    <li>Please prepare a check for the total amount</li>
                    <li>Make the check payable to: <strong>ATV/UTV Adventure Tours</strong></li>
                    <li>Include your booking reference in the memo field</li>
                    <li>Mail the check to: <strong>123 Adventure Street, City, State 12345</strong></li>
                    <li>Your booking will be confirmed once payment is received</li>
                  </ul>
                </div>
              @endif
            </div>
          </div>

          <div class="col-lg-4">
            <div class="payment-form">
              <h4>Quick Actions</h4>
              <a href="{{ route('frontend.packages.index') }}" class="btn btn-primary w-100 mb-2">
                <i class="bi  bi-plus"></i> Book Another Adventure
              </a>
              <a href="{{ route('frontend.process-to-checkout') }}" class="btn btn-outline-secondary w-100 mb-2">
                <i class="bi  bi-shopping-cart"></i> View Cart
              </a>
              <a href="{{ url('/') }}" class="btn btn-outline-primary w-100">
                <i class="bi  bi-home"></i> Back to Home
              </a>
            </div>
          </div>
        </div>
      @else
        <!-- Show simple success message for check payments -->
        <div class="payment-form">
          <div class="text-center">
            <i class="bi  bi-check-circle text-success" style="font-size: 4rem;"></i>
            <h2 class="mt-3">Booking Confirmed!</h2>
            <p class="lead">Your booking has been successfully processed.</p>
            <div class="mt-4">
              <a href="{{ route('frontend.packages.index') }}" class="btn btn-primary">
                <i class="bi  bi-plus"></i> Book Another Adventure
              </a>
              <a href="{{ url('/') }}" class="btn btn-outline-primary">
                <i class="bi  bi-home"></i> Back to Home
              </a>
            </div>
          </div>
        </div>
      @endif
    @elseif(session('error'))
      <div class="error-message">
        <i class="bi  bi-exclamation-triangle"></i>
        {{ session('error') }}
      </div>

      <div class="text-center mt-4">
        <a href="{{ route('frontend.process-to-checkout') }}" class="btn btn-primary">
          <i class="bi  bi-arrow-left"></i> Back to Cart
        </a>
      </div>
    @else
      <h1>Payment Options</h1>

      @if($errors->any())
        <div class="error-message">
          <i class="bi  bi-exclamation-triangle"></i>
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('frontend.checkout.process') }}" method="POST" id="payment-form">
        @csrf
        <div class="payment-form">
          <h2>Guest Information</h2>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="customer_name" class="form-label">Full Name *</label>
              <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                     id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
              @error('customer_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="customer_email" class="form-label">Email *</label>
              <input type="email" class="form-control @error('customer_email') is-invalid @enderror"
                     id="customer_email" name="customer_email" value="{{ old('customer_email') }}" required>
              @error('customer_email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="customer_phone" class="form-label">Phone Number *</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-telephone text-muted"></i>
                  <span class="ms-1 text-muted fw-bold">+880</span>
                </span>
                <input type="tel"
                       class="form-control border-start-0 @error('customer_phone') is-invalid @enderror"
                       id="customer_phone"
                       name="customer_phone"
                       value="{{ old('customer_phone') }}"
                       placeholder="1X XXX XXXX"
                       maxlength="15"
                       required>
              </div>
              <div class="form-text text-muted">
                Enter your mobile number (10-11 digits). +880 prefix is already included above.
              </div>
              @error('customer_phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="customer_address" class="form-label">Address</label>
              <textarea class="form-control @error('customer_address') is-invalid @enderror"
                        id="customer_address" name="customer_address" rows="2">{{ old('customer_address') }}</textarea>
              @error('customer_address')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="promo_code" class="form-label">Promo Code (Optional)</label>
              <input type="text" class="form-control" id="promo_code" name="promo_code" value="{{ old('promo_code') }}">
            </div>
          </div>
        </div>

        <div class="payment-form">
          <h2>Payment Method</h2>
          <div class="mb-3">
            <img src="{{ asset('frontEnd/images/payment.svg') }}" alt="Visa">
            <img src="{{ asset('frontEnd/images/payment.svg') }}" alt="Mastercard" class="ms-2">
            <img src="{{ asset('frontEnd/images/payment.svg') }}" alt="Amex" class="ms-2">
            <img src="{{ asset('frontEnd/images/payment.svg') }}" alt="Discover" class="ms-2">
            <img src="{{ asset('frontEnd/images/payment.svg') }}" alt="JCB" class="ms-2">
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="card_holder" class="form-label">Card Holder Name</label>
              <input type="text" class="form-control" id="card_holder" name="card_holder" required>
            </div>
            <div class="col-md-6">
              <label for="card_number" class="form-label">Card Number</label>
              <input type="text" class="form-control" id="card_number" name="card_number" required>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3">
              <label for="expiry" class="form-label">Expiry Date</label>
              <input type="text" class="form-control" id="expiry" name="expiry" placeholder="mm/yy" required>
            </div>
            <div class="col-md-3">
              <label for="cvc" class="form-label">CVV/CVC</label>
              <input type="text" class="form-control" id="cvc" name="cvc" required>
            </div>
          </div>
          <button type="submit" class="btn btn-warning btn-lg px-5">Process Payment</button>
        </div>
      </form>
    @endif
  </div>
@endsection

@push('scripts')
<script>
  // Auto-hide messages
  setTimeout(function() {
    $('.success-message, .error-message').fadeOut();
  }, 5000);

  // Form validation
  $('#payment-form').submit(function(e) {
    const requiredFields = ['customer_name', 'customer_email', 'customer_phone', 'card_holder', 'card_number', 'expiry', 'cvc'];
    let isValid = true;

    requiredFields.forEach(function(field) {
      const value = $('#' + field).val().trim();
      if (!value) {
        $('#' + field).addClass('is-invalid');
        isValid = false;
      } else {
        $('#' + field).removeClass('is-invalid');
      }
    });

    // Email validation
    const email = $('#customer_email').val();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email && !emailRegex.test(email)) {
      $('#customer_email').addClass('is-invalid');
      isValid = false;
    }

    // Phone validation (with +880 prefix support)
    const phone = $('#customer_phone').val().replace(/\s/g, '');
    let phoneValid = false;

    if (phone.startsWith('880')) {
      const phoneWithoutPrefix = phone.substring(3);
      phoneValid = /^\d{10,11}$/.test(phoneWithoutPrefix);
    } else {
      phoneValid = /^\d{10,11}$/.test(phone);
    }

    if (phone && !phoneValid) {
      $('#customer_phone').addClass('is-invalid');
      isValid = false;
    }

    if (!isValid) {
      e.preventDefault();
              toastNotifications.warning('Please fill in all required fields correctly.');
      return false;
    }
  });

  // Phone number formatting (simplified)
  $('#customer_phone').on('input', function() {
    let value = $(this).val().replace(/\D/g, '');

    // Limit to 11 digits
    if (value.length > 11) {
      value = value.substring(0, 11);
    }

    // Simple formatting - just add spaces for readability
    if (value.length >= 8) {
      value = value.substring(0, 2) + ' ' + value.substring(2, 5) + ' ' + value.substring(5, 8) + ' ' + value.substring(8);
    } else if (value.length >= 5) {
      value = value.substring(0, 2) + ' ' + value.substring(2, 5) + ' ' + value.substring(5);
    } else if (value.length >= 2) {
      value = value.substring(0, 2) + ' ' + value.substring(2);
    }

    $(this).val(value);
  });

  // Real-time validation
  $('input[required]').on('blur', function() {
    const value = $(this).val().trim();
    if (!value) {
      $(this).addClass('is-invalid');
    } else {
      $(this).removeClass('is-invalid');
    }
  });
</script>
@endpush


