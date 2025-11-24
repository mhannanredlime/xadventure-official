@extends('layouts.frontend')

@section('title', $package->name . ' - ATV/UTV Adventure')

@push('styles')
  <link rel="stylesheet" href="{{ asset('frontEnd/css/packege.css') }}">
  <style>
    .package-detail-card {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
    }
    .package-image {
      width: 100%;
      height: 300px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 20px;
    }
    .variant-card {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .variant-card:hover {
      border-color: #ff6b35;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .variant-card.selected {
      border-color: #ff6b35;
      background-color: #fff5f2;
    }
    .price-display {
      font-size: 1.2em;
      font-weight: bold;
      color: #ff6b35;
    }
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
    .loading {
      text-align: center;
      padding: 20px;
    }
    .date-picker {
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 8px;
      width: 100%;
    }
    .slot-option {
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 10px;
      margin: 5px 0;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .slot-option:hover {
      border-color: #ff6b35;
    }
    .slot-option.selected {
      border-color: #ff6b35;
      background-color: #fff5f2;
    }
    .slot-option.disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  </style>
@endpush

@section('content')
  <div class="container main-container">
    @if(session('error'))
      <div class="error-message">
        <i class="fas fa-exclamation-triangle"></i>
        {{ session('error') }}
      </div>
    @endif

    @if(session('success'))
      <div class="success-message">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
      </div>
    @endif

    @if($errors->any())
      <div class="error-message">
        <i class="fas fa-exclamation-triangle"></i>
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="row">
      <div class="col-lg-8">
        <div class="package-detail-card">
          @if($package->primaryImageUrl)
            <img src="{{ $package->primaryImageUrl }}" alt="{{ $package->name }}" class="package-image">
          @elseif($package->image)
            <img src="{{ asset('storage/' . $package->image) }}" alt="{{ $package->name }}" class="package-image">
          @else
            <div class="package-image bg-light d-flex align-items-center justify-content-center">
              <i class="fas fa-mountain fa-5x text-muted"></i>
            </div>
          @endif
          
          <h1 class="mb-3">{{ $package->name }}</h1>
          <p class="text-muted mb-4">{{ $package->description }}</p>
          
          <div class="mb-4">
            <h4>Available for:</h4>
            @foreach($package->vehicleTypes as $vehicleType)
              <span class="badge bg-primary me-2">{{ $vehicleType->name }}</span>
            @endforeach
          </div>

          <div class="mb-4">
            <h4>Package Variants</h4>
            @if($package->variants->isEmpty())
              <p class="text-muted">No variants available for this package.</p>
            @else
              @foreach($package->variants as $variant)
                <div class="variant-card" data-variant-id="{{ $variant->id }}">
                  <div class="row align-items-center">
                    <div class="col-md-8">
                      <h5 class="mb-1">{{ $variant->variant_name }}</h5>
                      <p class="text-muted mb-2">{{ $variant->description }}</p>
                      <div class="price-display">
                        @if($variant->prices->isNotEmpty())
                          @php
                            $weekdayPrice = $variant->prices->where('price_type', 'weekday')->first();
                            $weekendPrice = $variant->prices->where('price_type', 'weekend')->first();
                          @endphp
                          @if($weekdayPrice && $weekendPrice)
                            <span class="me-3">Weekday: TK {{ number_format($weekdayPrice->amount) }}</span>
                            <span>Weekend: TK {{ number_format($weekendPrice->amount) }}</span>
                          @elseif($weekdayPrice)
                            <span>TK {{ number_format($weekdayPrice->amount) }}</span>
                          @elseif($weekendPrice)
                            <span>TK {{ number_format($weekendPrice->amount) }}</span>
                          @endif
                        @else
                          <span class="text-muted">Price not available</span>
                        @endif
                      </div>
                    </div>
                    <div class="col-md-4 text-end">
                      <button class="btn btn-orange jatio-bg-color select-variant-btn" data-variant-id="{{ $variant->id }}">
                        Select
                      </button>
                    </div>
                  </div>
                </div>
              @endforeach
            @endif
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="package-detail-card">
          <h4>Book This Package</h4>
          
          <form id="booking-form" action="{{ route('frontend.cart.add') }}" method="POST">
            @csrf
            <input type="hidden" name="variant_id" id="selected-variant-id" required>
            
            <div class="mb-3">
              <label for="booking-date" class="form-label">Select Date</label>
              <input type="date" class="form-control date-picker" id="booking-date" name="date" 
                     min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Select Time Slot</label>
              <div id="time-slots">
                <div class="loading">
                  <i class="fas fa-spinner fa-spin"></i> Loading available slots...
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label for="quantity" class="form-label">Number of People</label>
              <input type="number" class="form-control" id="quantity" name="quantity" 
                     min="1" max="10" value="1" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Total Price</label>
              <div class="price-display" id="total-price">TK 0</div>
            </div>

            <button type="submit" class="btn btn-orange jatio-bg-color w-100" id="add-to-cart-btn" disabled>
              Add to Cart
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  let selectedVariantId = null;
  let selectedSlotId = null;
  let selectedDate = null;
  let quantity = 1;

  // Auto-hide messages
  setTimeout(function() {
    $('.success-message, .error-message').fadeOut();
  }, 5000);

  // Select variant
  $('.select-variant-btn').click(function() {
    const variantId = $(this).data('variant-id');
    selectedVariantId = variantId;
    
    $('.variant-card').removeClass('selected');
    $(this).closest('.variant-card').addClass('selected');
    
    $('#selected-variant-id').val(variantId);
    
    // Load time slots if date is selected
    if (selectedDate) {
      loadTimeSlots();
    }
    
    updateTotalPrice();
    updateAddToCartButton();
  });

  // Date selection
  $('#booking-date').change(function() {
    selectedDate = $(this).val();
    if (selectedVariantId) {
      loadTimeSlots();
    }
  });

  // Quantity change
  $('#quantity').change(function() {
    quantity = parseInt($(this).val());
    updateTotalPrice();
  });

  // Load time slots
  function loadTimeSlots() {
    if (!selectedVariantId || !selectedDate) return;

    $('#time-slots').html('<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

    $.get('{{ route("frontend.packages.availability") }}', {
      variant_id: selectedVariantId,
      date: selectedDate
    })
    .done(function(data) {
      let slotsHtml = '';
      if (data && data.length > 0) {
        data.forEach(function(slot) {
          const isAvailable = slot.available_capacity > 0;
          const slotClass = isAvailable ? 'slot-option' : 'slot-option disabled';
          const clickHandler = isAvailable ? `onclick="selectSlot(${slot.schedule_slot_id})"` : '';
          
          slotsHtml += `
            <div class="${slotClass}" ${clickHandler}>
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <strong>${slot.slot_name}</strong><br>
                  <small class="text-muted">Report: ${slot.report_time}</small>
                </div>
                <div class="text-end">
                  <div class="price-display">TK ${slot.price}</div>
                  <small class="text-muted">${slot.available_capacity} spots left</small>
                </div>
              </div>
            </div>
          `;
        });
      } else {
        slotsHtml = '<p class="text-muted">No available slots for this date.</p>';
      }
      $('#time-slots').html(slotsHtml);
    })
    .fail(function() {
      $('#time-slots').html('<p class="text-danger">Error loading time slots. Please try again.</p>');
    });
  }

  // Select time slot
  function selectSlot(slotId) {
    selectedSlotId = slotId;
    $('.slot-option').removeClass('selected');
    $(`[onclick="selectSlot(${slotId})"]`).addClass('selected');
    
    // Add hidden input for slot_id
    if ($('#selected-slot-id').length === 0) {
      $('#booking-form').append('<input type="hidden" name="slot_id" id="selected-slot-id">');
    }
    $('#selected-slot-id').val(slotId);
    
    updateTotalPrice();
    updateAddToCartButton();
  }

  // Update total price
  function updateTotalPrice() {
    if (!selectedVariantId || !selectedSlotId) {
      $('#total-price').text('TK 0');
      return;
    }

    // Get price from selected slot
    const selectedSlot = $(`.slot-option[onclick*="${selectedSlotId}"]`);
    const priceText = selectedSlot.find('.price-display').text();
    const price = parseInt(priceText.replace('TK ', '').replace(',', ''));
    
    const total = price * quantity;
    $('#total-price').text(`TK ${total.toLocaleString()}`);
  }

  // Update add to cart button
  function updateAddToCartButton() {
    const isValid = selectedVariantId && selectedSlotId && selectedDate && quantity > 0;
    $('#add-to-cart-btn').prop('disabled', !isValid);
  }

  // Form submission
  $('#booking-form').submit(function(e) {
    if (!selectedVariantId || !selectedSlotId) {
      e.preventDefault();
              toastNotifications.warning('Please select a package variant and time slot.');
      return false;
    }
  });
</script>
@endpush
