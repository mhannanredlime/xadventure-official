@props(['type' => 'success', 'text'])

@php
    $bgClass = match ($type) {
        'success', 'active' => 'bg-success-subtle text-success',
        'danger', 'inactive' => 'bg-danger-subtle text-danger',
        'warning', 'pending' => 'bg-warning-subtle text-warning',
        'info' => 'bg-info-subtle text-info',
        'primary' => 'bg-primary-subtle text-primary',
        default => 'bg-secondary-subtle text-secondary',
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge rounded-pill ' . $bgClass . ' px-3 py-2 fw-medium']) }}>
    {{ $text }}
</span>
