@extends('layouts.admin')

@section('title', 'Reports & Analytics')

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/reports.css') }}">
@endpush

@section('content')
<div class="reports-container">
    <!-- Header -->
    <div class="reports-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-2">Reports & Analytics</h1>
                <p class="text-muted mb-0">Comprehensive insights into your business performance</p>
            </div>
            <a href="{{ route('admin.reports.export', request()->query()) }}" class="export-btn">
                <i class="bi  bi-download"></i>
                Export Data
            </a>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="filter-controls">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" name="date_from" id="date_from" class="form-control"
                       value="{{ $dateFrom->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" name="date_to" id="date_to" class="form-control"
                       value="{{ $dateTo->format('Y-m-d') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Apply Filter</button>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="card-icon" style="background: linear-gradient(135deg, #ff6600, #ff8533);">
                    <i class="bi  bi-calendar-check"></i>
                </div>
                <div class="stat-number">{{ $reservationSummary['total'] }}</div>
                <div class="stat-label">Total Reservations</div>
                <div class="stat-change positive">
                    <i class="bi  bi-arrow-up"></i> {{ $reservationSummary['confirmed'] }} Confirmed
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="card-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">
                    <i class="bi  bi-coins"></i>
                </div>
                <div class="stat-number">৳{{ number_format($financialSummary['total_revenue'], 2) }}</div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-change positive">
                    <i class="bi  bi-arrow-up"></i> ৳{{ number_format($financialSummary['paid_reservations'], 2) }} Paid
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="card-icon" style="background: linear-gradient(135deg, #007bff, #6610f2);">
                    <i class="bi  bi-users"></i>
                </div>
                <div class="stat-number">{{ $customerAnalytics['total_customers'] }}</div>
                <div class="stat-label">Total Customers</div>
                <div class="stat-change positive">
                    <i class="bi  bi-arrow-up"></i> {{ $customerAnalytics['new_customers'] }} New
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="card-icon" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                    <i class="bi  bi-user-friends"></i>
                </div>
                <div class="stat-number">{{ $reservationSummary['total_guests'] }}</div>
                <div class="stat-label">Total Guests</div>
                <div class="stat-change positive">
                    <i class="bi  bi-arrow-up"></i> {{ number_format($reservationSummary['avg_party_size'], 1) }} Avg Party
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="report-section">
                <h3><i class="bi  bi-chart-pie me-2"></i>Financial Summary</h3>
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Revenue:</span>
                            <strong>৳{{ number_format($financialSummary['total_revenue'], 2) }}</strong>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Deposits:</span>
                            <strong>৳{{ number_format($financialSummary['total_deposits'], 2) }}</strong>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Balance:</span>
                            <strong>৳{{ number_format($financialSummary['total_balance'], 2) }}</strong>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Avg Reservation:</span>
                            <strong>৳{{ number_format($financialSummary['avg_reservation_value'], 2) }}</strong>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <h6 class="mb-3">Payment Status Breakdown</h6>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Paid</span>
                            <span>৳{{ number_format($financialSummary['paid_reservations'], 2) }}</span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: {{ $financialSummary['paid_percentage'] }}%"></div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Pending</span>
                            <span>৳{{ number_format($financialSummary['pending_payments'], 2) }}</span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: {{ $financialSummary['pending_percentage'] }}%"></div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Partial</span>
                            <span>৳{{ number_format($financialSummary['partial_payments'], 2) }}</span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: {{ $financialSummary['partial_percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="report-section">
                <h3><i class="bi  bi-chart-line me-2"></i>Reservation Status</h3>
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Confirmed:</span>
                            <strong>{{ $reservationSummary['confirmed'] }}</strong>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Pending:</span>
                            <strong>{{ $reservationSummary['pending'] }}</strong>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Completed:</span>
                            <strong>{{ $reservationSummary['completed'] }}</strong>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Cancelled:</span>
                            <strong>{{ $reservationSummary['cancelled'] }}</strong>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <h6 class="mb-3">Status Distribution</h6>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Confirmed</span>
                            <span>{{ number_format($reservationSummary['confirmed_percentage'], 1) }}%</span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: {{ $reservationSummary['confirmed_percentage'] }}%"></div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Pending</span>
                            <span>{{ number_format($reservationSummary['pending_percentage'], 1) }}%</span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: {{ $reservationSummary['pending_percentage'] }}%"></div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Completed</span>
                            <span>{{ number_format($reservationSummary['completed_percentage'], 1) }}%</span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: {{ $reservationSummary['completed_percentage'] }}%"></div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Cancelled</span>
                            <span>{{ number_format($reservationSummary['cancelled_percentage'], 1) }}%</span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: {{ $reservationSummary['cancelled_percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Analytics Details -->
    <div class="report-section">
        <h3><i class="bi  bi-user-chart me-2"></i>Customer Analytics</h3>
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="text-center p-3 bg-light rounded">
                    <h4 class="text-primary">{{ $customerAnalytics['total_customers'] }}</h4>
                    <p class="text-muted mb-0">Total Customers</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="text-center p-3 bg-light rounded">
                    <h4 class="text-success">{{ $customerAnalytics['new_customers'] }}</h4>
                    <p class="text-muted mb-0">New Customers</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="text-center p-3 bg-light rounded">
                    <h4 class="text-info">{{ $customerAnalytics['repeat_customers'] }}</h4>
                    <p class="text-muted mb-0">Repeat Customers</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="text-center p-3 bg-light rounded">
                    <h4 class="text-warning">{{ number_format($customerAnalytics['customer_growth_rate'], 1) }}%</h4>
                    <p class="text-muted mb-0">Growth Rate</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Performance -->
    <div class="report-section">
        <h3><i class="bi  bi-box me-2"></i>Top Performing Packages</h3>
        @if($packagePerformance->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover responsive-stacked">
                    <thead>
                        <tr>
                            <th>Package Name</th>
                            <th>Reservations</th>
                            <th>Total Revenue</th>
                            <th>Avg Revenue</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packagePerformance as $package)
                            @php
                                $maxReservations = $packagePerformance->max('reservations_count');
                                $performancePercent = $maxReservations > 0 ? ($package->reservations_count / $maxReservations) * 100 : 0;
                                $avgRevenue = $package->reservations_count > 0 ? ($package->reservations_sum_total_amount ?? 0) / $package->reservations_count : 0;
                            @endphp
                            <tr>
                                <td data-label="Package Name">
                                    <strong>{{ $package->name }}</strong>
                                    @if($package->is_active)
                                        <span class="badge badge-confirmed ms-2">Active</span>
                                    @else
                                        <span class="badge badge-cancelled ms-2">Inactive</span>
                                    @endif
                                </td>
                                <td data-label="Reservations">{{ $package->reservations_count }}</td>
                                <td data-label="Total Revenue">৳{{ number_format($package->reservations_sum_total_amount ?? 0, 2) }}</td>
                                <td data-label="Avg Revenue">৳{{ number_format($avgRevenue, 2) }}</td>
                                <td data-label="Performance">
                                    <div class="progress-bar-custom" style="width: 100px;">
                                        <div class="progress-fill" style="width: {{ $performancePercent }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ number_format($performancePercent, 1) }}%</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi  bi-box-open"></i>
                <p>No package performance data available for the selected period.</p>
            </div>
        @endif
    </div>

    <!-- Promo Code Performance -->
    <div class="report-section">
        <h3><i class="bi  bi-tag me-2"></i>Promo Code Performance</h3>
        @if($promoCodePerformance->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover responsive-stacked">
                    <thead>
                        <tr>
                            <th>Promo Code</th>
                            <th>Discount</th>
                            <th>Redemptions</th>
                            <th>Total Savings</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($promoCodePerformance as $promo)
                            <tr>
                                <td data-label="Promo Code"><strong>{{ $promo->code }}</strong></td>
                                <td data-label="Discount">{{ $promo->discount_type === 'percentage' ? $promo->discount_value . '%' : '$' . number_format($promo->discount_value, 2) }}</td>
                                <td data-label="Redemptions">{{ $promo->redemptions_count }}</td>
                                <td data-label="Total Savings">${{ number_format($promo->redemptions_sum_discount_amount ?? 0, 2) }}</td>
                                <td data-label="Status">
                                    @if($promo->is_active)
                                        <span class="badge badge-confirmed">Active</span>
                                    @else
                                        <span class="badge badge-cancelled">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi  bi-tag"></i>
                <p>No promo code performance data available for the selected period.</p>
            </div>
        @endif
    </div>

    <!-- Monthly Trends -->
    <div class="report-section">
        <h3><i class="bi  bi-chart-line me-2"></i>Monthly Trends (Last 12 Months)</h3>
        @if($monthlyTrends->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover responsive-stacked">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Reservations</th>
                            <th>Total Revenue</th>
                            <th>Average Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyTrends as $trend)
                            @php
                                $monthName = date('F Y', mktime(0, 0, 0, $trend->month, 1, $trend->year));
                            @endphp
                            <tr>
                                <td data-label="Month"><strong>{{ $monthName }}</strong></td>
                                <td data-label="Reservations">{{ $trend->total_reservations }}</td>
                                <td data-label="Total Revenue">${{ number_format($trend->total_revenue, 2) }}</td>
                                <td data-label="Average Revenue">${{ number_format($trend->avg_revenue, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi  bi-chart-line"></i>
                <p>No monthly trends data available.</p>
            </div>
        @endif
    </div>

    <!-- Top Customers -->
    <div class="report-section">
        <h3><i class="bi  bi-crown me-2"></i>Top Customers</h3>
        @if($topCustomers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover responsive-stacked">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Phone</th>
                            <th>Reservations</th>
                            <th>Total Spent</th>
                            <th>Avg Spent</th>
                            <th>Last Visit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCustomers as $customer)
                            <tr>
                                <td data-label="Customer Name"><strong>{{ $customer->name }}</strong></td>
                                <td data-label="Phone">{{ $customer->phone ? (new \App\Services\PhoneNumberService())->formatForDisplayWithoutPrefix($customer->phone) : 'N/A' }}</td>
                                <td data-label="Reservations">{{ $customer->reservations_count }}</td>
                                <td data-label="Total Spent">৳{{ number_format($customer->reservations_sum_total_amount ?? 0, 2) }}</td>
                                <td data-label="Avg Spent">৳{{ number_format($customer->reservations_count > 0 ? ($customer->reservations_sum_total_amount ?? 0) / $customer->reservations_count : 0, 2) }}</td>
                                <td data-label="Last Visit">{{ $customer->reservations->first() ? $customer->reservations->first()->date : 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi  bi-users"></i>
                <p>No customer data available for the selected period.</p>
            </div>
        @endif
    </div>

    <!-- Recent Activity -->
    <div class="report-section">
        <h3><i class="bi  bi-clock me-2"></i>Recent Activity</h3>
        @if($recentActivity->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover responsive-stacked">
                    <thead>
                        <tr>
                            <th>Booking Code</th>
                            <th>Customer</th>
                            <th>Package</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivity as $reservation)
                            <tr>
                                <td data-label="Booking Code"><strong>{{ $reservation->booking_code }}</strong></td>
                                <td data-label="Customer">{{ $reservation->customer->name ?? 'N/A' }}</td>
                                <td data-label="Package">{{ $reservation->packageVariant->package->name ?? 'N/A' }}</td>
                                <td data-label="Date">{{ date('m/d/Y', strtotime($reservation->date)) }}</td>
                                <td data-label="Amount">${{ number_format($reservation->total_amount, 2) }}</td>
                                <td data-label="Status">
                                    <span class="badge-status badge-{{ $reservation->booking_status }}">
                                        {{ ucfirst($reservation->booking_status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi  bi-clock"></i>
                <p>No recent activity available.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when date inputs change
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
});
</script>
@endpush
