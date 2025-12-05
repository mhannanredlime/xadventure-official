@extends('layouts.frontend')

@section('title', 'Your order summary')

@section('content')
    <div class="container mt-5 default-page-marign-top">
        <h2 class="mb-4">
            Your order summary
            <small class="text-muted">
                Reservation Date: {{ $selected_date }}
            </small>
        </h2>
        @if ($guestCartItems->count() > 0)
            <div class="row">
                <div class="col-lg-8">
                    {{-- HEre show time slot --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <table class="table table-borderless align-middle">
                                <thead class="border-bottom">
                                    <tr>
                                        <th>SL</th>
                                        <th width="50%">Package</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        {{-- <th>Action</th> --}}
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
                                                        @if (isset($time_slot))
                                                            <div class="schedule-color small mt-1">
                                                                Schedule:
                                                                {{ $time_slot->name ?? $time_slot->start_time . ' - ' . $time_slot->end_time }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>TK {{ number_format($item->cart_amount, 2) }}</td>
                                            <td>
                                                <div class="d-flex align-items-right">
                                                    <span class="mx-3 fw-bold">{{ $item->quantity }}</span>

                                                </div>
                                            </td>

                                            <td class="d-flex align-items-right"><strong>TK
                                                    {{ number_format($itemTotal, 2) }}</strong></td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Promo Section -->
                    <div class="promo-section mt-3 d-flex">
                        <input type="text" placeholder="Promo Code" class="form-control me-2" id="promo-code"
                            value="{{ $appliedPromoCode->code ?? '' }}" {{ isset($appliedPromoCode) ? 'readonly' : '' }}>

                        @if (isset($appliedPromoCode))
                            <button class="btn btn-danger me-2" id="remove-promo-btn" onclick="removePromo()">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        @endif

                        <button class="btn btn-orange jatio-bg-color primary-btn-border-radius apply-promo-btn"
                            id="apply-promo-btn" onclick="applyPromo()" {{ isset($appliedPromoCode) ? 'disabled' : '' }}>
                            {{ isset($appliedPromoCode) ? 'Applied!' : 'Apply' }}
                        </button>
                    </div>

                    <!-- Promo Message -->
                    <div id="promo-message" class="mt-2 small"></div>



                </div>
                <!-- Order Summary -->
                <div class="col-lg-4">
                    @php
                        $subtotal = $guestCartItems->sum(function ($item) {
                            return $item->cart_amount * $item->quantity;
                        });
                    @endphp
                    @include('frontend.checkout._order_summary', [
                        'guestCartItems' => $guestCartItems,
                        'subtotal' => $subtotal,
                        'showPlaceOrder' => true,
                    ])
                </div>

            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-shopping-cart fa-4x text-muted"></i>
                </div>
                <h3>Your cart is empty</h3>
                <p class="text-muted">Looks like you haven't added any packages yet.</p>
                <a href="{{ route('frontend.packages.index') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-store me-2"></i>Browse Packages
                </a>
            </div>
        @endif
    </div>
@endsection


@push('styles')
    <style>
        .object-fit-cover {
            object-fit: cover;
        }

        .schedule-color {
            color: #e55a22;
            font-weight: bold;
        }

        .equal-btn {
            width: 100%;
            max-width: 325px;
            text-decoration: none;
        }

        .sticky-top {
            position: sticky;
            top: 20px;
        }

        .table tbody tr:hover {
            background: rgba(0, 0, 0, 0.03);
        }

        .checkout-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px 10px;
            height: 57px;
            background: #FC692A;
            color: #fff;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: .3s;
        }

        .checkout-btn:hover {
            background: #e55a22;
            transform: translateY(-2px);
        }

        .continue-shopping-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px 16px;
            border-radius: 12px;
            border: 2px solid #FC692A;
            color: #FC692A;
            background: #fff;
            text-decoration: none;
            transition: .3s;
        }

        .continue-shopping-btn:hover {
            background: #FC692A;
            color: #fff;
        }

        .apply-promo-btn {
            min-width: 110px;
        }

        #promo-message {
            font-size: 14px;
        }

        #remove-promo-btn {
            width: 48px;
        }

        @media(max-width: 576px) {
            .checkout-btn {
                height: 50px;
            }
        }
    </style>
@endpush


@push('scripts')
    <script>
        // Global function: Show message under promo input
        function setPromoMessage(message, type = 'success') {
            const box = document.getElementById('promo-message');
            box.innerHTML = `<div class="text-${type}"><i class="bi bi-info-circle"></i> ${message}</div>`;
        }

        function applyPromo() {
            const promoCode = document.getElementById('promo-code').value.trim();
            const applyBtn = document.getElementById('apply-promo-btn');
            const promoInput = document.getElementById('promo-code');

            if (!promoCode) {
                setPromoMessage('Please enter a promo code.', 'danger');
                return;
            }

            // Disable UI
            applyBtn.disabled = true;
            promoInput.disabled = true;
            applyBtn.innerHTML = `<i class="bi bi-arrow-repeat fa-spin"></i> Applying...`;

            fetch('{{ route('frontend.cart.validate-promo') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        promo_code: promoCode
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        setPromoMessage(`Promo "${promoCode}" applied! Discount: ${data.discount_formatted}`,
                            'success');
                        applyBtn.innerHTML = "Applied!";
                        promoInput.readOnly = true;

                        // Insert remove button dynamically
                        if (!document.getElementById('remove-promo-btn')) {
                            const removeBtn = document.createElement('button');
                            removeBtn.className = "btn btn-danger me-2";
                            removeBtn.id = "remove-promo-btn";
                            removeBtn.innerHTML = `<i class="bi bi-x"></i>`;
                            removeBtn.onclick = removePromo;

                            applyBtn.parentNode.insertBefore(removeBtn, applyBtn);
                        }

                        // Refresh page so totals update
                        setTimeout(() => location.reload(), 5000);
                    } else {
                        setPromoMessage(data.message, 'danger');

                        applyBtn.innerHTML = "Apply";
                        applyBtn.disabled = false;
                        promoInput.disabled = false;
                    }
                })
                .catch(() => {
                    setPromoMessage("Something went wrong. Try again.", 'danger');

                    applyBtn.innerHTML = "Apply";
                    applyBtn.disabled = false;
                    promoInput.disabled = false;
                });
        }

        function removePromo() {
            const removeBtn = document.getElementById('remove-promo-btn');
            removeBtn.disabled = true;
            removeBtn.innerHTML = `<i class="bi bi-arrow-repeat fa-spin"></i>`;

            fetch('{{ route('frontend.cart.remove-promo') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        setPromoMessage(data.message, 'success');
                        setTimeout(() => location.reload(), 5000);
                    } else {
                        removeBtn.innerHTML = `<i class="bi bi-x"></i>`;
                        removeBtn.disabled = false;
                        setPromoMessage("Failed to remove promo.", "danger");
                    }
                })
                .catch(() => {
                    removeBtn.innerHTML = `<i class="bi bi-x"></i>`;
                    removeBtn.disabled = false;
                    setPromoMessage("Something went wrong. Try again.", "danger");
                });
        }

        // Submit promo on Enter
        document.getElementById('promo-code')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyPromo();
            }
        });
    </script>
@endpush
