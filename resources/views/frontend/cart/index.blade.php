@extends('layouts.frontend')

@section('title', 'Shopping Cart - ATV/UTV Adventure')

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontEnd/css/shopping-cart.css') }}">
    <style>
        .cart-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: white;
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
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

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart i {
            font-size: 4em;
            color: #ddd;
            margin-bottom: 20px;
        }

        .promo-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .order-summary {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            position: sticky;
            top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .summary-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.1em;
        }

        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
        }

        .remove-btn:hover {
            background: #c82333;
        }
    </style>
@endpush

@section('content')
    <div class="container maincontent-margin" style="margin-top: 5%;">
        <h2 class="cart-title">Shopping Cart</h2>

        @if (session('error'))
            <div class="error-message">
                <i class="bi  bi-exclamation-triangle"></i>
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="success-message">
                <i class="bi  bi-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="error-message">
                <i class="bi  bi-exclamation-triangle"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (empty($packages))
            <div class="empty-cart">
                <i class="bi  bi-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p class="text-muted">Add some adventure packages to get started!</p>
                <a href="{{ route('frontend.packages.index') }}" class="btn btn-orange jatio-bg-color">
                    Browse Packages
                </a>
            </div>
        @else
            <div class="row">
                <div class="col-lg-8">
                    @foreach ($packages as $index => $packageData)
                        @php
                            $variant = $packageData['variant'];
                            $quantity = $packageData['quantity'];
                            $date = $packageData['date'];
                            $slotId = $packageData['slot_id'];

                            // Get slot info
                            $slot = \App\Models\ScheduleSlot::find($slotId);

                            // Calculate price
                            $dayOfWeek = date('N', strtotime($date));
                            $priceType = $dayOfWeek >= 6 ? 'weekend' : 'weekday';
                            $price = $variant->prices->where('price_type', $priceType)->first();
                            $unitPrice = $price ? $price->amount : 0;
                            $subtotal = $unitPrice * $quantity;
                        @endphp

                        <div class="cart-item">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    @if ($variant->package->image)
                                        <img src="{{ asset('storage/' . $variant->package->image) }}"
                                            alt="{{ $variant->package->name }}" class="cart-item-image">
                                    @else
                                        <div
                                            class="cart-item-image bg-light d-flex align-items-center justify-content-center">
                                            <i class="bi  bi-mountain text-muted"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <h5 class="mb-1">{{ $variant->package->name }}</h5>
                                    <p class="text-muted mb-1">{{ $variant->variant_name }}</p>
                                    <p class="mb-1">
                                        <strong>Date:</strong> {{ date('F j, Y', strtotime($date)) }}<br>
                                        <strong>Time:</strong> {{ $slot ? $slot->name : 'N/A' }}<br>
                                        <strong>Report Time:</strong> {{ $slot ? $slot->report_time : 'N/A' }}
                                    </p>
                                    <p class="mb-0">
                                        <strong>Unit Price:</strong> <span class="price-display">TK
                                            {{ number_format($unitPrice) }}</span>
                                    </p>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" value="{{ $quantity }}" min="1" max="10"
                                        class="form-control quantity-input"
                                        data-key="{{ $variant->id }}_{{ $date }}_{{ $slotId }}">
                                </div>

                                <div class="col-md-2 text-end">
                                    <div class="price-display mb-2">TK {{ number_format($subtotal) }}</div>
                                    <form action="{{ route('frontend.cart.remove') }}" method="POST"
                                        style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="key"
                                            value="{{ $variant->id }}_{{ $date }}_{{ $slotId }}">
                                        <button type="submit" class="remove-btn"
                                            onclick="return confirm('Remove this item from cart?')">
                                            <i class="bi  bi-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="promo-section">
                        <h5>Promo Code</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" placeholder="Enter promo code" class="form-control" id="promo-code">
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-outline-primary w-100" id="apply-promo-btn">
                                    Apply
                                </button>
                            </div>
                        </div>
                        <div id="promo-message" class="mt-2"></div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="order-summary">
                        <h4>Order Summary</h4>

                        @php
                            $subtotal = 0;
                            $discount = 0;
                            foreach ($packages as $packageData) {
                                $variant = $packageData['variant'];
                                $quantity = $packageData['quantity'];
                                $date = $packageData['date'];

                                $dayOfWeek = date('N', strtotime($date));
                                $priceType = $dayOfWeek >= 6 ? 'weekend' : 'weekday';
                                $price = $variant->prices->where('price_type', $priceType)->first();
                                $unitPrice = $price ? $price->amount : 0;
                                $subtotal += $unitPrice * $quantity;
                            }

                            $tax = $subtotal * 0.15; // 15% VAT
                            $total = $subtotal + $tax - $discount;
                        @endphp

                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>TK {{ number_format($subtotal) }}</span>
                        </div>

                        <div class="summary-row" id="discount-row" style="display: none;">
                            <span>Discount</span>
                            <span id="discount-amount">- TK 0</span>
                        </div>

                        <div class="summary-row">
                            <span>VAT (15%)</span>
                            <span>TK {{ number_format($tax) }}</span>
                        </div>

                        <div class="summary-row">
                            <span>Total</span>
                            <span id="total-amount">TK {{ number_format($total) }}</span>
                        </div>

                        <a href="{{ route('frontend.checkout.index') }}" class="btn btn-orange jatio-bg-color w-100 mt-3">
                            Proceed to Checkout
                        </a>

                        <a href="{{ route('frontend.packages.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Auto-hide messages
        setTimeout(function() {
            $('.success-message, .error-message').fadeOut();
        }, 5000);

        // Quantity change
        $('.quantity-input').change(function() {
            const key = $(this).data('key');
            const quantity = $(this).val();

            // Update cart via AJAX
            $.post('{{ route('frontend.cart.add') }}', {
                    _token: '{{ csrf_token() }}',
                    key: key,
                    quantity: quantity
                })
                .done(function() {
                    location.reload();
                })
                .fail(function() {
                    toastNotifications.error('Error updating quantity. Please try again.');
                });
        });

        // Promo code application
        $('#apply-promo-btn').click(function() {
            const promoCode = $('#promo-code').val();
            if (!promoCode) {
                $('#promo-message').html('<div class="text-danger">Please enter a promo code.</div>');
                return;
            }

            $('#apply-promo-btn').prop('disabled', true).text('Applying...');

            $.post('{{ route('frontend.checkout.process') }}', {
                    _token: '{{ csrf_token() }}',
                    promo_code: promoCode,
                    action: 'validate_promo'
                })
                .done(function(response) {
                    if (response.valid) {
                        $('#promo-message').html(
                            '<div class="text-success">Promo code applied successfully!</div>');
                        $('#discount-row').show();
                        $('#discount-amount').text('- TK ' + response.discount.toLocaleString());
                        $('#total-amount').text('TK ' + (response.total).toLocaleString());
                    } else {
                        $('#promo-message').html('<div class="text-danger">' + response.message + '</div>');
                    }
                })
                .fail(function() {
                    $('#promo-message').html(
                        '<div class="text-danger">Error applying promo code. Please try again.</div>');
                })
                .always(function() {
                    $('#apply-promo-btn').prop('disabled', false).text('Apply');
                });
        });
    </script>
@endpush
