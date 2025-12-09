@extends('layouts.frontend')

@section('title', 'Customer Dashboard - ATV/UTV Adventures')

@section('content')
    <div class="container-fluid py-5"
        style="background: linear-gradient(135deg, #fff5f2 0%, #ffe8e0 100%); min-height: 100vh;">
        <div class="container">
            <!-- Header -->
            <div class="row mb-5">
                <div class="col-12">
                    <div
                        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                        <div class="flex-grow-1">
                            <h1 class="fw-bold jatio-color mb-2" style="font-size: 2.5rem;">Welcome back,
                                {{ $customer->name }}!</h1>
                            <p class="text-muted mb-0 fs-5">Manage your adventures and bookings</p>
                        </div>
                        <div class="d-flex gap-3 flex-shrink-0">
                            <a href="{{ route('customer.profile') }}" class="btn btn-outline-warning px-4 py-2">
                                <i class="bi bi-person-circle me-2"></i>Profile
                            </a>
                            <form action="{{ route('customer.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger px-4 py-2">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="row mb-5">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                        <div class="card-body text-center p-4">
                            <div class="d-inline-flex align-items-center justify-content-center jatio-bg-color bg-opacity-10 rounded-circle mb-3"
                                style="width: 70px; height: 70px;">
                                <i class="bi bi-calendar-check jatio-color" style="font-size: 1.8rem;"></i>
                            </div>
                            <h3 class="fw-bold jatio-color mb-2" style="font-size: 2.2rem;">{{ $totalReservations }}</h3>
                            <p class="text-muted mb-0 fs-6">Total Bookings</p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                        <div class="card-body text-center p-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle mb-3"
                                style="width: 70px; height: 70px;">
                                <i class="bi bi-check-circle text-success" style="font-size: 1.8rem;"></i>
                            </div>
                            <h3 class="fw-bold text-success mb-2" style="font-size: 2.2rem;">{{ $completedReservations }}
                            </h3>
                            <p class="text-muted mb-0 fs-6">Completed</p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                        <div class="card-body text-center p-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 rounded-circle mb-3"
                                style="width: 70px; height: 70px;">
                                <i class="bi bi-clock text-warning" style="font-size: 1.8rem;"></i>
                            </div>
                            <h3 class="fw-bold text-warning mb-2" style="font-size: 2.2rem;">{{ $pendingReservations }}</h3>
                            <p class="text-muted mb-0 fs-6">Pending</p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                        <div class="card-body text-center p-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 rounded-circle mb-3"
                                style="width: 70px; height: 70px;">
                                <i class="bi bi-calendar-event text-info" style="font-size: 1.8rem;"></i>
                            </div>
                            <h3 class="fw-bold text-info mb-2" style="font-size: 2.2rem;">{{ $confirmedReservations }}</h3>
                            <p class="text-muted mb-0 fs-6">Confirmed</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Upcoming Reservations -->
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                        <div class="card-header jatio-bg-color text-white"
                            style="border-radius: 15px 15px 0 0; border: none; padding: 1.25rem;">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-calendar-event me-2"></i>Upcoming Reservations
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @if ($groupedUpcomingReservations->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0 px-4 py-3">Package</th>
                                                <th class="border-0 px-4 py-3">Date</th>
                                                <th class="border-0 px-4 py-3">Time</th>
                                                <th class="border-0 px-4 py-3">Status</th>
                                                <th class="border-0 px-4 py-3">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($groupedUpcomingReservations as $group)
                                                @php
                                                    $primaryReservation = $group['primary_reservation'];
                                                    $reservations = $group['reservations'];
                                                    $isMultiPackage = $reservations->count() > 1;
                                                @endphp
                                                <tr class="{{ $isMultiPackage ? 'table-info' : '' }}">
                                                    <td class="px-4 py-3">
                                                        <div>
                                                            @if ($isMultiPackage)
                                                                <strong>Multi-Package Booking</strong>
                                                                <br>
                                                                <small class="text-muted">{{ $reservations->count() }}
                                                                    packages</small>
                                                            @else
                                                                <strong>{{ $primaryReservation->packageVariant->package->name ?? 'N/A' }}</strong>
                                                                <br>
                                                                <small
                                                                    class="text-muted">{{ $primaryReservation->packageVariant->variant_name ?? 'N/A' }}</small>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $primaryReservation->date ? \Carbon\Carbon::parse($primaryReservation->date)->format('M d, Y') : 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $primaryReservation->scheduleSlot->time_slot ?? 'N/A' }}</td>
                                                    <td class="px-4 py-3">
                                                        <span
                                                            class="badge bg-{{ $primaryReservation->booking_status === 'confirmed' ? 'success' : ($primaryReservation->booking_status === 'pending' ? 'warning' : 'secondary') }} px-3 py-2">
                                                            {{ ucfirst($primaryReservation->booking_status) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <a href="{{ route('customer.reservations.details', $primaryReservation->id) }}"
                                                            class="btn btn-sm btn-outline-warning px-3">
                                                            <i class="bi bi-eye me-1"></i>View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                                    <h5 class="text-muted mt-3 mb-2">No upcoming reservations</h5>
                                    <p class="text-muted mb-4">Start booking your next adventure!</p>
                                    <a href="{{ route('packages.atv-utv.list') }}"
                                        class="btn btn-orange jatio-bg-color px-4 py-2">
                                        <i class="bi bi-plus-circle me-2"></i>Book Now
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                        <div class="card-header bg-info text-white"
                            style="border-radius: 15px 15px 0 0; border: none; padding: 1.25rem;">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-lightning me-2"></i>Quick Actions
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-grid gap-3">
                                <button type="button" class="btn btn-orange jatio-bg-color py-3"
                                    onclick="openPackageSelection()">
                                    <i class="bi bi-plus-circle me-2"></i>Book New Adventure
                                </button>
                                <a href="{{ route('customer.reservations') }}" class="btn btn-outline-warning py-3">
                                    <i class="bi bi-calendar-check me-2"></i>View All Reservations
                                </a>
                                <a href="{{ route('customer.profile') }}" class="btn btn-outline-secondary py-3">
                                    <i class="bi bi-person-circle me-2"></i>Update Profile
                                </a>
                                <a href="{{ route('home') }}" class="btn btn-outline-info py-3">
                                    <i class="bi bi-house me-2"></i>Back to Home
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card border-0 shadow-sm mt-4" style="border-radius: 15px;">
                        <div class="card-header bg-success text-white"
                            style="border-radius: 15px 15px 0 0; border: none; padding: 1.25rem;">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-clock-history me-2"></i>Recent Activity
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            @if ($groupedRecentReservations->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach ($groupedRecentReservations->take(3) as $group)
                                        @php
                                            $primaryReservation = $group['primary_reservation'];
                                            $reservations = $group['reservations'];
                                            $isMultiPackage = $reservations->count() > 1;
                                        @endphp
                                        <div
                                            class="list-group-item border-0 px-0 py-3 {{ $isMultiPackage ? 'bg-light' : '' }}">
                                            <div class="d-flex w-100 justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    @if ($isMultiPackage)
                                                        <h6 class="mb-1 fw-semibold">Multi-Package Booking</h6>
                                                        <p class="mb-1 text-muted small">{{ $reservations->count() }}
                                                            packages</p>
                                                    @else
                                                        <h6 class="mb-1 fw-semibold">
                                                            {{ $primaryReservation->packageVariant->package->name ?? 'N/A' }}
                                                        </h6>
                                                        <p class="mb-1 text-muted small">
                                                            {{ $primaryReservation->packageVariant->variant_name ?? 'N/A' }}
                                                        </p>
                                                    @endif
                                                    <small class="text-muted">Status:
                                                        {{ ucfirst($primaryReservation->booking_status) }}</small>
                                                </div>
                                                <small
                                                    class="text-muted ms-3">{{ \Carbon\Carbon::parse($primaryReservation->created_at)->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center mb-0">No recent activity</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
        }

        .btn {
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-outline-warning {
            border-color: #ffc107;
            color: #ffc107;
        }

        .btn-outline-warning:hover {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }

        .btn-outline-danger {
            border-color: #dc3545;
            color: #dc3545;
        }

        .btn-outline-danger:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #fff;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
        }

        .table td {
            vertical-align: middle;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.5em 0.75em;
            font-weight: 500;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
        }

        @media (max-width: 768px) {
            .container-fluid {
                padding-top: 2rem !important;
                padding-bottom: 2rem !important;
            }

            h1 {
                font-size: 2rem !important;
            }

            .btn {
                padding: 0.75rem 1rem;
            }

            /* Enhanced mobile optimizations */
            .card-body {
                padding: 1.5rem !important;
            }

            .statistics-card .card-body {
                padding: 1rem !important;
            }

            .statistics-card .d-inline-flex {
                width: 60px !important;
                height: 60px !important;
            }

            .statistics-card i {
                font-size: 1.5rem !important;
            }

            .statistics-card h3 {
                font-size: 1.8rem !important;
            }

            .table-responsive {
                font-size: 0.875rem;
            }

            .table th,
            .table td {
                padding: 0.75rem 0.5rem !important;
            }

            .badge {
                font-size: 0.7rem;
                padding: 0.4em 0.6em;
            }

            .list-group-item {
                padding: 1rem 0.75rem;
            }
        }

        /* Tablet optimizations */
        @media (max-width: 991px) and (min-width: 769px) {
            .container-fluid {
                padding-top: 3rem !important;
                padding-bottom: 3rem !important;
            }

            h1 {
                font-size: 2.2rem !important;
            }

            .statistics-card .card-body {
                padding: 1.25rem !important;
            }

            .statistics-card .d-inline-flex {
                width: 65px !important;
                height: 65px !important;
            }

            .statistics-card i {
                font-size: 1.6rem !important;
            }

            .statistics-card h3 {
                font-size: 2rem !important;
            }
        }

        /* Small mobile optimizations */
        @media (max-width: 575px) {
            .container-fluid {
                padding-top: 1.5rem !important;
                padding-bottom: 1.5rem !important;
            }

            h1 {
                font-size: 1.8rem !important;
            }

            .card-body {
                padding: 1rem !important;
            }

            .statistics-card .card-body {
                padding: 0.75rem !important;
            }

            .statistics-card .d-inline-flex {
                width: 50px !important;
                height: 50px !important;
            }

            .statistics-card i {
                font-size: 1.2rem !important;
            }

            .statistics-card h3 {
                font-size: 1.5rem !important;
            }

            .statistics-card p {
                font-size: 0.75rem !important;
            }

            .table-responsive {
                font-size: 0.8rem;
            }

            .table th,
            .table td {
                padding: 0.5rem 0.25rem !important;
            }

            .btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }

            .badge {
                font-size: 0.65rem;
                padding: 0.3em 0.5em;
            }

            .list-group-item {
                padding: 0.75rem 0.5rem;
            }

            .list-group-item h6 {
                font-size: 0.875rem;
            }

            .list-group-item p,
            .list-group-item small {
                font-size: 0.75rem;
            }
        }

        /* Large screen optimizations */
        @media (min-width: 1200px) {
            .container-fluid {
                padding-top: 4rem !important;
                padding-bottom: 4rem !important;
            }

            h1 {
                font-size: 2.8rem !important;
            }

            .statistics-card .d-inline-flex {
                width: 80px !important;
                height: 80px !important;
            }

            .statistics-card i {
                font-size: 2rem !important;
            }

            .statistics-card h3 {
                font-size: 2.5rem !important;
            }
        }

        /* Landscape mobile optimizations */
        @media (max-width: 768px) and (orientation: landscape) {
            .container-fluid {
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }

            h1 {
                font-size: 1.5rem !important;
            }

            .statistics-card .card-body {
                padding: 0.75rem !important;
            }

            .statistics-card .d-inline-flex {
                width: 45px !important;
                height: 45px !important;
            }

            .statistics-card i {
                font-size: 1.1rem !important;
            }

            .statistics-card h3 {
                font-size: 1.3rem !important;
            }
        }

        /* Print styles */
        @media print {

            .btn,
            .form,
            .alert {
                display: none !important;
            }

            .card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
            }

            .container-fluid {
                background: white !important;
            }
        }
    </style>
@endpush
