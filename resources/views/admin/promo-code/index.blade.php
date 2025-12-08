@extends('layouts.admin')

@section('title', 'Promo Codes')

@section('content')
    <main class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 page-title-row">
            <h1 class="h3 page-title">Promo Codes</h1>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header bg-white p-3">
                <div
                    class="d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center gap-2">
                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
                        <div class="input-group">
                            <select class="form-select" id="packageFilter">
                                <option value="">All Packages</option>
                                @foreach ($packages ?? [] as $package)
                                    <option value="{{ $package->id }}">{{ $package->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group">
                            <select class="form-select" id="vehicleFilter">
                                <option value="">All Vehicles</option>
                                @foreach ($vehicleTypes ?? [] as $vehicleType)
                                    <option value="{{ $vehicleType->id }}">{{ $vehicleType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        @can('promo-codes.manage')
                            <a href="{{ route('admin.promo-codes.create') }}" class="btn btn-add-new jatio-bg-color">
                                <i class="bi bi-plus-lg"></i> Add New Promo Code
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 responsive-stacked" id="promoCodesTable">
                        <thead>
                            <tr>
                                <th>Promo Code</th>
                                <th>Applies To</th>
                                <th>Calculation (% or Flat)</th>
                                <th>Discount Amount</th>
                                <th>Start Date</th>
                                <th>Expire Date</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($promoCodes as $promoCode)
                                <tr data-package="{{ $promoCode->package_id }}"
                                    data-vehicle="{{ $promoCode->vehicle_type_id }}" data-status="{{ $promoCode->status }}">

                                    <td data-label="Promo Code"><strong>{{ $promoCode->code }}</strong></td>
                                    <td data-label="Applies To">
                                        @if ($promoCode->applies_to === 'all')
                                            <span class="badge bg-primary">All Packages</span>
                                        @elseif($promoCode->applies_to === 'package')
                                            <span class="badge bg-info">{{ $promoCode->package?->name ?? 'N/A' }}</span>
                                        @else
                                            <span
                                                class="badge bg-warning text-dark">{{ $promoCode->vehicleType?->name ?? 'N/A' }}</span>
                                        @endif
                                    </td>
                                    <td data-label="Type/Value">
                                        {{ $promoCode->discount_type === 'percentage' ? $promoCode->discount_value . '%' : '৳ ' . number_format($promoCode->discount_value) }}
                                    </td>
                                    <td data-label="Max Discount">
                                        @if ($promoCode->max_discount)
                                            Max: ৳ {{ number_format($promoCode->max_discount) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td data-label="Start Date">
                                        {{ $promoCode->starts_at ? date('d M, Y', strtotime($promoCode->starts_at)) : 'No Start Date' }}
                                    </td>
                                    <td data-label="Expire Date">
                                        {{ $promoCode->ends_at ? date('d M, Y', strtotime($promoCode->ends_at)) : 'No End Date' }}
                                    </td>
                                    <td data-label="Status">
                                        @php
                                            $statusClass = match ($promoCode->status) {
                                                'active' => 'bg-success',
                                                'inactive' => 'bg-secondary',
                                                'expired' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ ucfirst($promoCode->status) }}</span>
                                    </td>
                                    <td data-label="Remarks">{{ Str::limit($promoCode->remarks, 30) }}</td>
                                    <td data-label="Action" class="text-center action-icons">
                                        @can('promo-codes.manage')
                                            <a href="{{ route('admin.promo-codes.edit', $promoCode) }}" title="Edit"
                                                class="btn btn-link p-0">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.promo-codes.destroy', $promoCode) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this promo code?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete" class="btn btn-link p-0">
                                                    <i class="bi bi-trash text-danger"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="bi bi-tag fa-3x text-muted mb-3 d-block"></i>
                                        <h5>No Promo Codes Found</h5>
                                        <p class="text-muted">Create your first promo code to get started.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center table-footer">
                <div class="d-flex align-items-center">
                    <span class="me-3">Total Promo Codes: <span id="totalCount">{{ $promoCodes->count() }}</span></span>
                    <span class="me-3">Showing: <span id="showingCount">{{ $promoCodes->count() }}</span></span>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const packageFilter = document.getElementById('packageFilter');
            const vehicleFilter = document.getElementById('vehicleFilter');
            const statusFilter = document.getElementById('statusFilter');
            const tableRows = document.querySelectorAll('#promoCodesTable tbody tr[data-status]');

            function filterTable() {
                const packageValue = packageFilter.value;
                const vehicleValue = vehicleFilter.value;
                const statusValue = statusFilter.value;
                let visibleCount = 0;

                tableRows.forEach(function(row) {
                    const rowPackage = row.dataset.package;
                    const rowVehicle = row.dataset.vehicle;
                    const rowStatus = row.dataset.status;

                    const packageMatch = !packageValue || rowPackage === packageValue;
                    const vehicleMatch = !vehicleValue || rowVehicle === vehicleValue;
                    const statusMatch = !statusValue || rowStatus === statusValue;

                    if (packageMatch && vehicleMatch && statusMatch) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                document.getElementById('showingCount').textContent = visibleCount;
            }

            packageFilter.addEventListener('change', filterTable);
            vehicleFilter.addEventListener('change', filterTable);
            statusFilter.addEventListener('change', filterTable);
        });
    </script>
@endpush
