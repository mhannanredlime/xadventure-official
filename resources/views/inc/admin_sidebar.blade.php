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
                    <a href="#sampleSubmenu"
                        class="nav-link d-flex justify-content-between align-items-center   {{ request()->is('admin/packages*') ? 'active' : '' }}"
                        data-bs-toggle="collapse" aria-expanded="false">
                        <div>
                            <i class="bi bi-menu-button-wide me-2"></i>
                            <span>Package Management</span>
                        </div>
                        <i class="bi bi-chevron-down"></i>
                    </a>
                    <ul class="collapse list-unstyled ps-3" id="sampleSubmenu">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.packege.list') }}">
                                <i class="bi bi-circle me-2"></i>All Packages
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/admin/regular-packege-management') }}">
                                <i class="bi bi-circle me-2"></i>Regular Package
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-circle me-2"></i>Add ATV/UTV Package
                            </a>
                        </li>
                    </ul>
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
