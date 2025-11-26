@extends('layouts.admin')

@section('title', 'Contact Messages')

@push('styles')
<style>
.message-preview {
    max-width: 250px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer;
    transition: color 0.2s ease;
}

.message-preview:hover {
    color: var(--bs-primary);
}

.table-container {
    background: var(--bs-body-bg);
    border-radius: 0.75rem;
    box-shadow: 0 0.125rem 0.75rem rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.table th {
    border-bottom: 2px solid var(--bs-border-color);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: var(--bs-secondary);
    padding: 1rem 0.75rem;
    background: var(--bs-body-bg);
}

.table td {
    padding: 1rem 0.75rem;
    border-bottom: 1px solid var(--bs-border-color);
    vertical-align: middle;
}

.table tbody tr {
    transition: background-color 0.15s ease;
}

.table tbody tr:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.03);
}

.empty-state {
    padding: 3rem 1rem;
    text-align: center;
    color: var(--bs-secondary);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.date-cell {
    white-space: nowrap;
    font-size: 0.875rem;
    color: var(--bs-secondary);
}

.email-cell {
    font-family: monospace;
    font-size: 0.875rem;
}
</style>
@endpush

@section('content')
<div class="dashboard-area">
    <!-- Header Section -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 mb-2">{{ $page_title }}</h1>
            <p class="text-muted mb-0">{{ $page_desc }}</p>
        </div>
        <div class="col-auto">
            <span class="badge bg-primary rounded-pill">{{ $items->total() }} messages</span>
        </div>
    </div>

    <!-- Search Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ url('admin/customer/contacts') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label small text-muted mb-1">Search messages</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="Search by name, email, or subject..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn jatio-bg-color text-white flex-fill">
                                <i class="bi bi-search me-2"></i>Search
                            </button>
                            <a href="{{ url('admin/customer/contacts') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-times me-2"></i>Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="60">#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th width="120">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items  as  $key => $contact)
                            <tr>
                                <td class="fw-semibold text-muted">{{ ++$key }}</td>
                                <td>{{ $contact->name }}</td>
                                <td>
                                    <a href="mailto:{{ $contact->email }}" class="text-decoration-none">
                                        {{ $contact->email }}
                                    </a>
                                </td>
                                <td>{{ $contact->subject }}</td>
                                <td>

                                        {{ $contact->message }}

                                </td>
                                <td>
                                    {{ $contact->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <h5 class="mb-2">No Messages Found</h5>
                                        <p class="text-muted mb-0">No contact messages match your search criteria.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($items->hasPages())
    <div class="mt-4 d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} results
        </div>
        <div>
            {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(el) {
        return new bootstrap.Tooltip(el);
    });
});
</script>
@endpush
