@extends('layouts.admin')

@section('title', 'Package Management')

@section('content')
    <div class="dashboard-area">
        <!-- Header Section -->
        <div class="row align-items-center mb-4">
            <div class="col">
                <h1 class="h3 mb-2 text-gray-800">{{ $page_title }}</h1>
                <p class="text-muted mb-0">{{ $page_desc }}</p>
            </div>
            <div class="col-auto">
                <span class="badge bg-primary rounded-pill shadow-sm px-3 py-2">
                    <i class="bi bi-box-seam me-1"></i> {{ $items->count() }} packages
                </span>
            </div>
        </div>

        <!-- Search & Filter Section -->
        <x-admin.search action="{{ url('admin/packege/list') }}">
            <!-- Search -->
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0"
                        placeholder="Search package name..." value="{{ request('search') }}">
                </div>
            </div>

            <!-- Package Type -->
            <div class="col-md-3">
                <select name="package_type_id" class="form-select">
                    <option value="">All Types</option>
                    @foreach ($packageTypes as $type)
                        <option value="{{ $type->id }}" {{ request('package_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </x-admin.search>

        <!-- Package List Table -->
        <x-admin.table :headers="['SL', 'Main Image', 'Name', 'Price / Avg', 'Status', 'Actions']">
            @forelse($items as $key => $package)
                <tr>
                    <td class="fw-bold text-secondary">#{{ ++$key }}</td>
                    <td>
                        <div class="avatar avatar-lg rounded overflow-hidden shadow-sm border">
                            @if ($package->primaryImageUrl)
                                <img src="{{ $package->primaryImageUrl }}" class="img-cover w-100 h-100"
                                    alt="{{ $package->name }}">
                            @elseif($package->image_path)
                                <img src="{{ asset('storage/' . $package->image_path) }}" class="img-cover w-100 h-100"
                                    alt="{{ $package->name }}">
                            @else
                                <img src="{{ asset('admin/images/pack-1.png') }}" class="img-cover w-100 h-100"
                                    alt="{{ $package->name }}">
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark">{{ $package->name }}</span>
                            <small class="text-muted">{{ Str::limit($package->subtitle, 40) }}</small>
                            <small class="text-xs text-uppercase mt-1 text-primary">{{ $package->type }}</small>
                        </div>
                    </td>
                    <td>
                        @if ($package->type == 'regular')
                            <span class="fw-bold text-success fs-6">৳ {{ $package->display_starting_price }}</span>
                        @else
                            <div class="d-flex flex-column align-items-start">
                                <span class="badge bg-primary-subtle text-primary mb-1">Avg Price</span>
                                <span class="fw-bold">৳ {{ atvPackageAvgPrice($package) }}</span>
                            </div>
                        @endif
                    </td>

                    <td>
                        <x-admin.badge :type="$package->is_active ? 'success' : 'danger'" :text="$package->is_active ? 'Active' : 'Inactive'" />
                    </td>

                    <td>
                        <div class="d-flex gap-2">
                            @if ($package->type == 'regular')
                                <x-admin.actions.edit route="{{ route('admin.packages.edit', $package->id) }}" />
                            @elseif ($package->type == 'atv')
                                <x-admin.actions.edit
                                    route="{{ route('admin.atvutv-packege-management.edit', $package->id) }}" />
                            @endif

                            <x-admin.actions.view route="{{ route('admin.packages.show', $package->id) }}" />

                            <x-admin.actions.delete route="{{ route('admin.packages.destroy', $package->id) }}" />
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                            <i class="bi bi-inbox fs-1 mb-2 opacity-50"></i>
                            <p class="mb-0">No packages found regarding your search.</p>
                        </div>
                    </td>
                </tr>
            @endforelse

            <x-slot name="footer">
                @if ($items->hasPages())
                    <div class="d-flex justify-content-end">
                        {{ $items->links() }}
                    </div>
                @endif
            </x-slot>
        </x-admin.table>
    </div>

    <!-- Extra Styles for this page specifically -->
    @push('styles')
        <style>
            .avatar-lg {
                width: 60px;
                height: 60px;
                object-fit: cover;
            }

            .img-cover {
                object-fit: cover;
            }

            .form-select,
            .form-control {
                border-radius: 8px;
                border-color: #dee2e6;
            }

            .form-select:focus,
            .form-control:focus {
                border-color: #ff6600;
                box-shadow: 0 0 0 0.25rem rgba(255, 102, 0, 0.15);
            }
        </style>
    @endpush
@endsection
