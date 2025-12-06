@extends('layouts.frontend')

@section('title', 'Build Your Own Adventure')

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontEnd/css/custom-packages.css') }}">
@endpush

@section('content')
    <div class="container my-5 default-page-marign-top">
        <!-- Section Heading -->
        <div class="text-center mb-5">
            <h5 class="section-subtitle">Our Packages</h5>
            <h2 class="section-title-bundle">{{ $page_title }}</h2>
        </div>

        <!-- Packages Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="regularPackagesContainer">
            @forelse($allPackages as $package)
                @php
                    $img = $package->display_image_url;
                @endphp
                <div class="col" data-package-id="{{ $package->id }}" data-package-type="regular">
                    <div class="card figma-card shadow-sm border-0">
                        <div class="figma-card-img-wrapper">
                            <img src="{{ $img }}" class="card-img-top" alt="{{ $package->name }}">
                        </div>

                        <div class="card-body">
                            <h2 class="bundle-title">{{ $package->name }}</h2>
                            <h5 class="activity-title">{{ $package->subtitle }}</h5>

                            <p class="bundle-price">
                                Starting from BDT
                                <span class="price-text" data-display-price="{{ $package->display_starting_price ?? '' }}">
                                    {{ $package->display_starting_price ? number_format($package->display_starting_price) : '0' }}
                                </span>
                            </p>

                            <button class="btn figma-btn w-100 btn-add-to-cart" data-package-type="regular"
                                data-package-id="{{ $package->id }}">
                                Add to Cart
                                {{-- Just click korle session a rakho and cart count update korbe remove others checking and js  --}}
                            </button>
                        </div>
                    </div>
                </div>

            @empty
                <div class="col">
                    <div class="card figma-card shadow-sm border-0 text-center py-4">
                        <img src="{{ asset('images/bundle-2.svg') }}" class="card-img-top mb-3" alt="No Packages">
                        <div class="card-body">
                            <h5 class="card-title">No Regular Packages</h5>
                            <p class="card-text">Please check back later.</p>
                            <a href="#" class="btn jatio-bg-color w-100 disabled">
                                <i class="fa-solid fa-cart-shopping me-2"></i> Add to Cart
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

   

@endsection


@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize ToastMagic
            const toastMagic = new ToastMagic();

            function toast(message, type = 'info', autoClose = true, actionText = null, actionUrl = null) {
                toastMagic[type](message, '', autoClose, actionText, actionUrl);
            }

            // Button loading state
            function setBtnLoading($btn, isLoading, loadingText = 'Processing...') {
                if (isLoading) {
                    $btn.data('original', $btn.html());
                    $btn.html(`<span class="spinner-border spinner-border-sm me-2"></span>${loadingText}`);
                    $btn.prop('disabled', true);
                } else {
                    $btn.html($btn.data('original'));
                    $btn.prop('disabled', false);
                }
            }

            // Frontend session cart helpers
            function getCart() {
                return JSON.parse(sessionStorage.getItem('cart') || '[]');
            }

            function saveCart(cart) {
                sessionStorage.setItem('cart', JSON.stringify(cart));
            }

            function updateCartCount() {
                const cart = getCart();
                const count = cart.reduce((sum, item) => sum + item.quantity, 0);
                $("#cartCount").text(count);
            }

            // Add to cart
            function addToCart(packageId, $btn) {
                setBtnLoading($btn, true, "Adding...");

                // 1️⃣ Update frontend sessionStorage
                let cart = getCart();
                let existing = cart.find(item => item.packageId === packageId);
                if (existing) {
                    existing.quantity += 1;
                } else {
                    cart.push({
                        packageId: packageId,
                        quantity: 1
                    });
                }
                saveCart(cart);
                updateCartCount();

                // 2️⃣ Update backend session via AJAX
                $.ajax({
                    url: "{{ route('frontend.cart.add') }}",
                    method: "POST",
                    data: {
                        package_id: packageId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if (res.success) toast("Item added to cart!", "success");
                    },
                    error: function(err) {
                        toast(err.responseJSON?.message || "Server error.", "error");
                    },
                    complete: function() {
                        setBtnLoading($btn, false);
                    }
                });
            }

            // Button click
            $(".btn-add-to-cart").on("click", function(e) {
                e.preventDefault();
                const $btn = $(this);
                const packageId = $btn.data("package-id");
                if (!packageId) {
                    toast("No package selected.", "warning");
                    return;
                }
                addToCart(packageId, $btn);
            });

            // Initialize cart count on page load
            updateCartCount();
        });
    </script>
@endpush
