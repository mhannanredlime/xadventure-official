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
                                            <td x-data="cartItem({{ $item->package->id }}, {{ $item->quantity }})">
                                                <div class="btn-qty-selector">
                                                    <button class="qty-btn" @click="decrement"
                                                        :disabled="loading">−</button>
                                                    <span x-text="qty"></span>
                                                    <button class="qty-btn" @click="increment"
                                                        :disabled="loading">+</button>
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
                    <div class="promo-section card shadow-sm mt-3" x-data="promoComponent()">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="bi bi-tag-fill text-orange me-2"></i>Have a Promo Code?</h6>

                            <div class="d-flex gap-2">
                                <div class="flex-grow-1 position-relative">
                                    <input type="text" placeholder="Enter promo code" class="form-control"
                                        :class="{ 'border-success': applied, 'is-invalid': errorMessage }"
                                        x-model="promoCode" :readonly="applied || loading"
                                        @keydown.enter.prevent="applyPromo" style="text-transform: uppercase;">
                                    <template x-if="applied">
                                        <i class="bi bi-check-circle-fill text-success position-absolute"
                                            style="right: 12px; top: 50%; transform: translateY(-50%);"></i>
                                    </template>
                                </div>

                                <template x-if="applied">
                                    <button class="btn btn-outline-danger" @click="removePromo" :disabled="loading"
                                        title="Remove promo code">
                                        <i class="bi"
                                            :class="loading ? 'bi-arrow-repeat spin-animation' : 'bi-trash'"></i>
                                    </button>
                                </template>

                                <button class="btn btn-orange jatio-bg-color apply-promo-btn" @click="applyPromo"
                                    :disabled="applied || loading || !promoCode.trim()" :class="{ 'btn-success': applied }">
                                    <template x-if="loading && !applied">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="spinner-border spinner-border-sm"></span>
                                            Applying...
                                        </span>
                                    </template>
                                    <template x-if="!loading && !applied">
                                        <span><i class="bi bi-check2 me-1"></i>Apply</span>
                                    </template>
                                    <template x-if="applied">
                                        <span><i class="bi bi-check-circle me-1"></i>Applied</span>
                                    </template>
                                </button>
                            </div>

                            <!-- Success Message (only shown after user applies promo code) -->
                            <template x-if="showSuccessMessage && discountAmount && parseFloat(discountAmount) > 0">
                                <div x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                                    x-transition:enter-end="opacity-100 transform translate-y-0"
                                    class="alert alert-success mt-3 mb-0 d-flex align-items-center py-2">
                                    <i class="bi bi-gift-fill me-2 fs-5"></i>
                                    <div>
                                        <strong>Congratulations!</strong>
                                        <span x-text="'You saved ' + discountFormatted"></span>
                                    </div>
                                </div>
                            </template>

                            <!-- Error Message -->
                            <template x-if="errorMessage && errorMessage.length > 0">
                                <div x-transition class="alert alert-danger mt-3 mb-0 d-flex align-items-center py-2">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <span x-text="errorMessage"></span>
                                </div>
                            </template>
                        </div>
                    </div>



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
                <a href="{{ route('packages.custom.index') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-store me-2"></i>Browse Packages
                </a>
            </div>
        @endif
    </div>
@endsection


@push('styles')
    <style>
        /* Navbar styling for pages without hero section */
        .navbar {
            background-color: #1a1a2e !important;
            position: fixed !important;
        }

        .text-orange {
            color: #fc692a !important;
        }

        .spin-animation {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Hide Alpine.js elements until initialized */
        [x-cloak] {
            display: none !important;
        }

        /* Promo Section Styling */
        .promo-section .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
        }

        .promo-section .form-control:focus {
            border-color: #fc692a;
            box-shadow: 0 0 0 0.2rem rgba(252, 105, 42, 0.25);
        }

        .promo-section .apply-promo-btn {
            min-width: 120px;
            border-radius: 8px !important;
            font-weight: 600;
        }

        .promo-section .alert {
            border-radius: 8px;
            font-size: 14px;
        }

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

        .btn-qty-selector {
            background-color: #FC692A;
            color: white;
            border: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.3rem 0.8rem;
            border-radius: 0.75rem;
            font-weight: 700;
            width: 120px;
            transition: all 0.3s;
        }

        .btn-qty-selector:hover {
            background-color: #e65a1e;
            color: white;
        }

        .qty-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .qty-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
        }
    </style>
@endpush


@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('cartItem', (packageId, initialQty, minQty = 1, maxQty = 999) => ({
                id: packageId,
                qty: initialQty,
                min: minQty,
                max: maxQty,
                loading: false,

                async increment() {
                    if (this.qty >= this.max) return;
                    this.loading = true;
                    // Send +1 delta
                    await this.syncCart(1);
                },

                async decrement() {
                    this.loading = true;
                    if (this.qty <= this.min) {
                        if (confirm('Remove this item from cart?')) {
                            await this.removeFromCart();
                        } else {
                            this.loading = false;
                        }
                    } else {
                        // Send -1 delta
                        await this.syncCart(-1);
                    }
                },

                async syncCart(changeAmount) {
                    try {
                        const response = await fetch("{{ route('cart.add') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                package_id: this.id,
                                quantity: changeAmount
                            })
                        });

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            this.loading = false;
                            alert('Failed to update cart.');
                        }
                    } catch (e) {
                        console.error(e);
                        this.loading = false;
                    }
                },

                async removeFromCart() {
                    try {
                        const response = await fetch("{{ route('cart.remove-package') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                package_id: this.id
                            })
                        });
                        if (response.ok) {
                            window.location.reload();
                        } else {
                            this.loading = false;
                        }
                    } catch (e) {
                        console.error(e);
                        this.loading = false;
                    }
                }
            }));

            Alpine.data('promoComponent', () => ({
                promoCode: '{{ $appliedPromoCode->code ?? '' }}',
                applied: {{ isset($appliedPromoCode) ? 'true' : 'false' }},
                discountAmount: {{ session('promo_discount', 0) }},
                discountFormatted: 'TK {{ number_format(session('promo_discount', 0), 2) }}',
                showSuccessMessage: false, // Only true after user clicks Apply
                errorMessage: '',
                loading: false,

                async applyPromo() {
                    if (!this.promoCode.trim()) {
                        this.errorMessage = 'Please enter a promo code.';
                        return;
                    }

                    this.loading = true;
                    this.errorMessage = '';

                    try {
                        const response = await fetch('{{ route('cart.validate-promo') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                promo_code: this.promoCode.toUpperCase()
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.applied = true;
                            this.discountAmount = data.discount;
                            this.discountFormatted = data.discount_formatted;
                            this.showSuccessMessage =
                                true; // Show message only after successful apply
                            this.errorMessage = '';
                            // Reload to update order summary after a short delay
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            this.errorMessage = data.message;
                            this.applied = false;
                        }
                    } catch (error) {
                        this.errorMessage = 'Something went wrong. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                },

                async removePromo() {
                    this.loading = true;
                    try {
                        const response = await fetch('{{ route('cart.remove-promo') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.promoCode = '';
                            this.applied = false;
                            this.discountAmount = 0;
                            this.discountFormatted = 'TK 0.00';
                            this.errorMessage = '';
                            setTimeout(() => location.reload(), 800);
                        } else {
                            this.errorMessage = 'Failed to remove promo code.';
                        }
                    } catch (error) {
                        this.errorMessage = 'Something went wrong.';
                    } finally {
                        this.loading = false;
                    }
                }
            }));
        });
    </script>
@endpush
