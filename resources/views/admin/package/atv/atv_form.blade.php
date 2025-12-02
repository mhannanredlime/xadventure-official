@php
    $isEdit = isset($package);
    $pageTitle = $isEdit ? 'Edit ATV/UTV Package' : 'Add ATV/UTV Package';
    $weekendDays = $weekendDays ?? ['fri', 'sat'];
    $dayPrices = $dayPrices ?? [];
@endphp

{{-- Package Details --}}
<div class="card p-4 mb-4">
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

{{-- Package Pricing --}}
<div class="card p-4">
    <h5 class="card-title"><i class="bi bi-tag me-2"></i>Package Pricing (Day & Rider-wise)</h5>
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th>Day</th>
                    @foreach ($riderTypes as $rider)
                        <th>
                            {{ $rider->name }}
                            <input type="number" class="form-control apply-all-rider mt-1"
                                data-rider="{{ $rider->id }}" placeholder="Apply all">
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($days as $day)
                    @php
                        $isWeekend = in_array($day, $weekendDays);
                        $dayName = ucfirst($day);
                        // dd($day,$weekendDays, $dayName);
                    @endphp
                    <tr class="{{ $isWeekend ? 'table-warning' : '' }}">
                        <td>
                            {{ $dayName }}
                            @if ($isWeekend)
                                <span class="badge bg-warning text-dark ms-2">Weekend</span>
                            @endif
                        </td>

                        @foreach ($riderTypes as $rider)
                            @php
                                $val = '';
                                if (isset($dayPrices[$day]) && is_array($dayPrices[$day])) {
                                    $priceObj = collect($dayPrices[$day])->firstWhere('rider_type_id', $rider->id);
                                    $val = $priceObj['price'] ?? '';
                                }
                                // @dd($val); getting properly
                            @endphp
                            <td>
                                <input type="number" class="form-control day-rider-price"
                                    name="day_prices[{{ $day }}][{{ $rider->id }}]"
                                    data-day="{{ $day }}" data-rider="{{ $rider->id }}"
                                    value="{{ old('day_prices.' . $day . '.' . $rider->id, $val) }}" min="0">

                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <input type="hidden" name="day_prices" id="dayPricesInput" value="{{ json_encode($dayPrices) }}">
    <input type="hidden" name="active_days" id="activeDaysInput" value="{{ json_encode($days) }}">
</div>

<div class="d-flex justify-content-end mt-4">
    <button type="button" class="btn btn-save" id="submitBtn">
        {{ $isEdit ? 'Update Package' : 'Save Package' }}
    </button>
</div>

@push('scripts')
    <script>
        $(function() {
            const weekendDays = {!! json_encode($weekendDays) !!};
            const allDays = {!! json_encode($days) !!};
            let prices = {!! json_encode($dayPrices) !!};

            function getDayType(day) {
                return weekendDays.includes(day) ? 'weekend' : 'weekday';
            }

            function updatePrice(day, riderId, val) {
                if (!prices[day]) prices[day] = [];
                const idx = prices[day].findIndex(p => p.rider_type_id == riderId);
                if (val === null || val === '' || val === undefined) {
                    if (idx !== -1) prices[day].splice(idx, 1);
                } else {
                    if (idx !== -1) prices[day][idx].price = val;
                    else prices[day].push({
                        rider_type_id: riderId,
                        price: val
                    });
                }
            }

            // Apply-All functionality
            $(document).on('input', '.apply-all-rider', function() {
                const riderId = $(this).data('rider');
                const val = $(this).val();
                $(`.day-rider-price[data-rider="${riderId}"]`).each(function() {
                    $(this).val(val);
                    updatePrice($(this).data('day'), riderId, val);
                });
            });

            // Individual input update
            $(document).on('input', '.day-rider-price', function() {
                const day = $(this).data('day');
                const riderId = $(this).data('rider');
                const val = $(this).val() === '' ? null : Number($(this).val());
                updatePrice(day, riderId, val);
            });

            // Form submission
            $('#submitBtn').on('click', function(e) {
                e.preventDefault();
                const dayPricesArray = [];
                Object.keys(prices).forEach(day => {
                    prices[day].forEach(p => {
                        dayPricesArray.push({
                            day: day,
                            rider_type_id: p.rider_type_id,
                            price: p.price,
                            type: getDayType(day)
                        });
                    });
                });
                $('#dayPricesInput').val(JSON.stringify(dayPricesArray));
                $(this).prop('disabled', true).addClass('btn-loading');
                $('#packageForm')[0].submit();
            });
        });
    </script>
@endpush
