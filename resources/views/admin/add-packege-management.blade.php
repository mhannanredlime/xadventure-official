@extends('layouts.admin')

@section('title', 'Package Management')

@section('content')
    <div class="dashboard-area">
        <!-- Header Section -->
        <div class="row align-items-center mb-4">
            <div class="col">
                <h1 class="h3 mb-2">{{ $page_title }}</h1>
                <p class="text-muted mb-0">{{ $page_desc }}</p>
            </div>
            <div class="col-auto">
                <span class="badge bg-primary rounded-pill">{{ $items->total() }} package</span>
            </div>
        </div>

        <!-- Search & Filter Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ url('admin/packege/list') }}" method="GET">
                    <div class="row g-3">

                        <!-- Search -->
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search package name..."
                                value="{{ request('search') }}">
                        </div>

                        <!-- Package Type -->
                        <div class="col-md-3">
                            <select name="package_type_id" class="form-select">
                                <option value="">All Types</option>
                                @foreach ($packageTypes as $type)
                                    <option value="{{ $type->id }}"
                                        {{ request('package_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn jatio-bg-color text-white flex-fill">
                                    <i class="bi bi-search me-2"></i>Search
                                </button>

                                <a href="{{ url('admin/packege/list') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg me-2"></i>Clear
                                </a>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <!-- Package List Table -->
        <div class="card border-0 shadow-sm">

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 responsive-stacked">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Main Image</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $key => $package)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>
                                        @if ($package->primaryImageUrl)
                                            <img src="{{ $package->primaryImageUrl }}" class="package-image"
                                                alt="{{ $package->name }}">
                                        @elseif($package->image_path)
                                            <img src="{{ asset('storage/' . $package->image_path) }}" class="package-image"
                                                alt="{{ $package->name }}">
                                        @else
                                            <img src="{{ asset('admin/images/pack-1.png') }}" class="package-image"
                                                alt="{{ $package->name }}">
                                        @endif
                                    </td>
                                    <td>
                                        {{ $package->name }} <br>
                                        <small>{{ $package->subtitle }}</small>
                                    </td>
                                    <td>৳ {{ $package->display_starting_price }}</td>
                                    <td><span
                                            class="rounded-pill badge {{ $package->is_active ? 'bg-success' : 'bg-danger' }}">{{ $package->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.packages.edit', $package->id) }}" class="btn btn-link p-0"
                                            title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <a href="{{ route('admin.packages.show', $package->id) }}" class="btn btn-link p-0"
                                            title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <form action="{{ route('admin.packages.destroy', $package->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link p-0 text-danger"
                                                onclick="return confirm('Are you sure you want to delete this package?');"
                                                title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">No items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($items->hasPages())
                    <div class="card-footer clearfix">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    </div>
@endsection
