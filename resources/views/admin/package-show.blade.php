@extends('layouts.admin')

@section('title', 'Package Details')

@section('content')
    <div class="content-area">
        <div class="container-fluid">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="section-title">Package Details</h3>
                <a href="{{ route('admin.packege.list') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Packages
                </a>
            </div>

            {{-- Success Alert --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Main Card --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row g-4 align-items-stretch">
                        {{-- Package Image --}}
                        <div class="col-md-5 col-lg-4 text-center">
                            @php
                                $image =
                                    $package->display_image_url ??
                                    ($package->image_path
                                        ? asset('storage/' . $package->image_path)
                                        : asset('admin/images/pack-1.png'));
                            @endphp
                            <img src="{{ $image }}" class="img-fluid rounded-3 shadow-sm mb-3"
                                alt="{{ $package->name }}" style="max-height: 300px; object-fit: cover;">
                            <h4 class="text-primary mb-1">{{ $package->name }}</h4>
                            <p class="text-muted small">{{ $package->subtitle }}</p>
                        </div>

                        {{-- Package Info --}}
                        <div class="col-md-7 col-lg-8">
                            <div class="card card-body border-0 shadow-sm h-100">
                                <h5 class="mb-3 text-info"><i class="bi bi-info-circle me-2"></i>General Information</h5>
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-2">
                                        <p class="mb-1"><strong><i
                                                    class="bi bi-tag me-2 text-secondary"></i>Type:</strong> <span
                                                class="badge bg-primary">{{ ucfirst($package->type) }}</span></p>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <p class="mb-1"><strong><i
                                                    class="bi bi-people me-2 text-secondary"></i>Participants:</strong>
                                            {{ $package->min_participants }} - {{ $package->max_participants }}</p>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <p class="mb-1"><strong><i
                                                    class="bi bi-toggle-on me-2 text-secondary"></i>Status:</strong>
                                            <span class="badge {{ $package->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $package->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <p class="mb-1"><strong><i
                                                    class="bi bi-calendar-plus me-2 text-secondary"></i>Created:</strong>
                                            {{ $package->created_at->format('M j, Y') }}</p>
                                    </div>
                                    <div class="col-12">
                                        <p class="mb-0"><strong><i
                                                    class="bi bi-calendar-check me-2 text-secondary"></i>Last
                                                Updated:</strong> {{ $package->updated_at->format('M j, Y') }}</p>
                                    </div>
                                </div>

                                {{-- Description (if available) --}}
                                @if (!empty($package->description))
                                    <h5 class="mb-2 text-info"><i class="bi bi-file-text me-2"></i>Description</h5>
                                    <p class="text-muted small mb-4">{{ $package->description }}</p>
                                @endif

                                {{-- Actions --}}
                                <div class="mt-auto pt-3 border-top d-flex flex-wrap gap-2">
                                    @can('packages.manage')
                                        @if ($package->type === 'regular')
                                            <a href="{{ route('admin.packages.edit', $package->id) }}"
                                                class="btn btn-primary btn-sm">
                                                <i class="bi bi-pencil me-1"></i> Edit Package
                                            </a>
                                        @else
                                            <a href="{{ route('admin.atvutv-packege-management.edit', $package->id) }}"
                                                class="btn btn-primary btn-sm">
                                                <i class="bi bi-pencil me-1"></i> Edit Package
                                            </a>
                                        @endif
                                        <form action="{{ route('admin.packages.destroy', $package) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this package? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash me-1"></i> Delete Package
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Variants & Pricing --}}
            @if ($package->packagePrices->isNotEmpty())
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="mb-3"><i class="bi bi-currency-dollar me-2"></i>Day-wise Pricing</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>SL</th>
                                        <th>Day</th>
                                        <th>Type</th>
                                        <th>Price (৳)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($package->packagePrices as $key => $price)
                                        <tr>
                                            <td>{{ ++$key }}</td>
                                            <td>{{ strtoupper($price->day) }}</td>
                                            <td>{{ ucfirst($price->type) }}</td>
                                            <td>৳ {{ number_format($price->price) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
