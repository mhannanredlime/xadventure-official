@props(['href', 'text', 'icon' => null, 'can' => null])

@if (!$can || auth()->user()->can($can) || auth()->user()->hasRole('master-admin'))
    <a href="{{ $href }}"
        {{ $attributes->merge(['class' => 'btn d-inline-flex align-items-center justify-content-center']) }}>
        @if ($icon)
            <i class="bi {{ $icon }} me-2"></i>
        @endif
        {{ $text }}
    </a>
@endif
