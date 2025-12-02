{{-- resources/views/admin/package/atv/create.blade.php --}}
@extends('layouts.admin')
@section('title', isset($package) ? 'Edit ATV/UTV Package' : 'Add ATV/UTV Package')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/regular-package.css') }}">
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
            @include('admin.package.atv.atv_form')
        </form>
    </main>
@endsection

@push('scripts')
    <script>
        $(function() {
            const $priceContainer = $('#priceContainer');

            // Safely pass PHP variables to JS
            const allDays = {!! json_encode($days ?? ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']) !!};
            const riderTypes = {!! isset($riderTypes) ? $riderTypes->toJson() : '[]' !!};
            const weekendDays = {!! isset($weekendDays) ? json_encode($weekendDays) : '["fri","sat"]' !!};

            // Prices object: day => [{rider_type_id, price}]
            let prices = {!! isset($package->day_prices) ? json_encode($package->day_prices) : '{}' !!} || {};

            function getDayType(day) {
                return weekendDays.includes(day) ? 'weekend' : 'weekday';
            }

            function renderTable() {
                $priceContainer.empty();
                allDays.forEach(day => {
                    const isWeekend = weekendDays.includes(day);
                    const dayName = day.charAt(0).toUpperCase() + day.slice(1);

                    const weekendBadge = isWeekend ?
                        ' <span class="badge bg-warning text-dark ms-2">Weekend</span>' :
                        '';

                    const $tr = $('<tr>').addClass(isWeekend ? 'table-warning' : '');
                    $tr.append(`<td>${dayName}${weekendBadge}</td>`);

                    riderTypes.forEach(rider => {
                        const riderId = rider.id;

                        // Look for existing price
                        let val = '';
                        if (prices[day] && Array.isArray(prices[day])) {
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

            // Apply All functionality
            $(document).on('input', '.apply-all-rider', function() {
                const riderId = $(this).data('rider');
                const val = $(this).val();

                $(`.day-rider-price[data-rider="${riderId}"]`).each(function() {
                    $(this).val(val);
                    const day = $(this).data('day');
                    updatePriceInObject(day, riderId, val);
                });
            });

            // Update individual inputs
            $(document).on('input', '.day-rider-price', function() {
                const day = $(this).data('day');
                const riderId = $(this).data('rider');
                const val = $(this).val() === '' ? null : Number($(this).val());
                updatePriceInObject(day, riderId, val);
            });

            function updatePriceInObject(day, riderId, val) {
                if (!prices[day]) prices[day] = [];
                const idx = prices[day].findIndex(p => p.rider_type_id == riderId);

                if (val === null || val === '') {
                    if (idx !== -1) prices[day].splice(idx, 1);
                } else {
                    if (idx !== -1) prices[day][idx].price = val;
                    else prices[day].push({
                        rider_type_id: riderId,
                        price: val
                    });
                }
            }

            $('#submitBtn').on('click', function(e) {
                e.preventDefault();

                const dayPricesArray = [];
                allDays.forEach(day => {
                    if (prices[day] && Array.isArray(prices[day])) {
                        prices[day].forEach(p => {
                            dayPricesArray.push({
                                day: day,
                                rider_type_id: p.rider_type_id,
                                price: p.price,
                                type: getDayType(day)
                            });
                        });
                    }
                });

                $('#dayPricesInput').val(JSON.stringify(dayPricesArray));
                $(this).prop('disabled', true).addClass('btn-loading');
                $('#packageForm')[0].submit();
            });

            renderTable();
        });
    </script>
@endpush
