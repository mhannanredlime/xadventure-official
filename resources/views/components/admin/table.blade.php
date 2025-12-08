@props(['headers' => []])

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table {{ $attributes->merge(['class' => 'table table-hover mb-0 responsive-stacked align-middle']) }}>
                <thead class="bg-light">
                    <tr>
                        @foreach ($headers as $header)
                            <th class="py-3 px-4 fw-semibold text-secondary text-uppercase fs-7">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    {{ $slot }}
                </tbody>
            </table>
        </div>

        @if (isset($footer))
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
