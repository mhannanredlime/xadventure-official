@csrf

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="code" class="form-label">Promo Code <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code"
                value="{{ old('code', $promoCode->code ?? '') }}" required>
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="applies_to" class="form-label">Applies To <span class="text-danger">*</span></label>
            <select class="form-select @error('applies_to') is-invalid @enderror" id="applies_to" name="applies_to"
                required>
                <option value="all"
                    {{ old('applies_to', $promoCode->applies_to ?? 'all') === 'all' ? 'selected' : '' }}>All Packages
                </option>
                <option value="package"
                    {{ old('applies_to', $promoCode->applies_to ?? '') === 'package' ? 'selected' : '' }}>Specific
                    Package</option>
                <option value="vehicle_type"
                    {{ old('applies_to', $promoCode->applies_to ?? '') === 'vehicle_type' ? 'selected' : '' }}>Specific
                    Vehicle Type</option>
            </select>
            @error('applies_to')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6" id="packageField"
        style="{{ old('applies_to', $promoCode->applies_to ?? '') === 'package' ? '' : 'display: none;' }}">
        <div class="mb-3">
            <label for="package_id" class="form-label">Package</label>
            <select class="form-select @error('package_id') is-invalid @enderror" id="package_id" name="package_id">
                <option value="">Select Package</option>
                @foreach ($packages as $package)
                    <option value="{{ $package->id }}"
                        {{ old('package_id', $promoCode->package_id ?? '') == $package->id ? 'selected' : '' }}>
                        {{ $package->name }}
                    </option>
                @endforeach
            </select>
            @error('package_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6" id="vehicleTypeField"
        style="{{ old('applies_to', $promoCode->applies_to ?? '') === 'vehicle_type' ? '' : 'display: none;' }}">
        <div class="mb-3">
            <label for="vehicle_type_id" class="form-label">Vehicle Type</label>
            <select class="form-select @error('vehicle_type_id') is-invalid @enderror" id="vehicle_type_id"
                name="vehicle_type_id">
                <option value="">Select Vehicle Type</option>
                @foreach ($vehicleTypes as $vehicleType)
                    <option value="{{ $vehicleType->id }}"
                        {{ old('vehicle_type_id', $promoCode->vehicle_type_id ?? '') == $vehicleType->id ? 'selected' : '' }}>
                        {{ $vehicleType->name }}
                    </option>
                @endforeach
            </select>
            @error('vehicle_type_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Discount Type <span class="text-danger">*</span></label>
            <div class="btn-group w-100" role="group" aria-label="Discount Type">
                <input type="radio" class="btn-check" name="discount_type" id="percentage" value="percentage"
                    {{ old('discount_type', $promoCode->discount_type ?? 'percentage') === 'percentage' ? 'checked' : '' }}>
                <label class="btn btn-outline-primary" for="percentage">Percentage</label>

                <input type="radio" class="btn-check" name="discount_type" id="fixed" value="fixed"
                    {{ old('discount_type', $promoCode->discount_type ?? '') === 'fixed' ? 'checked' : '' }}>
                <label class="btn btn-outline-primary" for="fixed">Flat (৳)</label>
            </div>
            @error('discount_type')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="discount_value" class="form-label">Discount Value <span class="text-danger">*</span></label>
            <input type="number" class="form-control @error('discount_value') is-invalid @enderror" id="discount_value"
                name="discount_value" value="{{ old('discount_value', $promoCode->discount_value ?? '') }}"
                step="0.01" min="0" required>
            @error('discount_value')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="max_discount" class="form-label">Maximum Discount (Optional)</label>
            <input type="number" class="form-control @error('max_discount') is-invalid @enderror" id="max_discount"
                name="max_discount" value="{{ old('max_discount', $promoCode->max_discount ?? '') }}" step="0.01"
                min="0">
            @error('max_discount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="min_spend" class="form-label">Minimum Spend (Optional)</label>
            <input type="number" class="form-control @error('min_spend') is-invalid @enderror" id="min_spend"
                name="min_spend" value="{{ old('min_spend', $promoCode->min_spend ?? '') }}" step="0.01"
                min="0">
            @error('min_spend')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="usage_limit_total" class="form-label">Total Usage Limit (Optional)</label>
            <input type="number" class="form-control @error('usage_limit_total') is-invalid @enderror"
                id="usage_limit_total" name="usage_limit_total"
                value="{{ old('usage_limit_total', $promoCode->usage_limit_total ?? '') }}" min="1">
            @error('usage_limit_total')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="usage_limit_per_user" class="form-label">Usage Limit Per User <span
                    class="text-danger">*</span></label>
            <input type="number" class="form-control @error('usage_limit_per_user') is-invalid @enderror"
                id="usage_limit_per_user" name="usage_limit_per_user"
                value="{{ old('usage_limit_per_user', $promoCode->usage_limit_per_user ?? 1) }}" min="1"
                required>
            @error('usage_limit_per_user')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="starts_at" class="form-label">Start Date</label>
            <input type="date" class="form-control @error('starts_at') is-invalid @enderror" id="starts_at"
                name="starts_at"
                value="{{ old('starts_at', isset($promoCode->starts_at) ? $promoCode->starts_at->format('Y-m-d') : '') }}">
            @error('starts_at')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="ends_at" class="form-label">Expire Date</label>
            <input type="date" class="form-control @error('ends_at') is-invalid @enderror" id="ends_at"
                name="ends_at"
                value="{{ old('ends_at', isset($promoCode->ends_at) ? $promoCode->ends_at->format('Y-m-d') : '') }}">
            @error('ends_at')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="3">{{ old('remarks', $promoCode->remarks ?? '') }}</textarea>
            @error('remarks')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="mb-3">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <div class="btn-group w-100" role="group" aria-label="Discount Status">
                <input type="radio" class="btn-check" name="status" id="active" value="active"
                    {{ old('status', $promoCode->status ?? 'active') === 'active' ? 'checked' : '' }}>
                <label class="btn btn-outline-success" for="active">Active</label>

                <input type="radio" class="btn-check" name="status" id="inactive" value="inactive"
                    {{ old('status', $promoCode->status ?? '') === 'inactive' ? 'checked' : '' }}>
                <label class="btn btn-outline-secondary" for="inactive">Inactive</label>

                <input type="radio" class="btn-check" name="status" id="expired" value="expired"
                    {{ old('status', $promoCode->status ?? '') === 'expired' ? 'checked' : '' }}>
                <label class="btn btn-outline-danger" for="expired">Expired</label>
            </div>
            @error('status')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <x-admin.button-link href="{{ route('admin.promo-codes.index') }}" class="btn btn-secondary" text="Cancel"
        icon="bi-x-lg" />
    <x-admin.button type="submit" color="save" icon="bi bi-check-circle">
        {{ isset($promoCode) ? 'Update Promo Code' : 'Create Promo Code' }}
    </x-admin.button>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const appliesToSelect = document.getElementById('applies_to');
            const packageField = document.getElementById('packageField');
            const vehicleTypeField = document.getElementById('vehicleTypeField');

            function toggleFields() {
                const value = appliesToSelect.value;
                packageField.style.display = value === 'package' ? '' : 'none';
                vehicleTypeField.style.display = value === 'vehicle_type' ? '' : 'none';
            }

            appliesToSelect.addEventListener('change', toggleFields);
            toggleFields();
        });
    </script>
@endpush
