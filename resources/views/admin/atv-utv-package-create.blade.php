@extends('layouts.admin')
@section('title', isset($package) ? 'Edit ATV/UTV Package' : 'Add ATV/UTV Package')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
    <style>
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, .1);
            border-radius: .75rem;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #2c3e50;
            font-size: 1.25rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: .5rem;
        }

        .form-control,
        .form-select {
            border-radius: .5rem;
            border: 1px solid #dee2e6;
            padding: .75rem 1rem;
            transition: all .2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #F76B19;
            box-shadow: 0 0 0 .2rem rgba(247, 107, 25, .25);
        }

        .btn-save {
            background: linear-gradient(135deg, #F76B19 0%, #e55e14 100%);
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            border-radius: .75rem;
            padding: 1rem 2.5rem;
            border: none;
            box-shadow: 0 6px 20px rgba(247, 107, 25, .3);
            position: relative;
            overflow: hidden;
        }

        .btn-save.btn-loading {
            pointer-events: none;
            color: transparent;
        }

        .btn-save.btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid transparent;
            border-top: 2px solid #fff;
            border-radius: 50%;
            animation: button-spinner .8s linear infinite;
        }

        @keyframes button-spinner {
            0% {
                transform: rotate(0)
            }

            100% {
                transform: rotate(360deg)
            }
        }

        .table-warning td {
            background: #fff3cd;
        }
    </style>
@endpush

@section('content')
    <main class="mt-4">
        <header class="d-flex justify-content-between align-items-center page-header mb-4">
            <div>
                <h3>{{ isset($package) ? 'Edit ATV/UTV Package' : 'Add ATV/UTV Package' }}</h3>
                <p class="breadcrumb-custom"><i class="bi bi-home me-1"></i> Package Management &gt;
                    {{ isset($package) ? 'Edit' : 'Add' }} Package</p>
            </div>
            <a href="{{ url('admin/add-packege-management') }}" class="btn btn-outline-secondary"><i
                    class="bi bi-arrow-left me-2"></i>Back to Packages</a>
        </header>

        {{-- Alerts --}}
        @foreach (['success', 'error'] as $msg)
            @if (session($msg))
                <div class="alert alert-{{ $msg == 'success' ? 'success' : 'danger' }} alert-dismissible fade show"
                    role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi {{ $msg == 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-2"></i>
                        {{ session($msg) }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i><strong>Please correct the following errors:</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form id="packageForm" method="POST"
            action="{{ isset($package) ? route('admin.atvutv-packege-management.update', $package->id) : route('admin.atvutv-packege-management.store') }}"
            enctype="multipart/form-data">
            @csrf
            @if (isset($package))
                @method('PUT')
            @endif

            {{-- Package Details --}}
            <div class="card p-4">
                <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>Package Details</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="vehicleType" required>
                            <option value="">Select Vehicle Type</option>
                            @foreach ($vehicleTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('vehicleType', $package->vehicle_type_id ?? '') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Package Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="packageName"
                            value="{{ old('packageName', $package->name ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sub Title</label>
                        <input type="text" class="form-control" name="subTitle"
                            value="{{ old('subTitle', $package->subtitle ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Package Details</label>
                        <textarea class="form-control" name="details" rows="5">{{ old('details', $package->details ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            @php
                $weekendDays = $package
                    ? \App\Models\PackageWeekendDay::where('package_id', $package->id)->pluck('day')->toArray()
                    : ['fri', 'sat'];
            @endphp

            {{-- Package Pricing --}}
            <div class="card p-4">
                <h5 class="card-title"><i class="bi bi-tag me-2"></i>Package Pricing (Day & Rider-wise)</h5>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Day</th>
                                @foreach ($riderTypes as $rider)
                                    @php
                                        // Use rider_type_id instead of rider_count if that's what you have
                                        $riderId = $rider->id; // or $rider->rider_type_id if different
                                    @endphp
                                    <th>{{ $rider->name }}
                                        <input type="number" class="form-control apply-all-rider mt-1"
                                            data-rider="{{ $riderId }}" placeholder="Apply all">
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="priceContainer"></tbody>
                    </table>
                </div>

                <input type="hidden" name="day_prices" id="dayPricesInput"
                    value="{{ old('day_prices', json_encode($package->day_prices ?? [])) }}">
                <input type="hidden" name="active_days" id="activeDaysInput" value="{{ json_encode($days) }}">
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-save"
                    id="submitBtn">{{ isset($package) ? 'Update Package' : 'Save Package' }}</button>
            </div>
        </form>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/multiple-image-upload.js') }}"></script>
    <script>
        $(function() {
            const $priceContainer = $('#priceContainer');
            const allDays = {!! json_encode($days) !!};
            const riderTypes = {!! $riderTypes->toJson() !!};
            let prices = {!! json_encode($package->day_prices ?? []) !!} || [];
            const weekendDays = {!! json_encode($weekendDays) !!};

            function getDayType(day) {
                return weekendDays.includes(day) ? 'weekend' : 'weekday';
            }

            function renderTable() {
                $priceContainer.empty();
                allDays.forEach(day => {
                    const isWeekend = weekendDays.includes(day);
                    const $tr = $('<tr>').addClass(isWeekend ? 'table-warning' : '');
                    $tr.append(`<td>${day.charAt(0).toUpperCase()+day.slice(1)}</td>`);

                    riderTypes.forEach(rider => {
                        // Use rider ID instead of rider_count
                        const riderId = rider.id;

                        // Check if price exists for this day and rider
                        let val = '';
                        if (prices[day]) {
                            // Look for the price in the array format
                            const priceObj = prices[day].find(p => p.rider_type_id == riderId);
                            val = priceObj ? priceObj.price : '';
                        }

                        $tr.append(
                            `<td><input type="number" class="form-control day-rider-price" 
                            data-day="${day}" 
                            data-rider="${riderId}" 
                            value="${val}" 
                            min="0"></td>`
                        );
                    });
                    $priceContainer.append($tr);
                });
                $('#activeDaysInput').val(JSON.stringify(allDays));
            }

            // Apply All functionality - Fixed
            $(document).on('input', '.apply-all-rider', function() {
                const riderId = $(this).data('rider');
                const val = $(this).val();

                // Update all inputs with matching rider ID
                $(`.day-rider-price[data-rider="${riderId}"]`).each(function() {
                    $(this).val(val);

                    // Update the prices object
                    const day = $(this).data('day');
                    updatePriceInObject(day, riderId, val);
                });
            });

            // Update individual price inputs
            $(document).on('input', '.day-rider-price', function() {
                const day = $(this).data('day');
                const riderId = $(this).data('rider');
                const val = $(this).val() === '' ? null : Number($(this).val());
                updatePriceInObject(day, riderId, val);
            });

            function updatePriceInObject(day, riderId, val) {
                if (!prices[day]) {
                    prices[day] = [];
                }

                // Find if price already exists for this rider
                const existingIndex = prices[day].findIndex(p => p.rider_type_id == riderId);

                if (val === null || val === '') {
                    // Remove if empty
                    if (existingIndex !== -1) {
                        prices[day].splice(existingIndex, 1);
                    }
                } else {
                    // Update or add
                    if (existingIndex !== -1) {
                        prices[day][existingIndex].price = val;
                    } else {
                        prices[day].push({
                            rider_type_id: riderId,
                            price: val
                        });
                    }
                }
            }

            $('#submitBtn').on('click', function(e) {
                e.preventDefault();

                // Format prices for submission
                const dayPricesArray = [];

                allDays.forEach(day => {
                    if (prices[day]) {
                        prices[day].forEach(priceObj => {
                            dayPricesArray.push({
                                day: day,
                                rider_type_id: priceObj.rider_type_id,
                                price: priceObj.price,
                                type: getDayType(day)
                            });
                        });
                    }
                });

                $('#dayPricesInput').val(JSON.stringify(dayPricesArray));

                // For debugging - remove in production
                console.log('Submitting prices:', dayPricesArray);

                $(this).prop('disabled', true).addClass('btn-loading');
                $('#packageForm')[0].submit();
            });

            renderTable();
        });
    </script>
@endpush
