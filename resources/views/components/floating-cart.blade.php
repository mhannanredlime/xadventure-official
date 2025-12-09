<a href="{{ route('packages.regular.index') }}" class="floating-cart text-decoration-none">
    <div class="cart-badge-icon">
        <i class="bi bi-cart3 fs-5"></i>
        <div class="d-flex flex-column align-items-center lh-1">
            <span id="floatingCartCount" class="fs-5 item-count">{{ $cartCount ?? 0 }}</span>
            <span style="font-size: 10px;">Items</span>
        </div>
    </div>
</a>

@push('styles')
    <style>
        .floating-cart {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1050;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .floating-cart:hover {
            transform: scale(1.05);
        }

        .cart-badge-icon {
            background: #A6E7D8;
            color: #000;
            border: 2px solid #fff;
            border-radius: 12px;
            padding: 8px 16px;
            font-weight: 800;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 991px) {
            .cart-badge-icon {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
        }
    </style>
@endpush
