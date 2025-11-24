@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Admin Dashboard</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Reservations</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\Reservation::count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Packages</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\Package::where('is_active', true)->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Customers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\Customer::count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Payments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\Reservation::where('payment_status', 'pending')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.reservations.index') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-calendar"></i> Manage Reservations
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.packages.index') }}" class="btn btn-success btn-block">
                                <i class="fas fa-box"></i> Manage Packages
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.availabilities.index') }}" class="btn btn-info btn-block">
                                <i class="fas fa-clock"></i> Manage Availability
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-warning btn-block">
                                <i class="fas fa-tag"></i> Manage Promo Codes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Reservations</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Booking Code</th>
                                    <th>Customer</th>
                                    <th>Package</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\Reservation::with(['customer', 'packageVariant.package'])->latest()->take(5)->get() as $reservation)
                                <tr>
                                    <td>{{ $reservation->booking_code }}</td>
                                    <td>{{ $reservation->customer->name }}</td>
                                    <td>{{ $reservation->packageVariant->package->name }}</td>
                                    <td>{{ $reservation->date }}</td>
                                    <td>
                                        <span class="badge badge-{{ $reservation->booking_status === 'confirmed' ? 'success' : ($reservation->booking_status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($reservation->booking_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $reservation->payment_status === 'paid' ? 'success' : ($reservation->payment_status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($reservation->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
