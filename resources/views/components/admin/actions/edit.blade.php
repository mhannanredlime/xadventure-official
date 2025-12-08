@props(['route'])

<a href="{{ $route }}" class="btn btn-icon btn-light text-primary shadow-sm hover-scale" title="Edit">
    <i class="bi bi-pencil-square"></i>
</a>

<style>
    .hover-scale {
        transition: transform 0.2s;
    }

    .hover-scale:hover {
        transform: scale(1.1);
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }
</style>
