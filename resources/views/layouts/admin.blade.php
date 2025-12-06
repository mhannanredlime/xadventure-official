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
    <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/gallery.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/regular-package.css') }}">

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

        @include('inc.admin_sidebar')

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
    <script src="{{ asset('admin/js/multiple-image-upload.js') }}"></script>
    <script src="{{ asset('admin/js/gallery-manager.js') }}"></script>
    <script src="{{ asset('admin/js/files-uploader-init.js') }}"></script>
    <script src="{{ versioned_asset('admin/js/files-uploader-init.js') }}"></script>

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
