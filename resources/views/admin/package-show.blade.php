@extends('layouts.admin')

@section('title', 'Package Details')

@section('content')
    <div class="content-area">
        <div class="container-fluid">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="section-title">Package Details</h3>
                <a href="{{ route('admin.add-packege-management') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Packages
                </a>
            </div>

            {{-- Success Alert --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Main Card --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-4">
                        {{-- Package Image --}}
                        <div class="col-md-4 text-center">
                            @php
                                $image =
                                    $package->display_image_url ??
                                    ($package->image_path
                                        ? asset('storage/' . $package->image_path)
                                        : asset('admin/images/pack-1.png'));
                            @endphp
                            <img src="{{ $image }}" class="img-fluid rounded shadow-sm" alt="{{ $package->name }}">
                        </div>

                        {{-- Package Info --}}
                        <div class="col-md-8">
                            <h4 class="mb-1">{{ $package->name }}</h4>
                            <p class="text-muted mb-3">{{ $package->subtitle }}</p>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Type:</strong> {{ ucfirst($package->type) }}</p>
                                    <p class="mb-1"><strong>Min Participants:</strong> {{ $package->min_participants }}
                                    </p>
                                    <p class="mb-1"><strong>Max Participants:</strong> {{ $package->max_participants }}
                                    </p>
                                    <p class="mb-0"><strong>Status:</strong>
                                        <span class="badge {{ $package->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $package->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Created:</strong>
                                        {{ $package->created_at->format('M j, Y') }}</p>
                                    <p class="mb-0"><strong>Updated:</strong>
                                        {{ $package->updated_at->format('M j, Y') }}</p>
                                </div>
                            </div>

                            {{-- Variants & Pricing --}}
                            @if ($package->variants->isNotEmpty())
                                <div class="mt-4">
                                    <h5 class="mb-3">Variants & Pricing</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Variant</th>
                                                    <th>Capacity</th>
                                                    <th>Weekday Price</th>
                                                    <th>Weekend Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($package->variants as $variant)
                                                    <tr>
                                                        <td>{{ $variant->variant_name }}</td>
                                                        <td>{{ $variant->capacity }}</td>
                                                        <td>৳
                                                            {{ number_format($variant->prices->where('price_type', 'weekday')->first()->amount ?? 0) }}
                                                        </td>
                                                        <td>৳
                                                            {{ number_format($variant->prices->where('price_type', 'weekend')->first()->amount ?? 0) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div class="mt-4 d-flex flex-wrap gap-2">
                                @can('packages.manage')
                                    @if ($package->type === 'regular')
                                        <a href="{{ route('admin.regular-packege-management.edit', $package) }}"
                                            class="btn btn-primary">
                                            <i class="bi bi-edit me-1"></i> Edit Package
                                        </a>
                                    @else
                                        <a href="{{ route('admin.atvutv-packege-management.edit', $package) }}"
                                            class="btn btn-primary">
                                            <i class="bi bi-edit me-1"></i> Edit Package
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.packages.destroy', $package) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this package?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
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
    </div>
@endsection
