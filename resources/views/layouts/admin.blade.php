<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ versioned_asset('admin/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('admin/css/custom-admin.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <base href="{{ asset('admin/') }}/">
    @stack('styles')
    <style>
        /* Ensure dropdown menus work properly in sidebar */
        .sidebar .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            min-width: 200px;
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, .15);
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .175);
        }

        .sidebar .dropdown-item {
            padding: 0.5rem 1rem;
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #000;
        }

        .sidebar .dropdown-item.active {
            background-color: #0d6efd;
            color: #fff;
        }

        .sidebar .dropdown-divider {
            margin: 0.5rem 0;
            border-top: 1px solid #dee2e6;
        }
    </style>

    <style>
        /* Smooth transition on toggle */
        .user-toggle {
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .user-toggle:hover {
            transform: translateY(-1px);
        }

        /* Dropdown menu fade + scale */
        .dropdown-menu {
            opacity: 0;
            transform: scale(0.95);
            transition: opacity 0.15s ease-out, transform 0.15s ease-out;
        }

        .dropdown-menu.show {
            opacity: 1;
            transform: scale(1);
        }
    </style>
    {!! ToastMagic::styles() !!}

</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarCollapse"
                aria-controls="sidebarCollapse" aria-expanded="false" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand" href="{{ url('/admin/reservation-dashboard') }}">
                <img src="{{ asset('admin/images/admin-logo.svg') }}" class="navbar-logo" alt="Admin Logo">
            </a>

            <div class="collapse navbar-collapse justify-content-end">




                <ul class="navbar-nav align-items-center">
                    <li class="nav-item dropdown">

                        <!-- User Toggle -->
                        <a class="nav-link dropdown-toggle d-flex align-items-center px-3 py-2 rounded-3 user-toggle"
                            href="#" id="userMenuDropdown" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">

                            <!-- Larger Avatar Icon -->
                            <i class="bi bi-person-circle me-2 fs-3"></i>
                            <span class="fw-semibold">{{ Auth::user()->name ?? 'User' }}</span>
                        </a>

                        <!-- Dropdown Menu -->
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-3"
                            aria-labelledby="userMenuDropdown">

                            <li>
                                <a class="dropdown-item d-flex align-items-center"
                                    href="{{ route('admin.profile.index') }}">
                                    <i class="bi bi-person me-2 fs-5"></i> Profile
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item d-flex align-items-center"
                                    href="{{ route('admin.settings.index') }}">
                                    <i class="bi bi-gear me-2 fs-5"></i> Settings
                                </a>
                            </li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            <li>
                                <a class="dropdown-item d-flex align-items-center text-danger"
                                    href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right me-2 fs-5"></i> Logout
                                </a>
                            </li>

                        </ul>
                    </li>
                </ul>

            </div>
        </div>
    </nav>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <div class="main-wrapper">
        <div class="sidebar collapse collapse-horizontal d-lg-block" id="sidebarCollapse">
            <div class="sidebar-content">
                <ul class="nav flex-column sidebar-menu">
                    <!-- Core Operations -->
                    @can('dashboard.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/reservation-dashboard') ? 'active' : '' }}"
                                href="{{ url('/admin/reservation-dashboard') }}">
                                <i class="bi bi-clipboard-check"></i>
                                <span class="menu-text">Reservation Dashboard</span>
                            </a>
                        </li>
                    @endcan
                    @can('reservations.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/view-reservation-dashboard') ? 'active' : '' }}"
                                href="{{ url('/admin/view-reservation-dashboard') }}">
                                <i class="bi bi-clock-history"></i>
                                <span class="menu-text">Reservation History</span>
                            </a>
                        </li>
                    @endcan


                    @can('contacts.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/customer/contacts') ? 'active' : '' }}"
                                href="{{ url('/admin/customer/contacts') }}">
                                <i class="bi bi-clock-history"></i>
                                <span class="menu-text">Contact Messages</span>
                            </a>
                        </li>
                    @endcan

                    <!-- Calendar & Availability -->
                    @can('calendar.manage')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/calendar*') ? 'active' : '' }}"
                                href="{{ url('/admin/calendar') }}">
                                <i class="bi bi-calendar-event"></i>
                                <span class="menu-text">Availability & Pricing Setup</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/slot-presets*') ? 'active' : '' }}"
                                href="{{ url('/admin/slot-presets') }}">
                                <i class="bi bi-list-check"></i>
                                <span class="menu-text">Time Slot Presets</span>
                            </a>
                        </li>
                    @endcan

                    <!-- Package Management -->
                    @can('packages.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/add-packege-management*') || request()->is('admin/packages*') ? 'active' : '' }}"
                                href="{{ url('/admin/add-packege-management') }}">
                                <i class="bi bi-box-seam"></i>
                                <span class="menu-text">Package Management</span>
                            </a>
                        </li>
                    @endcan

                    <!-- Vehicle Management -->
                    @can('vehicle-types.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/vehical-setup*') || request()->is('admin/vehicle-types*') ? 'active' : '' }}"
                                href="{{ url('/admin/vehical-setup') }}">
                                <i class="bi bi-truck"></i>
                                <span class="menu-text">Vehicle Type Setup</span>
                            </a>
                        </li>
                    @endcan
                    @can('vehicles.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/vehical-management*') || request()->is('admin/vehicles*') ? 'active' : '' }}"
                                href="{{ url('/admin/vehical-management') }}">
                                <i class="bi bi-gear"></i>
                                <span class="menu-text">Vehicle Management</span>
                            </a>
                        </li>
                    @endcan

                    <!-- Media & Content -->
                    @can('gallery.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/gallery*') ? 'active' : '' }}"
                                href="{{ url('/admin/gallery') }}">
                                <i class="bi bi-images"></i>
                                <span class="menu-text">Image Gallery</span>
                            </a>
                        </li>
                    @endcan

                    <!-- Pricing & Promotions -->
                    @can('promo-codes.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/promo*') || request()->is('admin/promo-codes*') ? 'active' : '' }}"
                                href="{{ url('/admin/promo') }}">
                                <i class="bi bi-box-seam"></i>
                                <span class="menu-text">Promo Code Setup</span>
                            </a>
                        </li>
                    @endcan

                    <!-- Analytics & Reports -->
                    @can('analytics.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}"
                                href="{{ url('/admin/reports') }}">
                                <i class="bi bi-graph-up"></i>
                                <span class="menu-text">Reports & Analytics</span>
                            </a>
                        </li>
                    @endcan

                    <!-- User & Role Management -->
                    @can('users.view')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->is('admin/users*') ? 'active' : '' }}"
                                href="#" id="userManagementDropdown" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-people"></i>
                                <span class="menu-text">User Management</span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userManagementDropdown">
                                <li>
                                    <a class="dropdown-item {{ request()->is('admin/users') && (request('user_type') == 'admin' || !request('user_type')) ? 'active' : '' }}"
                                        href="{{ url('/admin/users?user_type=admin') }}">
                                        <i class="bi bi-person-check me-2"></i>Admins
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->is('admin/users') && request('user_type') == 'customer' ? 'active' : '' }}"
                                        href="{{ url('/admin/users?user_type=customer') }}">
                                        <i class="bi bi-person me-2"></i>Customers
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->is('admin/users') && request('user_type') == 'all' ? 'active' : '' }}"
                                        href="{{ url('/admin/users?user_type=all') }}">
                                        <i class="bi bi-people me-2"></i>All Users
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan
                    @can('roles.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/roles*') ? 'active' : '' }}"
                                href="{{ url('/admin/roles') }}">
                                <i class="bi bi-shield-check"></i>
                                <span class="menu-text">Role & Permission Management</span>
                            </a>
                        </li>
                    @endcan

                    <!-- Settings -->
                    @can('settings.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}"
                                href="{{ url('/admin/settings') }}">
                                <i class="bi bi-gear-fill"></i>
                                <span class="menu-text">System Settings</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        </div>

        <div class="content-area flex-grow-1">
            @yield('content')
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ versioned_asset('admin/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ versioned_asset('admin/js/toast-notifications.js') }}"></script>
    <script src="{{ versioned_asset('admin/js/modal-system.js') }}"></script>
    <script>
        // Icon fallback mechanism
        document.addEventListener('DOMContentLoaded', function() {
            // Check if Bootstrap Icons are loaded
            const testIcon = document.createElement('i');
            testIcon.className = 'bi bi-check';
            testIcon.style.position = 'absolute';
            testIcon.style.left = '-9999px';
            document.body.appendChild(testIcon);

            const computedStyle = window.getComputedStyle(testIcon, ':before');
            const content = computedStyle.getPropertyValue('content');

            if (content === 'none' || content === '') {
                // Bootstrap Icons not loaded, using fallback
                // Add fallback class to body
                document.body.classList.add('bi-fallback');
            }

            document.body.removeChild(testIcon);
        });
    </script>
    @stack('scripts')
    {!! ToastMagic::scripts() !!}
</body>

</html>
