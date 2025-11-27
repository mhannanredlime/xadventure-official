<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Xtreme Adventure')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ versioned_asset('frontEnd/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('frontEnd/css/csutom.css') }}">
    <base href="{{ asset('frontEnd/') }}/">

    <style>
        /* Ensure dropdown menus are visible */
        .dropdown-menu {
            display: none;
            position: absolute;
            z-index: 1001 !important;
            /* Higher than navbar z-index: 1000 */
            background-color: white;
            border: 1px solid rgba(0, 0, 0, .15);
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .175);
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: block;
            width: 100%;
            padding: 0.375rem 1rem;
            clear: both;
            font-weight: 400;
            color: #212529;
            text-align: inherit;
            text-decoration: none;
            white-space: nowrap;
            background-color: transparent;
            border: 0;
        }

        .dropdown-item:hover {
            color: #1e2125;
            background-color: #e9ecef;
        }

        /* Ensure navbar dropdowns work on mobile */
        @media (max-width: 991.98px) {
            .dropdown-menu {
                position: static;
                float: none;
                width: auto;
                margin-top: 0;
                background-color: transparent;
                border: 0;
                box-shadow: none;
                z-index: auto;
            }
        }

        /* Override any conflicting styles */
        .navbar .dropdown-menu {
            z-index: 1001 !important;
        }

        /* Package Selection Modal Styles */
        .package-option-card {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .package-option-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .package-image {
            margin-bottom: 15px;
        }

        .package-image img {
            max-height: 80px;
            width: auto;
        }

        .package-content {
            color: white;
        }

        .package-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: white;
        }

        .package-subtitle {
            font-size: 0.9rem;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.8);
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 25px 25px 0 25px;
        }

        .modal-title {
            font-size: 1.5rem;
            color: #333;
        }

        .modal-body {
            padding: 25px;
        }

        .btn-close {
            background-size: 1.2em;
        }
    </style>

    @stack('styles')
</head>

<body>



    <!-- Package Selection Modal -->
    <div class="modal fade" id="packageSelectionModal" tabindex="-1" aria-labelledby="packageSelectionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="packageSelectionModalLabel">Choose Package Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- ATV/UTV Trail Rides Option -->
                        <div class="col-md-6">
                            <div class="package-option-card" onclick="selectPackage('atv-utv')">
                                <div class="package-image">
                                    <img src="{{ asset('frontEnd/images/advan-slider-2.svg') }}"
                                        alt="ATV/UTV Trail Rides" class="img-fluid">
                                </div>
                                <div class="package-content">
                                    <h6 class="package-title">ATV/UTV</h6>
                                    <p class="package-subtitle">Trail Rides</p>
                                    <button class="btn btn-orange btn-sm">Book Now</button>
                                </div>
                            </div>
                        </div>

                        <!-- Build Your Own Adventure Option -->
                        <div class="col-md-6">
                            <div class="package-option-card" onclick="selectPackage('regular')">
                                <div class="package-image">
                                    <img src="{{ versioned_asset('frontEnd/images/climbing.svg') }}"
                                        alt="Build Your Own Adventure" class="img-fluid">
                                </div>
                                <div class="package-content">
                                    <h6 class="package-title">Build your own</h6>
                                    <p class="package-subtitle">Adventure</p>
                                    <button class="btn btn-orange btn-sm">Book Now</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark ">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="{{ url('/') }}">
                <img src="{{ versioned_asset('frontEnd/images/logo.webp') }}" alt="Logo" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/about') }}">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/adventure') }}">Adventure</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link " href="/#" id="packagesDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">Package</a>
                        <ul class="dropdown-menu" aria-labelledby="packagesDropdown">
                            <li><a class="dropdown-item" href="{{ route('custom-packages') }}">Regular Package</a></li>
                            <li><a class="dropdown-item" href="{{ route('frontend.atv-utv') }}">ATV-UTV Package</a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
                </ul>
                <div class="d-flex align-items-center gap-1 gap-md-3">
                    <a href="{{ url('/shopping-cart') }}"
                        style="color: white; text-decoration: none; position: relative;">
                        <i class="fa-solid fa-cart-arrow-down"></i>
                        @if ($cartCount > 0)
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                style="font-size: 0.6rem; transform: translate(-50%, -50%);">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>

                    @auth('customer')
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" id="accountDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi  bi-user"></i> Account
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="accountDropdown">
                                <li><a class="dropdown-item" href="{{ route('customer.dashboard') }}"><i
                                            class="bi  bi-tachometer-alt"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('customer.profile') }}"><i
                                            class="bi  bi-user-edit"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('customer.reservations') }}"><i
                                            class="bi  bi-calendar-check"></i> Reservations</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('customer.logout') }}"
                                        style="display: inline;">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i class="bi  bi-sign-out-alt"></i>
                                            Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @elseif(Auth::check() && Auth::user()->is_admin)
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" id="accountDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi  bi-user-shield"></i> {{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="accountDropdown">
                                <li><a class="dropdown-item" href="{{ route('admin.reservation-dashboard') }}"><i
                                            class="bi  bi-clipboard-check"></i> Admin Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.profile.index') }}"><i
                                            class="bi  bi-user-edit"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}"><i
                                            class="bi  bi-cog"></i> Settings</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i class="bi  bi-sign-out-alt"></i>
                                            Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" id="accountDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi  bi-user"></i> Account
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="accountDropdown">
                                <li><a class="dropdown-item" href="{{ route('customer.login') }}"><i
                                            class="bi  bi-sign-in-alt"></i> Login</a></li>
                                <li><a class="dropdown-item" href="{{ route('customer.register') }}"><i
                                            class="bi  bi-user-plus"></i> Register</a></li>
                            </ul>
                        </div>
                    @endauth

                    <button type="button" class="btn btn-orange jatio-bg-color" id="bookNowBtn">Book Now</button>
                </div>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="footer bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 text-start">
                    <h2 class="footer-brand">
                        <span class="text-white fw-bold">ADVEN</span><span class="text-orange">TURE</span>
                    </h2>
                    <p class="text-white-50">Xtreme Adventure Bandarban</p>
                    <div class=" mt-3">
                        <p class="d-flex align-items-start mb-2">
                            <i class="bi bi-map-marker-alt text-orange  mt-1"></i>
                            <span>Babunagarpara, Ward No. 3,<br>Tongkaboti, Bandarban</span>
                        </p>
                        <p class="d-flex align-items-center mb-2">
                            <i class="bi bi-envelope text-orange"></i>
                            <span>info@xadventurebandarban.com</span>
                        </p>
                        <p class="d-flex align-items-center mb-2">
                            <i class="bi bi-phone-alt text-orange"></i>
                            <span>01893583010, 01893585377</span>
                        </p>
                    </div>
                </div>
                <div class="col-md-2 mb-4">
                    <h5 class="text-white mb-3">Quick Links</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="{{ url('/about') }}">About Us</a></li>
                        <li><a href="{{ url('/adventure') }}">Services</a></li>
                        <li><a href="#">Our Team</a></li>
                        <li><a href="{{ route('contact') }}">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5 class="text-white mb-3">Useful Links</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="{{ route('frontend.privacy-policy') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('frontend.terms-conditions') }}">Terms and Conditions</a></li>
                        <li><a href="{{ route('frontend.faq') }}">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5 class="text-white mb-3">Work Hours</h5>
                    <p class="d-flex align-items-center mb-2">
                        <i class="bi bi-clock text-warning me-2"></i> 9 AM - 5 PM
                    </p>
                    <p class="text-white-50">Experience the serenity of the Hill Tracts. Your gateway to adventure and
                        relaxation in the heart of Bandarban.</p>
                    <div class="social-icons-footer mt-3 d-flex gap-3">
                        <a href="https://www.facebook.com/xadventure" target="_blank" class="text-white"><i
                                class="fab fa-facebook-f fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="copyright d-flex justify-content-between flex-wrap text-white-50 small">
                <span>Designed by Mrtect IT Solution</span>
                <span>Copyright &copy; 2025. All rights reserved.</span>
            </div>
        </div>
    </footer>

    <style>
        .footer {
            background-color: #212529;
            /* Dark background */
            color: #f8f9fa;
            /* Light text */
        }

        .footer-brand .text-orange {
            color: #fd7e14 !important;
            /* Bootstrap orange color */
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.75);
            /* Lighter white for links */
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #fd7e14;
            /* Orange on hover */
        }

        .contact-info i {
            color: #fd7e14;
            /* Orange for contact icons */
        }

        .social-icons-footer a {
            color: #f8f9fa;
            /* White for social icons */
            transition: color 0.3s ease;
        }

        .social-icons-footer a:hover {
            color: #fd7e14;
            /* Orange on hover */
        }

        .border-secondary {
            border-color: rgba(255, 255, 255, 0.1) !important;
            /* Lighter border for HR */
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ versioned_asset('frontEnd/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ versioned_asset('frontEnd/js/custom.js') }}"></script>f
    <script src="{{ versioned_asset('admin/js/toast-notifications.js') }}"></script>
    <script src="{{ versioned_asset('admin/js/modal-system.js') }}"></script>

    <script>
        // Ensure dropdowns work properly
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all dropdowns
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });

            // Add click event for Package dropdown specifically
            var packagesDropdown = document.getElementById('packagesDropdown');
            if (packagesDropdown) {
                packagesDropdown.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Package dropdown clicked');
                    var dropdownMenu = this.nextElementSibling;
                    if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                        dropdownMenu.classList.toggle('show');
                        console.log('Dropdown menu toggled, show class:', dropdownMenu.classList.contains(
                            'show'));
                        console.log('Dropdown menu display:', window.getComputedStyle(dropdownMenu)
                            .display);
                        console.log('Dropdown menu z-index:', window.getComputedStyle(dropdownMenu).zIndex);
                    }
                });

                // Test dropdown functionality
                console.log('Package dropdown found and initialized');
            } else {
                console.log('Package dropdown not found');
            }

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    var dropdowns = document.querySelectorAll('.dropdown-menu.show');
                    dropdowns.forEach(function(dropdown) {
                        dropdown.classList.remove('show');
                    });
                }
            });

            // Test all dropdowns
            console.log('Total dropdowns found:', dropdownElementList.length);

            // Add hover event for better UX
            var dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(function(dropdown) {
                dropdown.addEventListener('mouseenter', function() {
                    var menu = this.querySelector('.dropdown-menu');
                    if (menu) {
                        menu.classList.add('show');
                    }
                });

                dropdown.addEventListener('mouseleave', function() {
                    var menu = this.querySelector('.dropdown-menu');
                    if (menu) {
                        menu.classList.remove('show');
                    }
                });
            });
        });

        // Global function to update cart count
        function updateCartCount() {
            fetch('{{ route('frontend.cart.count') }}')
                .then(response => response.json())
                .then(data => {
                    // Update navigation cart badge
                    const cartBadge = document.querySelector('.fa-cart-arrow-down + .badge');
                    if (data.cart_count > 0) {
                        if (cartBadge) {
                            cartBadge.textContent = data.cart_count;
                        } else {
                            const cartLink = document.querySelector('a[href*="shopping-cart"]');
                            if (cartLink) {
                                const badge = document.createElement('span');
                                badge.className =
                                    'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                                badge.style.cssText = 'font-size: 0.6rem; transform: translate(-50%, -50%);';
                                badge.textContent = data.cart_count;
                                cartLink.appendChild(badge);
                            }
                        }
                    } else {
                        if (cartBadge) {
                            cartBadge.remove();
                        }
                    }

                    // Update floating cart if it exists
                    const floatingCartCount = document.querySelector('.floating-cart .item-count');
                    if (floatingCartCount) {
                        floatingCartCount.textContent = data.cart_count;
                    }

                    const floatingCartText = document.querySelector('.floating-cart .item-text');
                    if (floatingCartText) {
                        floatingCartText.textContent = data.cart_count == 1 ? 'Item' : 'Items';
                    }
                })
                .catch(error => {
                    // Error updating cart count
                });
        }

        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });

        // Make updateCartCount available globally
        window.updateCartCount = updateCartCount;

        // Package Selection Modal Functions
        function openPackageSelection() {
            console.log('openPackageSelection function called');

            const modalElement = document.getElementById('packageSelectionModal');
            if (modalElement) {
                console.log('Modal element found');
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
                console.log('Modal should be showing now');
            } else {
                console.error('Modal element not found');
            }
        }

        function selectPackage(packageType) {
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('packageSelectionModal'));
            modal.hide();

            // Navigate to the appropriate page based on package type
            if (packageType === 'atv-utv') {
                window.location.href = '{{ route('frontend.atv-utv') }}';
            } else if (packageType === 'regular') {
                window.location.href = '{{ route('custom-packages') }}';
            }
        }

        // Close modal when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('packageSelectionModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        bsModal.hide();
                    }
                });
            }

            // Add event listener for Book Now button
            const bookNowBtn = document.getElementById('bookNowBtn');
            if (bookNowBtn) {
                bookNowBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Book Now button clicked');
                    openPackageSelection();
                });
            } else {
                console.error('Book Now button not found');
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const playBtn = document.getElementById("playVideoBtn");
            const videoModalEl = document.getElementById("videoModal");
            const youtubeVideo = document.getElementById("youtubeVideo");

            if (!playBtn || !videoModalEl || !youtubeVideo) {
                console.error("❌ Video modal elements missing.");
                return;
            }

            // YouTube video ID
            // const videoID = "cZA-b2yUVIg";
            const videoID = "dqWIaDawJRM";
            // Always get or create instance safely
            let modalInstance = null;

            // When Play button clicked
            playBtn.addEventListener("click", function(e) {
                e.preventDefault();

                // Re-initialize modal safely every time
                modalInstance = bootstrap.Modal.getOrCreateInstance(videoModalEl);
                youtubeVideo.src =
                    `https://www.youtube.com/embed/${videoID}?autoplay=1&rel=0&modestbranding=1`;
                modalInstance.show();
            });

            // Close button click event (handled directly through Bootstrap)
            videoModalEl.addEventListener("click", function(e) {
                if (e.target.classList.contains("btn-close")) {
                    const instance = bootstrap.Modal.getInstance(videoModalEl);
                    if (instance) instance.hide();
                }
            });

            // Stop video after modal hides
            videoModalEl.addEventListener("hidden.bs.modal", function() {
                youtubeVideo.src = "";
            });
        });
    </script>


    @stack('scripts')

</body>


</html>
