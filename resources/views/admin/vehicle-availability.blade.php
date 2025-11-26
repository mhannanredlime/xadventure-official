@extends('layouts.admin')

@section('title', 'Vehicle Availability Dashboard')

@push('styles')
<style>
.availability-card {
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
}

.availability-card:hover {
    transform: translateY(-2px);
}

.vehicle-type-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.package-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.utilization-bar {
    height: 8px;
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.3);
    overflow: hidden;
}

.utilization-fill {
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    transition: width 0.3s ease;
    width: var(--util, 0%);
}

.availability-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.availability-high {
    background: #10b981;
    color: white;
}

.availability-medium {
    background: #f59e0b;
    color: white;
}

.availability-low {
    background: #ef4444;
    color: white;
}

.availability-none {
    background: #6b7280;
    color: white;
}

.vehicle-list {
    max-height: 200px;
    overflow-y: auto;
}

.vehicle-item {
    padding: 8px 12px;
    border-radius: 6px;
    margin-bottom: 4px;
    background: rgba(255, 255, 255, 0.1);
    border-left: 3px solid rgba(255, 255, 255, 0.5);
}

.date-picker {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 1rem;
}

.date-picker:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Responsive grid layout */
@media (max-width: 991.98px) {
    .col-lg-8, .col-lg-4 {
        margin-bottom: 2rem;
    }

    .sticky-top {
        position: static !important;
    }
}

@media (min-width: 992px) {
    .sticky-top {
        position: sticky !important;
        top: 20px !important;
    }
}

/* Grid improvements */
.main-grid-layout {
    gap: 2rem;
}

.left-column {
    min-height: 100%;
}

.right-column {
    height: fit-content;
}
</style>
@endpush

@section('content')
<div class="dashboard-area">
    <div class="dashboard-header">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Vehicle Availability Dashboard</h1>
                <p class="text-muted mb-0">Real-time vehicle availability based on bookings and vehicle management</p>
            </div>
            <div class="d-flex gap-3 align-items-center">
                <input type="date" id="datePicker" class="date-picker" value="{{ $date }}">
                <button class="btn btn-primary" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi  bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Main Content Grid Layout -->
    <div class="row main-grid-layout">
        <!-- Left Column: Vehicle Information -->
        <div class="col-lg-8 left-column">
            <!-- Vehicle Type Summary -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card availability-card vehicle-type-card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-car-front me-2"></i>Vehicle Type Availability Summary
                            </h5>
                            <div class="row">
                                @foreach($vehicleTypeAvailability as $vehicleTypeId => $data)
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $data['vehicle_type']->name }}</h6>
                                            <small class="opacity-75">{{ $data['vehicle_type']->subtitle }}</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold">{{ $data['available_vehicles'] }}/{{ $data['total_vehicles'] }}</div>
                                            <small class="opacity-75">Available</small>
                                        </div>
                                    </div>
                                    <div class="utilization-bar mt-2">
                                        <div class="utilization-fill" style="--util: {{ $data['utilization_percentage'] }}%"></div>
                                    </div>
                                    <small class="opacity-75">{{ $data['utilization_percentage'] }}% utilized</small>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Vehicle Breakdown -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card availability-card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-list-ul me-2"></i>Detailed Vehicle Breakdown
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach($vehicleBreakdown as $vehicleTypeId => $data)
                            <div class="mb-4">
                                <h6 class="fw-bold text-primary">{{ $data['vehicle_type']->name }} - {{ $data['vehicle_type']->subtitle }}</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="h4 mb-1">{{ $data['total_vehicles'] }}</div>
                                            <small class="text-muted">Total Vehicles</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="h4 mb-1 text-success">{{ $data['available_vehicles'] }}</div>
                                            <small class="text-muted">Available</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="h4 mb-1 text-warning">{{ $data['booked_vehicles'] }}</div>
                                            <small class="text-muted">Booked</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="h4 mb-1 text-info">{{ $data['utilization_percentage'] }}%</div>
                                            <small class="text-muted">Utilization</small>
                                        </div>
                                    </div>
                                </div>

                                @if($data['vehicles']->count() > 0)
                                <div class="mt-3">
                                    <h6 class="fw-bold">Individual Vehicles:</h6>
                                    <div class="vehicle-list">
                                        @foreach($data['vehicles'] as $vehicle)
                                        <div class="vehicle-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $vehicle->name }}</strong>
                                                    @if($vehicle->details)
                                                        <small class="d-block text-muted">{{ $vehicle->details }}</small>
                                                    @endif
                                                </div>
                                                <div>
                                                    @if($vehicle->op_start_date)
                                                        <small class="text-muted">Available from: {{ $vehicle->op_start_date->format('M d, Y') }}</small>
                                                    @endif
                                                    <span class="badge {{ $vehicle->is_active ? 'bg-success' : 'bg-secondary' }} ms-2">
                                                        {{ $vehicle->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Package Availability -->
        <div class="col-lg-4 right-column">
            <div class="card availability-card package-card sticky-top" style="top: 20px;">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0 text-white">
                        <i class="bi bi-box me-2"></i>Package Availability
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($packageAvailability as $variantId => $data)
                    <div class="mb-3 p-3 bg-white bg-opacity-10 rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $data['vehicle_types'] ? array_keys($data['vehicle_types'])[0] : 'Unknown' }} Package</h6>
                                <small class="opacity-75">Variant ID: {{ $variantId }}</small>
                            </div>
                            <div class="text-end">
                                @php
                                    $availabilityClass = 'availability-none';
                                    if ($data['total_available'] > 5) {
                                        $availabilityClass = 'availability-high';
                                    } elseif ($data['total_available'] > 2) {
                                        $availabilityClass = 'availability-medium';
                                    } elseif ($data['total_available'] > 0) {
                                        $availabilityClass = 'availability-low';
                                    }
                                @endphp
                                <span class="availability-badge {{ $availabilityClass }}">
                                    {{ $data['total_available'] }} Available
                                </span>
                            </div>
                        </div>

                        @if($data['vehicle_types'])
                        <div class="mt-2">
                            <small class="opacity-75">Vehicle Types:</small>
                            @foreach($data['vehicle_types'] as $vehicleTypeName => $vehicleData)
                            <span class="badge bg-light text-dark me-1">
                                {{ $vehicleTypeName }}: {{ $vehicleData['available_vehicles'] }}/{{ $vehicleData['total_vehicles'] }}
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date picker
    const datePicker = document.getElementById('datePicker');
    datePicker.addEventListener('change', function() {
        refreshData();
    });
});

function refreshData() {
    const date = document.getElementById('datePicker').value;
    const url = new URL(window.location);
    url.searchParams.set('date', date);
    window.location.href = url.toString();
}
</script>
@endpush
