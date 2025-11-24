@extends('layouts.admin')

@section('title', 'Package Management')

@section('content')
  <div class="content-area">
    <div class="container-fluid package-container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title">Packege Management</h3>
        @can('packages.manage')
        <div class="dropdown">
          <button class="btn jatio-bg-color text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">Add New Package</button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ url('/admin/regular-packege-management') }}">Add Regular Package</a></li>
            <li><a class="dropdown-item" href="{{ url('/admin/atvutv-packege-management') }}">Add ATV/UTV Package</a></li>
          </ul>
        </div>
        @endcan
      </div>
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle"></i> {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      @php
        $hasAnyRegularPackages = $groupedRegularPackages['Single']->isNotEmpty() || 
                                 $groupedRegularPackages['Bundle']->isNotEmpty() || 
                                 $groupedRegularPackages['Group']->isNotEmpty();
      @endphp
      
      @if($hasAnyRegularPackages)
        @foreach(['Single', 'Bundle', 'Group'] as $category)
          @if($groupedRegularPackages[$category]->isNotEmpty())
            <h3 class="section-title">{{ $category }} Packages</h3>
            <div class="row">
              @foreach($groupedRegularPackages[$category] as $package)
                <div class="col-md-6">
                  <div class="package-card">
                    @if($package->primaryImageUrl)
                      <img src="{{ $package->primaryImageUrl }}" class="package-image" alt="{{ $package->name }}">
                    @elseif($package->image_path)
                      <img src="{{ asset('storage/' . $package->image_path) }}" class="package-image" alt="{{ $package->name }}">
                    @else
                      <img src="{{ asset('admin/images/pack-1.png') }}" class="package-image" alt="{{ $package->name }}">
                    @endif
                    <div class="package-details">
                      <h5>{{ $package->name }}</h5>
                      <p class="description">{{ $package->subtitle ?? '30 (min) Guided Tour' }}</p>
                      @if($package->variants->isNotEmpty())
                        @php
                          $minPrice = $package->variants->flatMap->prices->min('amount');
                        @endphp
                        <p class="price">৳ {{ number_format($minPrice) }}</p>
                      @else
                        <p class="price">৳ 0</p>
                      @endif
                    </div>
                    <div class="card-icons">
                      <a href="{{ route('admin.regular-packege-management.edit', $package) }}" title="Edit">
                        <i class="fa-solid fa-pen"></i>
                      </a>
                      <a href="{{ route('admin.packages.show', $package) }}" title="View Details">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                      </a>
                      <form action="{{ route('admin.packages.destroy', $package) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this package?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-link p-0" title="Delete">
                          <i class="fa-solid fa-trash text-danger"></i>
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        @endforeach
      @else
        <div class="text-center py-4">
          <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
          <h5>No Regular Packages Found</h5>
          <p class="text-muted">Create your first regular package to get started.</p>
        </div>
      @endif

      <h3 class="section-title mt-4">ATV/UTV Package</h3>
      <div class="row">
        @forelse($packages->whereIn('type', ['atv', 'utv']) as $package)
          <div class="col-md-6">
            <div class="package-card">
              @if($package->display_image_url)
                <img src="{{ $package->display_image_url }}" class="package-image" alt="{{ $package->name }}">
              @elseif($package->image_path)
                <img src="{{ asset('storage/' . $package->image_path) }}" class="package-image" alt="{{ $package->name }}">
              @else
                <img src="{{ asset('admin/images/pack-2.png') }}" class="package-image" alt="{{ $package->name }}">
              @endif
              <div class="package-details">
                <h5>{{ $package->name }}</h5>
                <p class="description">{{ $package->subtitle ?? '30 Minutes off road Trail Riding' }}</p>
                @if($package->variants->isNotEmpty())
                  <p class="price-label">Weekdays Price</p>
                  @foreach($package->variants->take(2) as $variant)
                    @php
                      $weekdayPrice = $variant->prices->where('price_type', 'weekday')->first();
                    @endphp
                    @if($weekdayPrice)
                      <p class="price-item">{{ $variant->variant_name }}: <span>৳ {{ number_format($weekdayPrice->amount) }}</span></p>
                    @endif
                  @endforeach
                  <p class="price-label">Weekend Price</p>
                  @foreach($package->variants->take(2) as $variant)
                    @php
                      $weekendPrice = $variant->prices->where('price_type', 'weekend')->first();
                    @endphp
                    @if($weekendPrice)
                      <p class="price-item">{{ $variant->variant_name }}: <span>৳ {{ number_format($weekendPrice->amount) }}</span></p>
                    @endif
                  @endforeach
                @else
                  <p class="price-item">No pricing available</p>
                @endif
              </div>
              <div class="card-icons">
                @can('packages.manage')
                <a href="{{ route('admin.atvutv-packege-management.edit', $package) }}" title="Edit">
                  <i class="fa-solid fa-pen"></i>
                </a>
                @endcan
                <a href="{{ route('admin.packages.show', $package) }}" title="View Details">
                  <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
                @can('packages.manage')
                <form action="{{ route('admin.packages.destroy', $package) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this package?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-link p-0" title="Delete">
                    <i class="fa-solid fa-trash text-danger"></i>
                  </button>
                </form>
                @endcan
              </div>
            </div>
          </div>
        @empty
          <div class="col-12">
            <div class="text-center py-4">
              <i class="fas fa-motorcycle fa-3x text-muted mb-3"></i>
              <h5>No ATV/UTV Packages Found</h5>
              <p class="text-muted">Create your first ATV/UTV package to get started.</p>
            </div>
          </div>
        @endforelse
      </div>
    </div>
  </div>
@endsection


