@push('scripts')
    <script>
        $(function() {
            const weekendDays = {!! json_encode($weekendDays ?? ['fri', 'sat']) !!};
            const riderTypes = {!! $riderTypes->toJson() !!};

            let activeDays = JSON.parse($('#activeDaysInput').val());

            // Convert flat dayPrices into object keyed by day
            let prices = {};
            @if (!empty($dayPrices))
                @foreach ($dayPrices as $p)
                    @php
                        $day = $p['day'];
                        $riderId = $p['rider_type_id'];
                        $price = $p['price'] ?? 'null';
                    @endphp
                    if (!prices['{{ $day }}']) prices['{{ $day }}'] = [];
                    prices['{{ $day }}'].push({
                        rider_type_id: {{ $riderId }},
                        price: {{ $price }}
                    });
                @endforeach
            @endif

            // Apply All
            $(document).on('input', '.apply-all-rider', function() {
                const riderId = $(this).data('rider');
                const val = $(this).val();
                $(`.day-rider-price[data-rider="${riderId}"]`).each(function() {
                    $(this).val(val).trigger('input');
                });
            });

            // Update individual price
            $(document).on('input', '.day-rider-price', function() {
                const day = $(this).data('day');
                const riderId = $(this).data('rider');
                const val = $(this).val() === '' ? null : Number($(this).val());
                if (!prices[day]) prices[day] = [];
                const idx = prices[day].findIndex(p => p.rider_type_id == riderId);
                if (val === null || isNaN(val)) {
                    if (idx !== -1) prices[day].splice(idx, 1);
                } else {
                    if (idx !== -1) prices[day][idx].price = val;
                    else prices[day].push({
                        rider_type_id: riderId,
                        price: val
                    });
                }
            });

            // Clear single price
            $(document).on('click', '.clear-price-btn', function() {
                $(this).prev('.day-rider-price').val('').trigger('input');
            });

            // Remove day row
            $(document).on('click', '.remove-day-row', function() {
                const day = $(this).data('day');
                $(`tr[data-day-row="${day}"]`).remove();
                delete prices[day];
                activeDays = activeDays.filter(d => d !== day);
                $('#activeDaysInput').val(JSON.stringify(activeDays));
            });

            // Submit
            $('#submitBtn').on('click', function(e) {
                e.preventDefault();
                const dayPricesArray = [];
                activeDays.forEach(day => {
                    if (prices[day] && Array.isArray(prices[day])) {
                        prices[day].forEach(p => {
                            if (p.price !== null && !isNaN(p.price) && p.rider_type_id) {
                                dayPricesArray.push({
                                    day: day,
                                    rider_type_id: p.rider_type_id,
                                    price: p.price,
                                    type: weekendDays.includes(day) ? 'weekend' :
                                        'weekday'
                                });
                            }
                        });
                    }
                });
                $('#dayPricesInput').val(JSON.stringify(dayPricesArray));
                $(this).prop('disabled', true).addClass('btn-loading');
                $('#packageForm')[0].submit();
            });

            // Initialize missing combos ONLY for active days
            activeDays.forEach(day => {
                if (!prices[day]) prices[day] = [];
                riderTypes.forEach(rider => {
                    if (!prices[day].some(p => p.rider_type_id == rider.id)) {
                        prices[day].push({
                            rider_type_id: rider.id,
                            price: null
                        });
                    }
                });
            });
        });
    </script>
@endpush
