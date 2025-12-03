@php
    $isEdit = isset($package);
    $pageTitle = $isEdit ? 'Edit ATV/UTV Package' : 'Add ATV/UTV Package';
    $weekendDays = $weekendDays ?? ['fri', 'sat'];
    $dayPrices = $dayPrices ?? [];
@endphp
@csrf
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
<div class="card p-4 mb-4">
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($days as $day)
                    @php
                        $isWeekend = in_array($day, $weekendDays);
                        $dayName = ucfirst($day);
                    @endphp
                    <tr class="{{ $isWeekend ? 'table-warning' : '' }}" data-day-row="{{ $day }}">
                        <td>
                            {{ $dayName }}
                            @if ($isWeekend)
                                <span class="badge bg-warning text-dark ms-2">Weekend</span>
                            @endif
                        </td>

                        @foreach ($riderTypes as $rider)
                         {{-- getting day wise price and rider wise price properly --}}
                            @php
                                $val = '';
                                $priceObj = collect($dayPrices)->firstWhere(function ($item) use ($day, $rider) {
                                    return $item['day'] == $day && $item['rider_type_id'] == $rider->id;
                                });
                                $val = $priceObj['price'] ?? '';
                            @endphp

                            <td>
                                <div class="input-group">
                                    <input type="number" class="form-control day-rider-price"
                                        data-day="{{ $day }}" data-rider="{{ $rider->id }}"
                                        value="{{ old('day_prices.' . $day . '.' . $rider->id, $val) }}" min="0"
                                        placeholder="Price">

                                    <button type="button" class="btn btn-outline-danger clear-price-btn"
                                        title="Clear Price">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                            </td>
                        @endforeach

                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-day-row"
                                data-day="{{ $day }}" title="Remove Day">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <input type="hidden" name="day_prices" id="dayPricesInput" value="[]">
        <input type="hidden" name="active_days" id="activeDaysInput" value="{{ json_encode($days) }}">
    </div>
</div>


<div class="d-flex justify-content-end mt-4">
    <button type="button" class="btn btn-save" id="submitBtn">
        {{ $isEdit ? 'Update Package' : 'Save Package' }}
    </button>
</div>


@push('scripts')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.clear-price-btn', function() {
                $(this).prev('.day-rider-price').val('');
            });
        });
    </script>
@endpush
