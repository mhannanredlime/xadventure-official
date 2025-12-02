@csrf
@if (isset($package))
    @method('PUT')
@endif

{{-- ---------- Image Upload Section ---------- --}}
<div class="card p-4 mb-4">
    <h5 class="card-title"><i class="bi bi-images me-2"></i>Package Images</h5>
    <div class="row">
        <div class="col-12">
            <label class="form-label">Upload Package Images (Max 4 images)</label>
            <input type="file" id="package_images_input" name="images[]" multiple accept="image/*" style="display:none;">

            <div id="multiple-image-upload" data-model-type="Package" data-model-id="{{ $package->id ?? '' }}"
                data-upload-url="{{ route('admin.regular-packege-management.store') }}"
                data-update-url="{{ isset($package) ? route('admin.regular-packege-management.update', $package) : '' }}"
                data-images-url="{{ route('admin.images.get', ['model_type' => 'Package', 'model_id' => $package->id ?? '']) }}"
                data-primary-url="{{ url('admin/images') }}/:id/primary"
                data-reorder-url="{{ route('admin.images.reorder') }}"
                data-alt-text-url="{{ url('admin/images') }}/:id/alt-text"
                data-delete-url="{{ url('admin/images') }}/:id"
                data-existing-images="{{ isset($package) ? $package->images->toJson() : '[]' }}" data-max-files="4"
                data-max-file-size="{{ 5 * 1024 * 1024 }}">
            </div>

            @if (isset($package) && $package->images->count() > 0)
                <div class="mt-3">
                    <p class="text-success mb-2">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ $package->images->count() }} existing image(s) found
                    </p>
                    <div class="image-preview-container">
                        @foreach ($package->images as $image)
                            {{-- @dd($image); --}}
                            <div class="existing-image">
                                <img src="{{ asset('storage/' . $image->image_path) }}"
                                    alt="Package Image {{ $loop->iteration }}"
                                    title="{{ $image->alt_text ?? 'Package Image' }}">
                                <span class="image-number">{{ $loop->iteration }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <small class="text-muted mt-2 d-block">
                <i class="bi bi-info-circle me-1"></i>
                Upload new images or manage existing ones. First image is the main display image.
            </small>
        </div>
    </div>
</div>

{{-- Package Details --}}
<div class="card p-4 mb-4">
    <h5 class="card-title">Package Details</h5>
    <div class="row g-4">
        <div class="col-md-4">
            <label class="form-label">Package Type <span class="text-danger">*</span></label>
            <select class="form-select @error('packageType') is-invalid @enderror" name="packageType" required>
                <option value="">Select Package Type</option>
                @foreach ($packageTypes as $type)
                    <option value="{{ $type->id }}"
                        {{ old('packageType', @$package->package_type_id) == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
            @error('packageType')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Package Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('packageName') is-invalid @enderror" name="packageName"
                value="{{ old('packageName', @$package->name) }}" placeholder="Enter package name" required>
            @error('packageName')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Sub Title</label>
            <input type="text" class="form-control @error('subTitle') is-invalid @enderror" name="subTitle"
                value="{{ old('subTitle', @$package->subtitle) }}" placeholder="Enter subtitle">
            @error('subTitle')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>



        <div class="col-12">
            <label class="form-label">Package Details</label>
            <textarea class="form-control @error('details') is-invalid @enderror" name="details" rows="5"
                placeholder="Describe the package details...">{{ old('details', @$package->details) }}</textarea>
            @error('details')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Display Base Price (৳) <span class="text-danger">*</span></label>
            <input type="number" step="0.01"
                class="form-control @error('displayStartingPrice') is-invalid @enderror" name="displayStartingPrice"
                value="{{ old('displayStartingPrice', @$package->display_starting_price) }}" min="50"
                placeholder="0.00" required>
            @error('displayStartingPrice')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Minimum Participants <span class="text-danger">*</span></label>
            <input type="number" class="form-control @error('minParticipant') is-invalid @enderror"
                name="minParticipant" value="{{ old('minParticipant', @$package->min_participants) }}" min="1"
                required>
            @error('minParticipant')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Maximum Participants <span class="text-danger">*</span></label>
            <input type="number" class="form-control @error('maxParticipant') is-invalid @enderror"
                name="maxParticipant" value="{{ old('maxParticipant', @$package->max_participants) }}" min="1"
                required>
            @error('maxParticipant')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- Package Pricing use direct html form  --}}
<div class="card p-4 mb-4">
    <h5 class="card-title"><i class="bi bi-tag me-2"></i>Package Pricing (Day-wise)</h5>

    {{-- PHP logic to prepare existing prices --}}
    @php
        $days = weekDays();
        $existingPrices = [];
        if (isset($package) && $package->packagePrices) {
            foreach ($package->packagePrices as $price) {
                $existingPrices[$price->day] = $price->price;
            }
        }
        $existingDayPrices = [];
        foreach ($days as $d) {
            $existingDayPrices[] = ['day' => $d, 'price' => $existingPrices[$d] ?? null];
        }
    @endphp

    <div class="mb-4">
        <label class="form-label">Apply Same Price to All Days:</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" id="applyAllPrice" class="form-control" placeholder="Enter price for all days"
                min="0" step="0.01">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th>Day</th>
                    <th>Price (৳)</th>
                </tr>
            </thead>
            <tbody id="priceContainer">
                {{-- Dynamic HTML generation for price inputs --}}
                @foreach ($days as $day)
                    @php
                        $isWeekend = in_array($day, ['fri', 'sat']);
                        $weekendBadge = $isWeekend
                            ? '<span class="badge bg-warning text-dark ms-2 weekend-badge">Weekend</span>'
                            : '';
                        $val = $existingPrices[$day] ?? '';
                    @endphp
                    <tr class="{{ $isWeekend ? 'table-warning' : '' }}">
                        <td class="fw-bold text-uppercase">
                            {{ ucfirst($day) }} {!! $weekendBadge !!}
                        </td>
                        <td>
                            <input type="number" class="form-control day-price-input text-center"
                                data-day="{{ $day }}" value="{{ $val }}" placeholder="0.00"
                                min="0" step="0.01">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <input type="hidden" name="day_prices" id="dayPricesInput"
        value="{{ old('day_prices', json_encode($existingDayPrices)) }}">
</div>

<div class="d-flex justify-content-end mt-4">
    <button type="submit" class="btn btn-save" id="submitBtn">
        {{ isset($package) ? 'Update Package' : 'Save Package' }}
    </button>
</div>
