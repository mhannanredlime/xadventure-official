@extends('layouts.frontend')

@section('title', 'ATV/UTV Package Booking')

@push('styles')
    <style>
        .package-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .package-card img {
            max-height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }

        .price {
            font-weight: bold;
            font-size: 1.2rem;
            color: #28a745;
        }
    </style>
@endpush

@section('content')
    <div class="container py-5">
        <h2 class="mb-4">ATV / UTV Packages</h2>

        @foreach ($packages as $package)
            <div class="package-card row align-items-center">
                <div class="col-md-4">
                    <img src="{{ $package->display_image_url }}" alt="{{ $package->name }}" class="img-fluid">
                </div>
                <div class="col-md-8">
                    <h4>{{ $package->name }}</h4>
                    <p>{{ $package->subtitle ?? $package->description }}</p>

                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <label>Rider Type</label>
                            <select class="form-select rider-type" data-package-id="{{ $package->id }}">
                                @if ($package->single_price)
                                    <option value="1">Single Rider ({{ number_format($package->single_price, 2) }})
                                    </option>
                                @endif
                                @if ($package->double_price)
                                    <option value="2">Double Rider ({{ number_format($package->double_price, 2) }})
                                    </option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Day Type</label>
                            <select class="form-select day-type" data-package-id="{{ $package->id }}">
                                <option value="weekday" {{ $package->day_name == 'weekday' ? 'selected' : '' }}>Weekday
                                </option>
                                <option value="weekend" {{ $package->day_name == 'weekend' ? 'selected' : '' }}>Weekend
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Schedule Slot</label>
                            <select class="form-select schedule-slot">
                                @foreach ($scheduleSlots as $slot)
                                    <option value="{{ $slot->id }}">{{ $slot->start_time }} - {{ $slot->end_time }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <p class="price" id="price-{{ $package->id }}">
                        Price: {{ number_format($package->effective_price, 2) }}
                    </p>

                    <button class="btn btn-primary add-to-cart" data-package-id="{{ $package->id }}">Add to Cart</button>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Update price dynamically when rider type or day changes
            $('.rider-type, .day-type').on('change', function() {
                const packageId = $(this).data('package-id');
                const rider = $(`.rider-type[data-package-id="${packageId}"]`).val();
                const day = $(`.day-type[data-package-id="${packageId}"]`).val();

                const packageData = @json($package_data_json);

                const pkg = packageData.find(p => p.id == packageId);
                if (pkg) {
                    let price = pkg.price_data.display;
                    if (rider == 1) price = pkg.price_data.single;
                    else if (rider == 2) price = pkg.price_data.double;
                    $('#price-' + packageId).text('Price: ' + price.toFixed(2));
                }
            });

            // Add to cart button
            $('.add-to-cart').on('click', function() {
                const packageId = $(this).data('package-id');
                const rider = $(`.rider-type[data-package-id="${packageId}"]`).val();
                const day = $(`.day-type[data-package-id="${packageId}"]`).val();
                const slot = $(`.schedule-slot`).val();

                $.ajax({
                    url: "{{ route('cart.add') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        package_id: packageId,
                        rider_type: rider,
                        day_type: day,
                        schedule_slot: slot
                    },
                    success: function(res) {
                        alert('Added to cart successfully!');
                    },
                    error: function(err) {
                        console.log(err);
                        alert('Failed to add to cart.');
                    }
                });
            });
        });
    </script>
@endpush
