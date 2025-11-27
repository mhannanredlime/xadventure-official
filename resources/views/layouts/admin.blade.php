<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin')</title>

    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Bootstrap & Custom CSS -->
    <link rel="stylesheet" href="{{ versioned_asset('admin/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('admin/css/custom-admin.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <base href="{{ asset('admin/') }}/">
    @stack('styles')
    <style>
        /* ================= Sidebar ================= */
        .sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background-color: #f8f9fa;
            border-right: 1px solid #ddd;
            z-index: 1020;
            display: flex;
            flex-direction: column;
        }



        /* Sidebar menu scrollable */
        .sidebar-content {
            flex: 1 1 auto;
            overflow-y: auto;
            padding-top: 10px;
        }

        .sidebar .nav-link {
            color: #333;
            padding: 10px 20px;
            transition: 0.2s;
        }



        .sidebar .dropdown-menu {
            position: static;
            float: none;
            min-width: 100%;
            background-color: #f8f9fa;
            border: none;
            box-shadow: none;
        }

        .sidebar .dropdown-item {
            padding-left: 40px;
        }

        /* ================= Top Navbar ================= */
        .navbar {
            position: fixed;
            top: 0;
            left: 250px;
            /* sidebar width */
            right: 0;
            z-index: 1030;
            height: 56px;
        }

        /* ================= Content Area ================= */
        .content-area {
            margin-left: 250px;
            padding: 20px;
        }

        /* User toggle */
        .user-toggle {
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .user-toggle:hover {
            transform: translateY(-1px);
        }

        /* Dropdown fade + scale */
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
    <div class="main-wrapper d-flex">

        {{-- ================= Sidebar ================= --}}
        <aside class="sidebar">
            <div class="sidebar-logo">
                <a href="{{ url('/admin/reservation-dashboard') }}">
                    <img src="{{ asset('admin/images/admin-logo.svg') }}" alt="XAdventure">
                </a>
            </div>
            <div class="sidebar-content">
                <ul class="nav flex-column sidebar-menu">
                    {{-- Fix dropdown issue i want to open dropdown on click smoothly  --}}
                    <li class="nav-item">
                        <a href="#dashboardSubmenu" class="nav-link d-flex justify-content-between align-items-center"
                            data-bs-toggle="collapse" aria-expanded="false">
                            <div>
                                <i class="bi bi-clipboard-check"></i>
                                <span>Dashboard</span>
                            </div>
                            <i class="bi bi-chevron-down"></i>
                        </a>
                        <ul class="collapse list-unstyled ps-3" id="dashboardSubmenu">
                            <li class="nav-item">
                                <a href="../../index.html" class="nav-link">
                                    <i class="bi bi-circle me-2"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Dashboard --}}
                    @can('dashboard.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/reservation-dashboard') ? 'active' : '' }}"
                                href="{{ url('/admin/reservation-dashboard') }}">
                                <i class="bi bi-clipboard-check"></i>
                                <span class="menu-text">Reservation Dashboard</span>
                            </a>
                        </li>
                    @endcan
                    {{-- Reservations --}}
                    @can('reservations.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/view-reservation-dashboard') ? 'active' : '' }}"
                                href="{{ url('/admin/view-reservation-dashboard') }}">
                                <i class="bi bi-clock-history"></i>
                                <span class="menu-text">Reservation History</span>
                            </a>
                        </li>
                    @endcan
                    {{-- Contacts --}}
                    @can('contacts.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/customer/contacts') ? 'active' : '' }}"
                                href="{{ url('/admin/customer/contacts') }}">
                                <i class="bi bi-clock-history"></i>
                                <span class="menu-text">Contact Messages</span>
                            </a>
                        </li>
                    @endcan
                    {{-- Calendar --}}
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
                    {{-- Package Management --}}
                    @can('packages.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/add-packege-management*') || request()->is('admin/packages*') ? 'active' : '' }}"
                                href="{{ url('/admin/add-packege-management') }}">
                                <i class="bi bi-box-seam"></i>
                                <span class="menu-text">Package Management</span>
                            </a>
                        </li>
                    @endcan
                    {{-- Vehicle Management --}}
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
                    {{-- Media & Gallery --}}
                    @can('gallery.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/gallery*') ? 'active' : '' }}"
                                href="{{ url('/admin/gallery') }}">
                                <i class="bi bi-images"></i>
                                <span class="menu-text">Image Gallery</span>
                            </a>
                        </li>
                    @endcan
                    {{-- Promo Codes --}}
                    @can('promo-codes.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/promo*') || request()->is('admin/promo-codes*') ? 'active' : '' }}"
                                href="{{ url('/admin/promo') }}">
                                <i class="bi bi-box-seam"></i>
                                <span class="menu-text">Promo Code Setup</span>
                            </a>
                        </li>
                    @endcan
                    {{-- Analytics --}}
                    @can('analytics.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}"
                                href="{{ url('/admin/reports') }}">
                                <i class="bi bi-graph-up"></i>
                                <span class="menu-text">Reports & Analytics</span>
                            </a>
                        </li>
                    @endcan
                    {{-- User & Role Management --}}
                    @can('users.view')
                        <li class="nav-item">
                            <a href="#userManagementSubmenu"
                                class="nav-link d-flex justify-content-between align-items-center {{ request()->is('admin/users*') ? 'active' : '' }}"
                                data-bs-toggle="collapse" aria-expanded="false">
                                <div>
                                    <i class="bi bi-people me-2"></i>
                                    <span>User Management</span>
                                </div>
                                <i class="bi bi-chevron-down"></i>
                            </a>
                            <ul class="collapse list-unstyled ps-3" id="userManagementSubmenu">
                                <li class="nav-item">
                                    <a class="nav-link {{ request('user_type') == 'admin' ? 'active' : '' }}"
                                        href="{{ url('/admin/users?user_type=admin') }}">
                                        <i class="bi bi-person-check me-2"></i>Admins
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request('user_type') == 'customer' ? 'active' : '' }}"
                                        href="{{ url('/admin/users?user_type=customer') }}">
                                        <i class="bi bi-person me-2"></i>Customers
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request('user_type') == 'all' ? 'active' : '' }}"
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
                    {{-- System Settings --}}
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
        </aside>

        {{-- ================= Top Navbar ================= --}}
        <nav class="navbar navbar-expand-lg bg-white shadow-sm fixed-top">
            <div class="container-fluid">
                {{-- Sidebar toggle for smaller screens --}}
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                                class="bi bi-list"></i></a>
                    </li>

                </ul>
                {{-- Right User Menu --}}
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center px-3 py-2 rounded-3 user-toggle"
                            href="#" id="userMenuDropdownTop" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-person-circle me-2 fs-3"></i>
                            <span class="fw-semibold">{{ Auth::user()->name ?? 'User' }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2"
                            aria-labelledby="userMenuDropdownTop">
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
        </nav>

        {{-- ================= Content Area ================= --}}
        <main class="content-area">
            @yield('content')
        </main>

        {{-- ================= Logout Form ================= --}}
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

    </div> {{-- End main-wrapper --}}

    <!-- JS Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ versioned_asset('admin/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ versioned_asset('admin/js/toast-notifications.js') }}"></script>
    <script src="{{ versioned_asset('admin/js/modal-system.js') }}"></script>

    <script>
        // Bootstrap Icons fallback
        document.addEventListener('DOMContentLoaded', function() {
            const testIcon = document.createElement('i');
            testIcon.className = 'bi bi-check';
            testIcon.style.position = 'absolute';
            testIcon.style.left = '-9999px';
            document.body.appendChild(testIcon);

            const content = window.getComputedStyle(testIcon, ':before').getPropertyValue('content');
            if (content === 'none' || content === '') document.body.classList.add('bi-fallback');
            document.body.removeChild(testIcon);
        });
    </script>

    @stack('scripts')
    {!! ToastMagic::scripts() !!}
</body>

</html>
