@extends('layouts.frontend')
@section('title', 'Build Your Own Adventure')

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontEnd/css/custom-packages.css') }}">
    <style>
        /* Specific overrides for the new design */
        .page-header-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #000;
            text-align: center;
            margin-bottom: 20px;
        }

        .section-header-bundle {
            font-size: 2rem;
            font-weight: 800;
            margin-top: 40px;
            margin-bottom: 30px;
            text-align: center;
            color: #000;
        }

        .btn-qty-selector {
            background-color: #FC692A;
            color: white;
            border: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 1rem;
            border-radius: 0.75rem;
            font-weight: 700;
            width: 100%;
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
            width: 30px;
            height: 30px;
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

@section('content')
    <div class="container my-5 default-page-marign-top">

        <!-- Floating Cart -->
        <x-floating-cart />

        <!-- Header Section -->
        <div class="text-center mb-4">
            <h5 class="section-subtitle">Our Packages</h5>
            <h1 class="page-header-title">Bundle Package</h1>
        </div>

        <!-- Availability Notification -->
        <div class="d-flex justify-content-center mb-5">
            <div class="fixed-success-msg text-center">
                Please confirm availability before booking any bundle package that includes ATV/UTV
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="regularPackagesContainer">
            @forelse($allPackages as $package)
                <div class="col" x-data="packageCard({{ $package->id }}, {{ $package->display_starting_price ?? 0 }}, {{ $package->min_participants ?? 1 }}, {{ $package->max_participants ?? 'null' }})">
                    <div class="card figma-card shadow-sm border-0 h-100">
                        <div class="figma-card-img-wrapper">
                            @php
                                $img = $package->images->first()
                                    ? asset('storage/' . $package->images->first()->image_path)
                                    : asset('frontEnd/images/advan-slider-2.svg');
                            @endphp
                            <img src="{{ $img }}" class="card-img-top" alt="{{ $package->name }}">
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h2 class="bundle-title">{{ $package->name }}</h2>
                            <h5 class="activity-title">{{ $package->subtitle ?? 'Adventure Package' }}</h5>

                            <p class="bundle-price mt-auto">
                                {{ $package->display_starting_price ? 'TK ' . number_format($package->display_starting_price) : 'Contact for Price' }}
                            </p>

                            <!-- Add to Cart / Quantity Toggle -->
                            <div class="mt-3">
                                <template x-if="!inCart">
                                    <button class="btn figma-btn w-100" @click="addToCart" :disabled="loading">
                                        <span x-show="!loading">Add to Cart</span>
                                        <span x-show="loading" class="spinner-border spinner-border-sm"></span>
                                    </button>
                                </template>

                                <template x-if="inCart">
                                    <div class="btn-qty-selector">
                                        <button class="qty-btn" @click="decrement" :disabled="loading">−</button>
                                        <span><span x-text="qty"></span> Package</span>
                                        <button class="qty-btn" @click="increment" :disabled="loading">+</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <h3>No packages available at the moment.</h3>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('packageCard', (packageId, price, minQty = 1, maxQty = null) => ({
                id: packageId,
                price: price,
                min: minQty || 1,
                max: maxQty || 999,
                qty: 0,
                inCart: false,
                loading: false,

                init() {
                    this.checkCartStatus();
                    // Listen for global cart updates to sync state
                    window.addEventListener('cart-updated', () => this.checkCartStatus());
                },

                async checkCartStatus() {
                    // First check server for actual cart state (handles cart cleared after payment)
                    try {
                        const response = await fetch("{{ route('api.cart.status') }}", {
                            method: "GET",
                            headers: {
                                "Accept": "application/json"
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();

                            // Sync sessionStorage with server data
                            sessionStorage.setItem('cart', JSON.stringify(data.items || []));

                            // Find this package in the server cart
                            const item = (data.items || []).find(i => i.packageId === this.id);
                            if (item) {
                                this.qty = item.quantity;
                                this.inCart = true;
                            } else {
                                this.qty = 0;
                                this.inCart = false;
                            }

                            // Update floating cart count
                            const badge = document.getElementById('floatingCartCount');
                            if (badge) badge.innerText = data.count || 0;

                            return;
                        }
                    } catch (e) {
                        console.warn('Cart status fetch failed, falling back to sessionStorage');
                    }

                    // Fallback to sessionStorage if server fails
                    const cart = JSON.parse(sessionStorage.getItem('cart') || '[]');
                    const item = cart.find(i => i.packageId === this.id);
                    if (item) {
                        this.qty = item.quantity;
                        this.inCart = true;
                    } else {
                        this.qty = 0;
                        this.inCart = false;
                    }
                },

                async addToCart() {
                    this.loading = true;
                    await this.syncCart(this.min);
                    this.loading = false;
                },

                async increment() {
                    // Check max limit
                    if (this.qty >= this.max) {
                        new ToastMagic().warning(`Maximum ${this.max} participants allowed.`);
                        return;
                    }

                    this.loading = true;
                    await this.syncCart(1);
                    this.loading = false;
                },

                async decrement() {
                    this.loading = true;

                    // If trying to decrement below min, remove from cart
                    if (this.qty <= this.min) {
                        await this.removeFromCart();
                        // Reset local state handled by removeFromCart
                        this.qty = 0;
                        this.inCart = false;

                    } else {
                        await this.syncCart(-1);
                    }
                    this.loading = false;
                },

                // Helper to sync with Backend & SessionStorage
                async updateCart(newQty) {
                    // Calculate delta (backend adds, so we need to know what to send)
                    // Wait, our backend addToCart ADDS to existing.
                    // If we want to set absolute quantity, we'd need a different endpoint.
                    // But we can just send +1 or -1.
                    // However, this function takes "newQty". To make this robust:

                    // Actually, let's look at how we call it.
                    // increment -> qty++ -> updateCart(qty)
                    // decrement -> qty-- -> updateCart(qty)
                    // addToCart -> qty=min -> updateCart(qty)

                    // We need to know the DIFF.
                    // Let's change the params or logic.
                    // Easier: pass the DELTA to this function, or calculcate it.
                    // But 'qty' is already updated in local state before calling this.
                    // Let's stick to the plan: "Refactor updateCart(newQty) to syncCart(changeAmount)"
                },

                async syncCart(changeAmount) {
                    // 1. Update Local State
                    this.qty += changeAmount;
                    if (this.qty < 0) this.qty = 0; // Ensure quantity doesn't go below zero
                    this.inCart = this.qty > 0;

                    // 2. Update SessionStorage
                    let cart = JSON.parse(sessionStorage.getItem('cart') || '[]');
                    const index = cart.findIndex(i => i.packageId === this.id);

                    if (this.qty > 0) {
                        if (index > -1) {
                            cart[index].quantity = this.qty;
                        } else {
                            cart.push({
                                packageId: this.id,
                                quantity: this.qty
                            });
                        }
                    } else {
                        if (index > -1) cart.splice(index, 1);
                    }
                    sessionStorage.setItem('cart', JSON.stringify(cart));
                    this.updateGlobalCartCount();

                    // 3. Sync with Backend
                    try {
                        const response = await fetch("{{ route('cart.add') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                package_id: this.id,
                                quantity: changeAmount // Send +1, -1, or initial qty
                            })
                        });

                        if (!response.ok) throw new Error('Network response was not ok');

                    } catch (e) {
                        console.error('Cart sync error', e);
                        new ToastMagic().error('Failed to sync cart. Please refresh.');
                        // We might want to revert logic here but it gets complex.
                    }
                },

                async removeFromCart() {
                    // 1. Update SessionStorage
                    let cart = JSON.parse(sessionStorage.getItem('cart') || '[]');
                    const index = cart.findIndex(i => i.packageId === this.id);
                    if (index > -1) cart.splice(index, 1);
                    sessionStorage.setItem('cart', JSON.stringify(cart));
                    this.updateGlobalCartCount();

                    // 2. Backend Sync
                    try {
                        await fetch("{{ route('cart.remove-package') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                package_id: this.id
                            })
                        });
                    } catch (e) {
                        console.error('Remove cart error', e);
                    }
                },

                updateGlobalCartCount() {
                    const cart = JSON.parse(sessionStorage.getItem('cart') || '[]');
                    const count = cart.reduce((sum, item) => sum + item.quantity, 0);

                    // Update floating badge
                    const badge = document.getElementById('floatingCartCount');
                    if (badge) badge.innerText = count;

                    // Update Navbar Badge if exists
                    // window.updateCartCount(); // Call the global function from layout
                }
            }));
        });

        // Initial Cart Count Load
        document.addEventListener('DOMContentLoaded', () => {
            const cart = JSON.parse(sessionStorage.getItem('cart') || '[]');
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            const badge = document.getElementById('floatingCartCount');
            if (badge) badge.innerText = count;
        });
    </script>
@endpush
