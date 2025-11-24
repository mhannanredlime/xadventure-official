@extends('layouts.admin')

@section('title', 'Package Details')

@section('content')
  <div class="content-area">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title">Package Details</h3>
        <a href="{{ route('admin.add-packege-management') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Back to Packages
        </a>
      </div>

      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle"></i> {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              @if($package->display_image_url)
                <img src="{{ $package->display_image_url }}" class="img-fluid rounded" alt="{{ $package->name }}">
              @elseif($package->image_path)
                <img src="{{ asset('storage/' . $package->image_path) }}" class="img-fluid rounded" alt="{{ $package->name }}">
              @else
                <img src="{{ asset('admin/images/pack-1.png') }}" class="img-fluid rounded" alt="{{ $package->name }}">
              @endif
            </div>
            <div class="col-md-8">
              <h4>{{ $package->name }}</h4>
              <p class="text-muted">{{ $package->subtitle }}</p>
              
              <div class="row mt-3">
                <div class="col-md-6">
                  <strong>Type:</strong> {{ ucfirst($package->type) }}<br>
                  <strong>Min Participants:</strong> {{ $package->min_participants }}<br>
                  <strong>Max Participants:</strong> {{ $package->max_participants }}<br>
                  <strong>Status:</strong> 
                  <span class="badge {{ $package->is_active ? 'bg-success' : 'bg-danger' }}">
                    {{ $package->is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
                <div class="col-md-6">
                  <strong>Created:</strong> {{ $package->created_at->format('M j, Y') }}<br>
                  <strong>Updated:</strong> {{ $package->updated_at->format('M j, Y') }}
                </div>
              </div>

              @if($package->variants->isNotEmpty())
                <div class="mt-4">
                  <h5>Variants & Pricing</h5>
                  <div class="table-responsive">
                    <table class="table table-bordered responsive-stacked">
                      <thead>
                        <tr>
                          <th>Variant</th>
                          <th>Capacity</th>
                          <th>Weekday Price</th>
                          <th>Weekend Price</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($package->variants as $variant)
                          <tr>
                            <td>{{ $variant->variant_name }}</td>
                            <td>{{ $variant->capacity }}</td>
                            <td>৳ {{ number_format($variant->prices->where('price_type', 'weekday')->first()->amount ?? 0) }}</td>
                            <td>৳ {{ number_format($variant->prices->where('price_type', 'weekend')->first()->amount ?? 0) }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              @endif

              <div class="mt-4">
                @can('packages.manage')
                @if($package->type === 'regular')
                  <a href="{{ route('admin.regular-packege-management.edit', $package) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Package
                  </a>
                @else
                  <a href="{{ route('admin.atvutv-packege-management.edit', $package) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Package
                  </a>
                @endif
                @endcan
                
                @can('packages.manage')
                <form action="{{ route('admin.packages.destroy', $package) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this package?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete Package
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
