@extends('layouts.admin')

@section('title', 'Contact Messages')

@push('styles')
    <style>
        .message-box {
            max-width: 350px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .badge-subscriber {
            background-color: #0d6efd;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-area">

        <div class="dashboard-header mb-4">
            <h1>{{ $page_title }}</h1>
            <p class="text-muted">{{ $page_desc }}</p>

            <form method="GET" action="{{ url('admin/customer/contacts') }}" class="mt-3">
                <div class="d-flex gap-2" style="max-width: 400px;">
                    <input type="text" name="search" class="form-control" placeholder="Search name, email or subject"
                        value="{{ request('search') }}">
                    <button class="btn btn-primary">Search</button>
                    <a href="{{ url('admin/customer/contacts') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
        <!-- Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light fw-bold">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($items as $contact)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $contact->name }}</td>
                                <td>{{ $contact->email }}</td>
                                <td>{{ $contact->subject }}</td>
                                <td>
                                    <div class="message-box" title="{{ $contact->message }}">
                                        {{ $contact->message }}
                                    </div>
                                </td>

                                <td>{{ $contact->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5>No Messages Found</h5>
                                    <p class="text-muted">No contact messages match your search.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $items->withQueryString()->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection
