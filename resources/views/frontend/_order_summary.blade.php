<div class="card shadow-sm sticky-top">
    <div class="card-body">
        <!-- Order Summary Header -->
        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-shopping-cart text-orange me-2"></i>Order Summary
            </h5>

        </div>

        @php
            // Read promo discount from session
            $promoDiscount = session('promo_discount', 0);
            $amountAfterDiscount = max(0, $subtotal - $promoDiscount);
            $vatData = calculateVAT($amountAfterDiscount);
        @endphp

        <form action="{{ url('checkout') }}" method="GET">
            <div class="pt-3">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span class="fw-medium">TK {{ number_format($subtotal, 2) }}</span>
                </div>

                @if ($promoDiscount > 0)
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span><i class="bi bi-tag-fill me-1"></i>Promo Discount</span>
                        <span class="fw-medium">- TK {{ number_format($promoDiscount, 2) }}</span>
                    </div>
                @endif

                <div class="d-flex justify-content-between mt-3 pt-3">
                    <strong>VAT ({{ env('VAT_RATE', 15) }}%)</strong>
                    <strong class="fs-5">TK {{ number_format($vatData['vat'], 2) }}</strong>
                </div>

                <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                    <strong>Total Amount</strong>
                    <strong class="fs-5 text-orange">TK {{ number_format($vatData['total'], 2) }}</strong>
                </div>
            </div>

            <div class="terms-conditions mt-3 mb-3">
                <p class="text-muted small">
                    By placing your order, you agree to Adventour Adventure Bandarban's
                    <a href="{{ route('frontend.privacy-policy') }}" target="_blank"
                        class="text-decoration-none">privacy policy</a>
                    and
                    <a href="{{ route('frontend.terms-conditions') }}" target="_blank"
                        class="text-decoration-none">conditions of use</a>.
                </p>
            </div>

            <!-- Hidden inputs for cart items (UUID, quantity, ID) -->
            @foreach ($guestCartItems as $index => $ci)
                <input type="hidden" name="cart_items[{{ $index }}][uuid]" value="{{ $ci->cart_uuid }}">
                <input type="hidden" name="cart_items[{{ $index }}][qty]" value="{{ $ci->quantity }}">
            @endforeach

            <!-- Hidden inputs for subtotal, VAT, and total -->
            <input type="hidden" name="promo_discount" value="{{ $promoDiscount }}">
            <input type="hidden" name="subtotal" value="{{ $subtotal }}">
            <input type="hidden" name="vat" value="{{ $vatData['vat'] }}">
            <input type="hidden" name="total" value="{{ $vatData['total'] }}">

            @if (!isset($showPlaceOrder) || $showPlaceOrder)
                <div class="d-flex justify-content-center mt-4 gap-3">
                    <a href="{{ route('packages.custom.index') }}" class="btn continue-shopping-btn equal-btn">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                    <button type="submit" class="checkout-btn equal-btn">
                        Place Order
                    </button>
                </div>
            @else
                <div class="d-flex justify-content-center mt-4 gap-3">
                    <a href="{{ route('packages.custom.index') }}" class="btn continue-shopping-btn equal-btn">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            @endif

        </form>
    </div>
</div>
