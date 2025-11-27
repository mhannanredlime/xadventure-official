@extends('layouts.admin')

@section('title', isset($package) ? 'Edit Regular Package' : 'Add Regular Package')

@section('content')


<div class="container py-4">

    <h3 class="mb-4 fw-bold">Create New Package</h3>

    <form action="#" method="POST" enctype="multipart/form-data" id="packageForm">
        @csrf

        <div class="row g-4">

            <!-- Basic Info -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <strong>Package Details</strong>
                    </div>

                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Package Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Subtitle</label>
                            <input type="text" name="subtitle" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Package Type</label>
                            <select name="type" class="form-select">
                                <option value="Single">Single</option>
                                <option value="Bundle">Bundle</option>
                                <option value="Group">Group</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Details</label>
                            <textarea name="details" class="form-control" rows="3"></textarea>
                        </div>

                    </div>
                </div>

                <!-- Image Upload -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-secondary text-white">
                        <strong>Package Image</strong>
                    </div>
                    <div class="card-body">
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>

            <!-- Pricing Panel -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <strong>Package Pricing (Day-wise)</strong>
                    </div>

                    <div class="card-body">

                        <!-- Active Days Select -->
                        <label class="fw-semibold mb-2">Select Active Days</label>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @php
                                $days = ['mon','tue','wed','thu','fri','sat','sun'];
                            @endphp

                            @foreach($days as $day)
                                <div class="form-check form-check-inline day-pill">
                                    <input type="checkbox"
                                           class="btn-check day-checkbox"
                                           id="day-{{ $day }}"
                                           value="{{ $day }}"
                                           autocomplete="off">
                                    <label class="btn btn-outline-primary btn-sm" for="day-{{ $day }}">
                                        {{ strtoupper($day) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <!-- Price List -->
                        <div id="priceContainer"></div>

                        <!-- Apply To All -->
                        <div class="mt-3">
                            <label class="fw-semibold">Apply Same Price to All</label>
                            <input type="number" class="form-control" id="applyAllPrice" placeholder="e.g. 1200">
                            <button type="button" class="btn btn-sm btn-dark mt-2" id="applyAllBtn">
                                Apply
                            </button>
                        </div>

                        <!-- Hidden JSON fields -->
                        <input type="hidden" name="active_days" id="activeDaysInput">
                        <input type="hidden" name="day_prices" id="dayPricesInput">

                    </div>
                </div>
            </div>

        </div>

        <div class="text-end mt-4">
            <button class="btn btn-lg btn-primary px-4">Save Package</button>
        </div>

    </form>
</div>
@endsection


@push('scripts')
<script>
    const priceContainer = $("#priceContainer");
    const selectedDays = new Set();
    const prices = {};

    // --- Update Price Inputs ---
    function renderPriceInputs() {
        priceContainer.empty();

        [...selectedDays].forEach(day => {
            priceContainer.append(`
                <div class="mb-2">
                    <label class="form-label fw-semibold text-uppercase">${day} Price</label>
                    <input type="number" class="form-control price-input" data-day="${day}"
                           value="${prices[day] ?? ''}" placeholder="Enter price">
                </div>
            `);
        });
    }

    // --- When selecting days ---
    $(".day-checkbox").on("change", function () {
        const day = $(this).val();

        if (this.checked) {
            selectedDays.add(day);
        } else {
            selectedDays.delete(day);
            delete prices[day];
        }

        renderPriceInputs();
    });

    // --- Update internal prices map ---
    $(document).on("input", ".price-input", function () {
        const day = $(this).data("day");
        prices[day] = $(this).val();
    });

    // --- Apply to all ---
    $("#applyAllBtn").on("click", function () {
        const val = $("#applyAllPrice").val();

        if (!val) return;

        [...selectedDays].forEach(day => {
            prices[day] = val;
        });

        renderPriceInputs();
    });

    // --- Before submit ---
    $("#packageForm").on("submit", function () {
        $("#activeDaysInput").val(JSON.stringify([...selectedDays]));
        $("#dayPricesInput").val(JSON.stringify(prices));
    });
</script>
@endpush
