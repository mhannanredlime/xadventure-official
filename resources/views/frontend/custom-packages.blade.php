@extends('layouts.frontend')

@section('title', 'Build Your Own Adventure')

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontEnd/css/custom-packages.css') }}">
    <style>
        /* Buttons */
        .jatio-bg-color {
            background-color: #ff782d;
            color: #fff;
            border: 2px solid #ff782d;
            font-weight: bold;
            transition: 0.3s;
        }

        .jatio-bg-color:hover {
            background-color: #e66a28;
        }

        .figma-btn {
            font-weight: 700;
            font-size: 1.2rem;
            padding: 0.6rem 1rem;
            border: 2px solid #FC692A;
            border-radius: 0.75rem;
            color: #FC692A;
            background: transparent;
            transition: 0.3s;
        }

        .figma-btn:hover {
            background: #FC692A;
            color: #fff;
        }

        /* Section Headings */
        .section-subtitle {
            font-weight: 700;
            font-size: 1.25rem;
            color: #FC692A;
            margin-bottom: 0.5rem;
        }

        .section-title-bundle {
            font-weight: 900;
            font-size: 2.5rem;
            color: #000;
            margin-bottom: 2.5rem;
            margin-top: 2.5rem;
        }

        /* Cards */
        .figma-card {
            border-radius: 0.75rem;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .figma-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
        }

        .figma-card-img-wrapper img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .bundle-title {
            font-weight: 800;
            font-size: 1.75rem;
            margin: 0.75rem 0 0.5rem;
        }

        .activity-title {
            font-weight: 500;
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
            color: #555;
        }

        .bundle-price {
            font-weight: 700;
            font-size: 1.25rem;
            color: #54595F;
            margin-bottom: 1rem;
        }

        /* Floating Cart */
        .floating-cart-icon {
            position: fixed;
            top: 120px;
            right: 30px;
            z-index: 1000;
            background: #87CEEB;
            color: #333;
            border-radius: 1.5rem;
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            border: 2px solid #fff;
            transition: 0.3s;
        }

        .floating-cart-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        /* Notifications */
        .success-notification,
        .toast-notification {
            border-radius: 0.75rem;
            padding: 1rem 1.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transition: 0.3s;
            position: fixed;
            z-index: 1001;
        }

        .success-notification.show,
        .toast-notification.show {
            opacity: 1;
        }

        .success-notification {
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            background: #10b981;
            color: #fff;
        }

        .success-notification.show {
            transform: translate(-50%, -50%) scale(1);
        }

        .success-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .success-content i {
            font-size: 1.5rem;
            background: #059669;
            border-radius: 50%;
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .success-content span {
            font-size: 1rem;
            font-weight: 600;
        }

        /* Loading Overlay */
        .loading-overlay {
            display: none;
            justify-content: center;
            align-items: center;
            background: rgba(255, 255, 255, 0.8);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
        }

        .fixed-success-msg {
            max-width: 100%;
            /* responsive width */
            width: 957px;
            /* desktop width */
            min-height: 60px;
            /* ensure enough height */
            background-color: #CCFFD4;
            border-radius: 10px;
            padding: 10px 24px;
            font-size: 1.5rem;
            /* 24px */
            color: #54595F;
            box-sizing: border-box;
            /* include padding in width */
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .fixed-success-msg {
                width: 90%;
                /* shrink on tablets */
                font-size: 1.25rem;
                /* slightly smaller text */
            }
        }

        @media (max-width: 576px) {
            .fixed-success-msg {
                width: 95%;
                /* almost full width on mobile */
                font-size: 1rem;
                /* readable on small screens */
                padding: 8px 16px;
            }
        }

        .loading-overlay.active {
            display: flex;
        }

        /* Responsive adjustments */
        @media(max-width: 768px) {
            .bundle-title {
                font-size: 1.5rem;
            }

            .activity-title {
                font-size: 1rem;
            }

            .bundle-price {
                font-size: 1.1rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container my-5 default-page-marign-top">
        <!-- Section Heading -->
        <div class="text-center mb-4">
            <h5 class="section-subtitle">Our Packages</h5>
            <h2 class="section-title-bundle">Custom Your Package</h2>
        </div>

        <div class="d-flex justify-content-center align-items-center mx-auto my-4 fixed-success-msg">
            <p class="mb-0 text-center">
                Please confirm availability before booking any bundle package that includes ATV/UTV
            </p>
        </div>



        <!-- Packages Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="regularPackagesContainer">
            @forelse($allPackages as $package)
                @php
                    $variants = $variantsByPackage[$package->id] ?? collect();
                    $firstVariant = $variants->first();
                    $img = $package->display_image_url;
                @endphp

                <div class="col" data-package-id="{{ $package->id }}" data-package-type="regular"
                    @if ($firstVariant) data-variant-id="{{ $firstVariant->id }}" @endif
                    data-min-participants="{{ $package->min_participants }}"
                    data-max-participants="{{ $package->max_participants }}">
                    <div class="card figma-card shadow-sm border-0">
                        <div class="figma-card-img-wrapper">
                            <img src="{{ $img }}" class="card-img-top" alt="{{ $package->name }}">
                        </div>
                        <div class="card-body">
                            <h2 class="bundle-title">{{ $package->name }}</h2>
                            <h5 class="activity-title">{{ $package->subtitle }}</h5>
                            <p class="bundle-price">
                                Starting from BDT
                                <span class="price-text" data-price-for-variant="{{ $firstVariant->id ?? '' }}"
                                    data-display-price="{{ $package->display_starting_price ?? '' }}">
                                    {{ $package->display_starting_price ? number_format($package->display_starting_price) : '0' }}
                                </span>
                            </p>
                            <button class="btn figma-btn w-100 btn-add-to-cart" data-package-type="regular"
                                data-package-id="{{ $package->id }}"
                                @if ($firstVariant) data-variant-id="{{ $firstVariant->id }}" @endif>
                                Add to Cart
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
                            <a href="#" class="btn jatio-bg-color w-100 disabled"><i
                                    class="fa-solid fa-cart-shopping me-2"></i> Add to Cart</a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Floating Cart Icon -->
    <a href="{{ route('regular-packages-booking') }}" class="floating-cart-icon" id="floatingCartIcon">
        <i class="fa-solid fa-cart-shopping"></i>
        <span class="cart-count" id="cartCount">{{ $cartCount ?? 0 }}</span>
        <span class="cart-text">Items</span>
    </a>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Success Notification -->
    <div class="success-notification" id="successNotification" style="display: none;">
        <div class="success-content">
            <i class="bi bi-check"></i>
            <span id="successMessage">Item added to cart successfully!</span>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- JS will go here -->
@endpush
