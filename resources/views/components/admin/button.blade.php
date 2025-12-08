@props([
    'route' => null,
    'type' => 'button',
    'color' => 'primary',
    'size' => '',
    'icon' => null,
    'confirm' => null,
])

@php
    $baseClass = "btn btn-{$color} {$size}";
    // If it's a specific "jatio" color requested or standard bs
if ($color === 'jatio-bg-color') {
        $baseClass = "btn {$color} text-white {$size}";
    }
@endphp

@if ($route)
    <a href="{{ $route }}" {{ $attributes->merge(['class' => $baseClass]) }}>
        @if ($icon)
            <i class="{{ $icon }} me-2"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClass]) }}
        @if ($confirm) onclick="return confirm('{{ $confirm }}')" @endif>
        @if ($icon)
            <i class="{{ $icon }} me-2"></i>
        @endif
        {{ $slot }}
    </button>
@endif
