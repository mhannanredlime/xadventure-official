@extends('layouts.frontend')

@section('title', 'Build Your Own Adventure')

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontEnd/css/adventure-2.css') }}">
    <style>
        .jatio-bg-color {
            background-color: #ff782d;
            color: #ffffff;
            border-color: #ff782d;
        }

        .jatio-bg-color:hover {
            background-color: #e66a28 !important;
            color: #ffffff;
        }

        .hero-section {
            background: linear-gradient(rgba(255, 255, 255, 0.066), rgba(255, 255, 255, 0.5)), no-repeat center center;
            background-size: cover;
            color: #000;
            height: 40vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin-bottom: 3rem;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }

        .hero-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #000;
            padding-top: 10%;
        }

        .quantity-selector {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 10px;
            color: #fff;
            padding: 7px 25px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .quantity-selector .quantity-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
            padding: 0 10px;
            line-height: 0.6;
        }

        .quantity-selector .quantity-text {
            flex-grow: 1;
            text-align: center;
            min-width: 100px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Floating Cart Icon Styles */
        .floating-cart-icon {
            position: fixed;
            top: 120px;
            right: 30px;
            z-index: 1000;
            background: #87CEEB;
            border-radius: 25px;
            padding: 15px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            color: #333;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: 2px solid #fff;
        }

        .floating-cart-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            color: #333;
            text-decoration: none;
        }

        .floating-cart-icon i {
            font-size: 18px;
            color: #333;
        }

        .floating-cart-icon .cart-count {
            font-weight: bold;
            color: #333;
        }

        .floating-cart-icon .cart-text {
            color: #333;
        }

        /* Success notification styles */
        .success-notification {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            background: #10b981;
            color: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            z-index: 1001;
            opacity: 0;
            transition: all 0.3s ease;
            min-width: 300px;
            text-align: center;
        }

        .success-notification.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        .success-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .success-content i {
            font-size: 24px;
            color: white;
            background: #059669;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-content span {
            font-size: 16px;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="hero-section">
        <h1>Build Your Own Adventure!</h1>
    </div>


    <div class="container my-5">
        <center>
            <div class="alert alert-success text-center d-inline-block" role="alert">
                <span>Please confirm availability before booking any bundle package that includes ATV/UTV</span>
            </div>
        </center>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <div class="col">
                <div class="custom-card">
                    <img src="images/atv-trial.svg" class="card-img-top" alt="Climbing">
                    <div class="card-body">
                        <h5 class="card-title">Climbing</h5>
                        <p class="card-text">30 (min) Guided Tour<br><span class="price-text">TK 6,999</span></p>
                        <center>
                            <a href="{{ url('/cart-2') }}" class="btn jatio-bg-color w-100"><i
                                    class="fa-solid fa-cart-shopping me-2"></i> Add to Cart</a>
                        </center>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="custom-card">
                    <img src="images/bundle-2.svg" class="card-img-top" alt="Archery Target">
                    <div class="card-body">
                        <h5 class="card-title">Archery Target</h5>
                        <p class="card-text">30 (min) Guided Tour<br><span class="price-text">TK 6,999</span></p>
                        <center>
                            <div class="quantity-selector jatio-bg-color w-100">
                                <button class="quantity-btn minus">-</button>
                                <span class="quantity-text">3 Package</span>
                                <button class="quantity-btn plus">+</button>
                            </div>
                        </center>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5 mb-3">
            <span class="package-badge mb-3"></span>
            <div class="line-divider"></div>
            <h2 class="bundle-title">Custom Your Package</h2>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @forelse($packages as $package)
                @php
                    $variants = $variantsByPackage[$package->id] ?? collect();
                    $firstVariant = $variants->first();
                    $img = $package->display_image_url;
                @endphp
                <div class="col">
                    <div class="custom-card" data-package-id="{{ $package->id }}"
                        @if ($firstVariant) data-variant-id="{{ $firstVariant->id }}" @endif>
                        <img src="{{ $img }}" class="card-img-top" alt="{{ $package->name }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $package->name }}</h5>
                            <p class="card-text">30 (min) Guided Tour<br><span class="price-text"
                                    data-price-for-variant="{{ $firstVariant->id ?? '' }}">TK 0</span></p>
                            <center>
                                <div class="add-to-cart-container">
                                    <a href="#" class="btn jatio-bg-color w-100 btn-add-regular"
                                        @if ($firstVariant) data-variant-id="{{ $firstVariant->id }}" @endif>
                                        <i class="fa-solid fa-cart-shopping me-2"></i> Add to Cart
                                    </a>
                                    <div class="quantity-controls" style="display: none;">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <button class="btn btn-outline-secondary btn-sm quantity-btn-minus"
                                                type="button">-</button>
                                            <span class="quantity-display">1 Package</span>
                                            <button class="btn btn-outline-secondary btn-sm quantity-btn-plus"
                                                type="button">+</button>
                                        </div>
                                    </div>
                                </div>
                            </center>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col">
                    <div class="custom-card">
                        <img src="images/bundle-2.svg" class="card-img-top" alt="No Packages">
                        <div class="card-body">
                            <h5 class="card-title">No Regular Packages</h5>
                            <p class="card-text">Please check back later.<br><span class="price-text">&nbsp;</span></p>
                            <center>
                                <a href="#" class="btn jatio-bg-color w-100 disabled"><i
                                        class="fa-solid fa-cart-shopping me-2"></i> Add to Cart</a>
                            </center>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];

            function setBtnLoading(btn, isLoading, loadingText) {
                if (!btn) return;
                if (isLoading) {
                    btn.setAttribute('data-original-text', btn.innerHTML);
                    btn.innerHTML =
                        `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${loadingText || 'Processing...'}`;
                    btn.classList.add('disabled');
                    btn.setAttribute('aria-disabled', 'true');
                } else {
                    const original = btn.getAttribute('data-original-text');
                    if (original) btn.innerHTML = original;
                    btn.classList.remove('disabled');
                    btn.removeAttribute('aria-disabled');
                }
            }

            function showSuccessMessage(message) {
                // Create success notification
                const notification = document.createElement('div');
                notification.className = 'success-notification';
                notification.innerHTML = `
      <div class="success-content">
        <i class="bi  bi-check"></i>
        <span>${message}</span>
      </div>
    `;

                // Add to page
                document.body.appendChild(notification);

                // Show notification
                setTimeout(() => {
                    notification.classList.add('show');
                }, 100);

                // Remove after 3 seconds
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 300);
                }, 3000);
            }

            function fetchJson(url) {
                return new Promise((resolve, reject) => {
                    const controller = new AbortController();
                    const id = setTimeout(() => controller.abort(), 10000);
                    fetch(url, {
                            signal: controller.signal
                        })
                        .then(async res => {
                            clearTimeout(id);
                            if (!res.ok) {
                                const text = await res.text().catch(() => '');
                                return reject(new Error(
                                    `HTTP ${res.status}: ${text || res.statusText}`));
                            }
                            return res.json().then(resolve).catch(err => reject(new Error(
                                'Invalid JSON response')));
                        })
                        .catch(err => {
                            clearTimeout(id);
                            reject(err);
                        });
                });
            }

            function bindAddToCart(btn) {
                if (!btn || btn.getAttribute('data-bound') === '1') return;
                btn.setAttribute('data-bound', '1');

                // Get the container and quantity controls
                const container = btn.closest('.add-to-cart-container');
                const quantityControls = container.querySelector('.quantity-controls');
                const quantityDisplay = container.querySelector('.quantity-display');
                const minusBtn = container.querySelector('.quantity-btn-minus');
                const plusBtn = container.querySelector('.quantity-btn-plus');

                let currentQuantity = 1;
                let cartKey = null;

                // Function to add/update item in cart
                function addToCart(quantity) {
                    const variantId = btn.getAttribute('data-variant-id');
                    if (!variantId) {
                        alert('This package is not available right now.');
                        return Promise.reject(new Error('No variant ID'));
                    }

                    setBtnLoading(btn, true, 'Adding...');

                    const tryAddForDate = (offsetDays = 0) => {
                        const date = new Date();
                        date.setDate(date.getDate() + offsetDays);
                        const dateStr = date.toISOString().split('T')[0];

                        return fetchJson(
                                `/api/schedule-slots/availability?variant_id=${variantId}&date=${dateStr}`)
                            .then(slots => {
                                const open = Array.isArray(slots) ? slots.find(s => s.is_open && s
                                    .available_total > 0) : null;
                                if (open) {
                                    return fetch(`{{ route('frontend.cart.add') }}`, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            },
                                            body: JSON.stringify([{
                                                variant_id: Number(variantId),
                                                quantity: quantity,
                                                date: dateStr,
                                                slot_id: String(open.id)
                                            }])
                                        })
                                        .then(async res => {
                                            let body = null;
                                            try {
                                                body = await res.json();
                                            } catch (e) {}
                                            if (!res.ok) {
                                                let message = 'Unable to add to cart.';
                                                if (body) {
                                                    if (body.message) message = body.message;
                                                    if (body.errors) {
                                                        const firstKey = Object.keys(body.errors)[
                                                        0];
                                                        if (firstKey && body.errors[firstKey] &&
                                                            body.errors[firstKey][0]) {
                                                            message = body.errors[firstKey][0];
                                                        }
                                                    }
                                                }
                                                throw new Error(message);
                                            }
                                            return body;
                                        });
                                } else if (offsetDays < 30) {
                                    return tryAddForDate(offsetDays + 1);
                                } else {
                                    throw new Error('No availability found in the next 30 days.');
                                }
                            });
                    };

                    return tryAddForDate(0);
                }

                // Handle quantity increment/decrement with automatic cart update
                minusBtn.addEventListener('click', function() {
                    if (currentQuantity > 1) {
                        currentQuantity--;
                        quantityDisplay.textContent =
                            `${currentQuantity} Package${currentQuantity > 1 ? 's' : ''}`;

                        // Update cart with new quantity
                        addToCart(currentQuantity)
                            .then(data => {
                                if (data && data.success) {
                                    if (typeof updateCartCount === 'function') updateCartCount();
                                    showSuccessMessage('Quantity updated successfully!');
                                }
                            })
                            .catch(err => {
                                // Revert quantity on error
                                currentQuantity++;
                                quantityDisplay.textContent =
                                    `${currentQuantity} Package${currentQuantity > 1 ? 's' : ''}`;
                                alert(`Error updating quantity: ${err.message}`);
                            })
                            .finally(() => {
                                setBtnLoading(btn, false);
                            });
                    }
                });

                plusBtn.addEventListener('click', function() {
                    currentQuantity++;
                    quantityDisplay.textContent =
                        `${currentQuantity} Package${currentQuantity > 1 ? 's' : ''}`;

                    // Update cart with new quantity
                    addToCart(currentQuantity)
                        .then(data => {
                            if (data && data.success) {
                                updateCartCount();
                                showSuccessMessage('Quantity updated successfully!');
                            }
                        })
                        .catch(err => {
                            // Revert quantity on error
                            currentQuantity--;
                            quantityDisplay.textContent =
                                `${currentQuantity} Package${currentQuantity > 1 ? 's' : ''}`;
                            alert(`Error updating quantity: ${err.message}`);
                        })
                        .finally(() => {
                            setBtnLoading(btn, false);
                        });
                });

                // Handle "Add to Cart" button click - immediately add 1 item and show quantity controls
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (btn.classList.contains('disabled')) return;

                    // Add 1 item to cart immediately
                    addToCart(1)
                        .then(data => {
                            if (data && data.success) {
                                updateCartCount();
                                showSuccessMessage('Item added to cart successfully!');

                                // Show quantity controls for further adjustments
                                btn.style.display = 'none';
                                quantityControls.style.display = 'block';
                                currentQuantity = 1;
                                quantityDisplay.textContent =
                                    `${currentQuantity} Package${currentQuantity > 1 ? 's' : ''}`;
                            } else {
                                alert(data.message || 'Unable to add to cart.');
                            }
                        })
                        .catch(err => {
                            alert(`Error adding to cart: ${err.message}`);
                        })
                        .finally(() => {
                            setBtnLoading(btn, false);
                        });
                });
            }

            // Populate prices for visible regular package variants
            document.querySelectorAll('[data-price-for-variant]').forEach(function(span) {
                const variantId = span.getAttribute('data-price-for-variant');
                if (!variantId) return;
                span.textContent = 'Loading...';
                fetchJson(`/api/pricing/date?date=${today}&variant_id=${variantId}`)
                    .then(data => {
                        if (data && typeof data.final_price !== 'undefined') {
                            span.textContent = 'TK ' + Number(data.final_price).toLocaleString();
                        } else {
                            span.textContent = 'TK —';
                        }
                    })
                    .catch(err => {
                        span.textContent = 'TK —';
                    });
            });

            // Add to Cart: auto-pick earliest available date/slot for the first variant
            document.querySelectorAll('.btn-add-regular[data-variant-id]').forEach(bindAddToCart);

            // Function to update cart count
            function updateCartCount() {
                console.log('Updating cart count...');
                fetch('{{ route('frontend.cart.count') }}')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Cart count data:', data);
                        const cartCountElement = document.getElementById('cartCount');
                        if (cartCountElement) {
                            const newCount = data.cart_total_items || 0;
                            cartCountElement.textContent = newCount;
                            console.log('Cart count updated to:', newCount);
                        } else {
                            console.error('Cart count element not found');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating cart count:', error);
                    });
            }

            // Update cart count on page load
            updateCartCount();

            // Enhance the first (top) Add to Cart card to be functional with the first regular variant
            const dynamicFirstCard = document.querySelector('.custom-card[data-variant-id]');
            const topRows = document.querySelectorAll('.container .row.row-cols-1.row-cols-md-2.row-cols-lg-3.g-4');
            if (dynamicFirstCard && topRows.length > 0) {
                const firstVariantId = dynamicFirstCard.getAttribute('data-variant-id');
                const topFirstCard = topRows[0].querySelector('.custom-card');
                if (topFirstCard) {
                    const topBtn = topFirstCard.querySelector('a.btn.jatio-bg-color.w-100');
                    const topPrice = topFirstCard.querySelector('.price-text');
                    if (topBtn && firstVariantId) {
                        topBtn.setAttribute('data-variant-id', firstVariantId);
                        topBtn.classList.add('btn-add-regular');
                        bindAddToCart(topBtn);
                    }
                    // Populate price for the top card based on first variant
                    if (topPrice && firstVariantId) {
                        topPrice.textContent = 'Loading...';
                        fetchJson(`/api/pricing/date?date=${today}&variant_id=${firstVariantId}`)
                            .then(data => {
                                if (data && typeof data.final_price !== 'undefined') {
                                    topPrice.textContent = 'TK ' + Number(data.final_price).toLocaleString();
                                } else {
                                    topPrice.textContent = 'TK —';
                                }
                            })
                            .catch(() => {
                                topPrice.textContent = 'TK —';
                            });
                    }
                }
            }

            // Quantity selector plus/minus behavior on the top second card (visual only, keeps design intact)
            const qtyContainer = document.querySelector('.quantity-selector');
            if (qtyContainer) {
                const minusBtn = qtyContainer.querySelector('.quantity-btn.minus');
                const plusBtn = qtyContainer.querySelector('.quantity-btn.plus');
                const textEl = qtyContainer.querySelector('.quantity-text');
                const parseQty = () => {
                    const num = parseInt((textEl.textContent || '1').replace(/\D/g, ''));
                    return isNaN(num) ? 1 : num;
                };
                const setQty = (val) => {
                    textEl.textContent = `${val} Package${val > 1 ? 's' : ''}`;
                };
                if (minusBtn && plusBtn && textEl) {
                    minusBtn.addEventListener('click', function() {
                        const q = Math.max(1, parseQty() - 1);
                        setQty(q);
                    });
                    plusBtn.addEventListener('click', function() {
                        const q = parseQty() + 1;
                        setQty(q);
                    });
                }
            }
        });
    </script>
@endpush
