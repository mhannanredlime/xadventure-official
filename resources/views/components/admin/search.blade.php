@props(['action', 'placeholder' => 'Search...'])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form action="{{ $action }}" method="GET">
            <div class="row g-3 align-items-end">
                {{ $slot }}

                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn jatio-bg-color text-white flex-fill shadow-sm">
                            <i class="bi bi-search me-2"></i>Search
                        </button>

                        <a href="{{ $action }}" class="btn btn-outline-secondary shadow-sm">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
