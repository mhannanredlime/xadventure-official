@extends('layouts.admin')

@section('title', isset($package) ? 'Edit Regular Package' : 'Add Regular Package')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/gallery.css') }}">
    <style>
        /* ---------- General Styles ---------- */
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.1);
            border-radius: 0.75rem;
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
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #F76B19;
            box-shadow: 0 0 0 0.2rem rgba(247, 107, 25, 0.25);
        }

        /* ---------- Pricing Section ---------- */
        .pricing-card {
            border: 1px solid #e9ecef;
            border-radius: 0.75rem;
            padding: 2rem;
            background: #fff;
            margin: 1.5rem 0;
        }

        .pricing-section-title {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .pricing-pills {
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .pricing-pills .nav-link {
            border-radius: 0.6rem;
            border: 1px solid #e0e0e0;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            color: #495057;
            background: #fff;
            min-width: 100px;
            text-align: center;
            transition: all 0.2s;
        }

        .pricing-pills .nav-link.active {
            background-color: #F76B19 !important;
            border-color: #F76B19 !important;
            color: #fff !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(247, 107, 25, 0.3);
        }

        .pricing-pills .nav-link:hover:not(.active) {
            background-color: #f8f9fa;
            border-color: #F76B19;
            color: #F76B19;
            transform: translateY(-1px);
        }

        .price-input .input-group {
            max-width: 300px;
        }

        .price-input .input-group-text {
            font-weight: 600;
            background: #f8f9fa;
            border-color: #dee2e6;
            color: #495057;
        }

        .price-input .form-control {
            border-left: none;
        }

        .selected-day-display {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #F76B19;
        }

        .selected-day-display strong {
            color: #495057;
        }

        .selected-day-display span {
            color: #F76B19;
            font-weight: 600;
        }

        .btn-save {
            background: linear-gradient(135deg, #F76B19 0%, #e55e14 100%);
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 0.75rem;
            padding: 1rem 2.5rem;
            border: none;
            box-shadow: 0 6px 20px rgba(247, 107, 25, 0.3);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(247, 107, 25, 0.4);
            background: linear-gradient(135deg, #e55e14 0%, #d95f17 100%);
        }

        .btn-save:active {
            transform: translateY(-1px);
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
            animation: button-spinner 0.8s linear infinite;
        }

        @keyframes button-spinner {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush

@section('content')
    <main class="mt-4">
        <header class="d-flex justify-content-between align-items-center page-header mb-4">
            <div>
                <h1>{{ isset($package) ? 'Edit Regular Package' : 'Add Regular Package' }}</h1>
                <p class="breadcrumb-custom"><i class="bi bi-home me-1"></i> Package Management >
                    {{ isset($package) ? 'Edit Regular Package' : 'Add Regular Package' }}</p>
            </div>
            <a href="{{ url('admin/add-packege-management') }}" class="btn btn-outline-secondary"><i
                    class="bi bi-arrow-left me-2"></i>Back to Packages</a>
        </header>

        {{-- Alerts --}}
        @foreach (['success', 'error'] as $msg)
            @if (session($msg))
                <div class="alert alert-{{ $msg == 'success' ? 'success' : 'danger' }} alert-dismissible fade show"
                    role="alert">
                    <div class="d-flex align-items-center"><i
                            class="bi {{ $msg == 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-2"></i>{{ session($msg) }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Please correct the following errors:</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body p-4">
                <form id="packageForm" method="POST"
                    action="{{ isset($package) ? route('admin.regular-packege-management.update', $package) : route('admin.regular-packege-management.store') }}"
                    enctype="multipart/form-data" novalidate>
                    @csrf
                    @if (isset($package))
                        @method('PUT')
                    @endif

                    {{-- ---------- Image Upload Section ---------- --}}
                    <div class="image-upload-section">
                        <h5 class="card-title mb-3"><i class="bi bi-images me-2"></i>Package Images</h5>
                        <div class="row">
                            <div class="col-12">
                                <label for="multiple-image-upload" class="form-label">Upload Package Images (Max 4
                                    images)</label>
                                <div id="multiple-image-upload" data-model-type="App\Models\Package"
                                    data-model-id="{{ $package->id ?? '' }}"
                                    data-upload-url="{{ route('admin.regular-packege-management.store') }}"
                                    data-update-url="{{ isset($package) ? route('admin.regular-packege-management.update', $package) : '' }}"
                                    data-images-url="{{ route('admin.images.get') }}"
                                    data-primary-url="{{ url('admin/images') }}/:id/primary"
                                    data-reorder-url="{{ route('admin.images.reorder') }}"
                                    data-alt-text-url="{{ url('admin/images') }}/:id/alt-text"
                                    data-delete-url="{{ url('admin/images') }}/:id"
                                    data-existing-images="{{ isset($package) ? $package->images->toJson() : '[]' }}"
                                    data-max-files="12" data-max-file-size="{{ 5 * 1024 * 1024 }}"></div>
                                <input type="file" id="package_images_input" name="images[]" multiple accept="image/*"
                                    style="display:none;">
                                <input type="hidden" id="gallery_images_input" name="gallery_images" value="">
                                <small class="text-muted mt-2"><i class="bi bi-info-circle me-1"></i> Supported formats:
                                    JPG, PNG, WebP. Max file size: 5MB. First image will be main display.</small>
                            </div>
                        </div>
                    </div>

                    {{-- ---------- Package Details ---------- --}}
                    <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>Package Details</h5>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Package Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('packageName') is-invalid @enderror"
                                name="packageName" value="{{ old('packageName', $package->name ?? '') }}"
                                placeholder="Enter package name" required>
                            @error('packageName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sub Title</label>
                            <input type="text" class="form-control @error('subTitle') is-invalid @enderror"
                                name="subTitle" value="{{ old('subTitle', $package->subtitle ?? '') }}"
                                placeholder="Enter package subtitle">
                            @error('subTitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Package Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('packageType') is-invalid @enderror" name="packageType"
                                required>
                                <option value="">Select Package Type</option>
                                @foreach (['Single', 'Bundle', 'Group'] as $type)
                                    <option value="{{ $type }}"
                                        {{ old('packageType', optional($package?->variants->first())->variant_name) == $type ? 'selected' : '' }}>
                                        {{ $type }} Package</option>
                                @endforeach
                            </select>
                            @error('packageType')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Package Details</label>
                            <textarea class="form-control @error('details') is-invalid @enderror" name="details" rows="5"
                                placeholder="Describe the package details...">{{ old('details', $package->details ?? '') }}</textarea>
                            @error('details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- ---------- Pricing Section ---------- --}}
                    <div class="pricing-card">
                        <h5 class="pricing-section-title"><i class="bi bi-tag me-2"></i>Pricing Configuration</h5>
                        <div class="row g-4">
                            @php
                                $weekdays = weekdays();
                                $weekends = weekends();
                            @endphp

                            {{-- Weekdays --}}
                            <div class="col-lg-6">
                                <label class="fw-semibold mb-1">Weekday Prices</label>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="applyAllWeekdays"
                                        {{ old('apply_all_weekdays', 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="applyAllWeekdays">Apply same price to all
                                        weekdays</label>
                                </div>
                                <ul class="nav nav-pills pricing-pills mb-4" id="weekdayPills">
                                    @foreach ($weekdays as $day)
                                        @php
                                            $dayPrice = old(
                                                'weekday_price_' . $day,
                                                $package->weekdayPrices[$day] ?? 0,
                                            );
                                            $active = $day == old('selected_weekday', 'monday') ? 'active' : '';
                                            $disabled = old('apply_all_weekdays', 0) ? 'disabled' : '';
                                        @endphp
                                        <li class="nav-item"><button type="button" class="nav-link {{ $active }}"
                                                data-day="{{ $day }}" data-price="{{ $dayPrice }}"
                                                {{ $disabled }}>{{ ucfirst($day) }}</button></li>
                                    @endforeach
                                </ul>

                                <div class="price-input"><label class="form-label">Price (৳)</label>
                                    <div class="input-group"><span class="input-group-text">৳</span><input type="number"
                                            step="0.01" id="weekdayPrice" name="weekdayPrice" class="form-control"
                                            value="{{ old('weekdayPrice', $package->weekdayPrices['monday'] ?? 0) }}"
                                            placeholder="0.00" min="0"></div>
                                </div>
                            </div>

                            {{-- Weekends --}}
                            <div class="col-lg-6">
                                <label class="fw-semibold mb-1">Weekend Prices</label>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="applyAllWeekends"
                                        {{ old('apply_all_weekends', 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="applyAllWeekends">Apply same price to all
                                        weekends</label>
                                </div>
                                <ul class="nav nav-pills pricing-pills mb-4" id="weekendPills">
                                    @foreach ($weekends as $day)
                                        @php
                                            $dayPrice = old(
                                                'weekend_price_' . $day,
                                                $package->weekendPrices[$day] ?? 0,
                                            );
                                            $active = $day == old('selected_weekend', 'friday') ? 'active' : '';
                                            $disabled = old('apply_all_weekends', 0) ? 'disabled' : '';
                                        @endphp
                                        <li class="nav-item"><button type="button" class="nav-link {{ $active }}"
                                                data-day="{{ $day }}" data-price="{{ $dayPrice }}"
                                                {{ $disabled }}>{{ ucfirst($day) }}</button></li>
                                    @endforeach
                                </ul>

                                <div class="price-input"><label class="form-label">Price (৳)</label>
                                    <div class="input-group"><span class="input-group-text">৳</span><input type="number"
                                            step="0.01" id="weekendPrice" name="weekendPrice" class="form-control"
                                            value="{{ old('weekendPrice', $package->weekendPrices['friday'] ?? 0) }}"
                                            placeholder="0.00" min="0"></div>
                                </div>
                            </div>

                            {{-- Hidden Inputs --}}
                            <input type="hidden" name="selected_weekday" id="selected_weekday"
                                value="{{ old('selected_weekday', 'monday') }}">
                            <input type="hidden" name="selected_weekend" id="selected_weekend"
                                value="{{ old('selected_weekend', 'friday') }}">
                            <input type="hidden" name="apply_all_weekdays" id="apply_all_weekdays"
                                value="{{ old('apply_all_weekdays', 0) }}">
                            <input type="hidden" name="apply_all_weekends" id="apply_all_weekends"
                                value="{{ old('apply_all_weekends', 0) }}">
                        </div>
                    </div>

                    {{-- ---------- Submit Button ---------- --}}
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-save" id="submitBtn"><i
                                class="bi bi-save me-2"></i>{{ isset($package) ? 'Update Package' : 'Save Package' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('admin/js/multiple-image-upload.js') }}"></script>
    <script src="{{ asset('admin/js/gallery-manager.js') }}"></script>

    <script>
        $(function() {

            function updatePills($pills, value, all = false) {
                $pills.each(function() {
                    $(this).data('price', value);
                    if (all) $(this).text($(this).text().split(' - ')[0] + ' - ৳' + value);
                });
            }

            // Arrays to keep track of selected days
            let selectedWeekdays = $('#selected_weekday').val() ? $('#selected_weekday').val().split(',') : [];
            let selectedWeekends = $('#selected_weekend').val() ? $('#selected_weekend').val().split(',') : [];

            // Apply all weekdays toggle
            $('#applyAllWeekdays').change(function() {
                const checked = $(this).is(':checked');
                $('#apply_all_weekdays').val(checked ? 1 : 0);
                updatePills($('#weekdayPills .nav-link'), $('#weekdayPrice').val(), checked);
                $('#weekdayPills .nav-link').prop('disabled', checked);
                if (checked) {
                    selectedWeekdays = weekdays(); // select all weekdays
                    $('#selected_weekday').val(selectedWeekdays.join(','));
                }
            });

            // Apply all weekends toggle
            $('#applyAllWeekends').change(function() {
                const checked = $(this).is(':checked');
                $('#apply_all_weekends').val(checked ? 1 : 0);
                updatePills($('#weekendPills .nav-link'), $('#weekendPrice').val(), checked);
                $('#weekendPills .nav-link').prop('disabled', checked);
                if (checked) {
                    selectedWeekends = weekends(); // select all weekends
                    $('#selected_weekend').val(selectedWeekends.join(','));
                }
            });

            // Price input updates
            $('#weekdayPrice').on('input', function() {
                if ($('#applyAllWeekdays').is(':checked')) {
                    updatePills($('#weekdayPills .nav-link'), $(this).val(), true);
                } else {
                    $('#weekdayPills .nav-link.active').data('price', $(this).val());
                }
            });

            $('#weekendPrice').on('input', function() {
                if ($('#applyAllWeekends').is(':checked')) {
                    updatePills($('#weekendPills .nav-link'), $(this).val(), true);
                } else {
                    $('#weekendPills .nav-link.active').data('price', $(this).val());
                }
            });

            // Weekday pill click (multiple selection)
            $('#weekdayPills .nav-link').click(function() {
                if (!$('#applyAllWeekdays').is(':checked')) {
                    const day = $(this).data('day');
                    $(this).toggleClass('active');

                    if ($(this).hasClass('active')) {
                        if (!selectedWeekdays.includes(day)) selectedWeekdays.push(day);
                    } else {
                        selectedWeekdays = selectedWeekdays.filter(d => d !== day);
                    }

                    $('#selected_weekday').val(selectedWeekdays.join(','));
                }
            });

            // Weekend pill click (multiple selection)
            $('#weekendPills .nav-link').click(function() {
                if (!$('#applyAllWeekends').is(':checked')) {
                    const day = $(this).data('day');
                    $(this).toggleClass('active');

                    if ($(this).hasClass('active')) {
                        if (!selectedWeekends.includes(day)) selectedWeekends.push(day);
                    } else {
                        selectedWeekends = selectedWeekends.filter(d => d !== day);
                    }

                    $('#selected_weekend').val(selectedWeekends.join(','));
                }
            });

            // Submit button
            $('#submitBtn').click(function() {
                $(this).prop('disabled', true).addClass('btn-loading');
                $('#packageForm').submit();
            });

            // Helper arrays (optional, in case you don't have them in JS)
            function weekdays() {
                return ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];
            }

            function weekends() {
                return ['friday', 'saturday'];
            }

        });
    </script>
@endpush
