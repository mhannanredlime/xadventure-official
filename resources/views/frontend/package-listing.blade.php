@extends('layouts.frontend')

@section('title', 'Packages')

@push('styles')
  <link rel="stylesheet" href="{{ asset('frontEnd/css/package-listing.css') }}">
  <style>
    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
    .success-message {
      background: #d4edda;
      color: #155724;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
    .package-card {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      transition: box-shadow 0.3s ease;
    }
    .package-card:hover {
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .price-badge {
      background: #ff6b35;
      color: white;
      padding: 5px 10px;
      border-radius: 20px;
      font-weight: bold;
    }
  </style>
@endpush

@section('content')
  <div class="container main-container">
    <h1>ATV/UTV Trail Rides</h1>

    @if(session('error'))
      <div class="error-message">
        <i class="bi  bi-exclamation-triangle"></i>
        {{ session('error') }}
      </div>
    @endif

    @if(session('success'))
      <div class="success-message">
        <i class="bi  bi-check-circle"></i>
        {{ session('success') }}
      </div>
    @endif

    @if($errors->any())
      <div class="error-message">
        <i class="bi  bi-exclamation-triangle"></i>
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if(isset($packages) && $packages->isEmpty())
      <div class="text-center py-5">
        <i class="bi  bi-info-circle fa-3x text-muted mb-3"></i>
        <h3>No packages available</h3>
        <p class="text-muted">Please check back later for available adventure packages.</p>
      </div>
    @elseif(isset($packages))
      <div class="row">
        @foreach($packages as $package)
          <div class="col-lg-6 col-md-6 mb-4">
            <div class="package-card">
              @if($package->display_image_url)
                <img src="{{ $package->display_image_url }}" alt="{{ $package->name }}" class="img-fluid mb-3" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
              @else
                <div class="bg-light d-flex align-items-center justify-content-center mb-3" style="width: 100%; height: 200px; border-radius: 8px;">
                  <i class="bi  bi-mountain fa-3x text-muted"></i>
                </div>
              @endif

              <h3 class="h5 mb-2">{{ $package->name }}</h3>
              <p class="text-muted mb-3">{{ Str::limit($package->description, 100) }}</p>

              <div class="mb-3">
                <strong>Available for:</strong>
                @foreach($package->vehicleTypes as $vehicleType)
                  <span class="badge bg-primary me-1">{{ $vehicleType->name }}</span>
                @endforeach
              </div>

              <div class="mb-3">
                <strong>Starting from:</strong>
                @if($package->variants->isNotEmpty())
                  @php
                    $minPrice = $package->variants->flatMap->prices->min('amount');
                  @endphp
                  <span class="price-badge">TK {{ number_format($minPrice) }}</span>
                @else
                  <span class="text-muted">Price not available</span>
                @endif
              </div>

              <a href="{{ route('frontend.packages.show', $package) }}" class="btn btn-orange jatio-bg-color">
                View Details & Book
              </a>
            </div>
          </div>
        @endforeach
      </div>
    @else

    <div class="accordion" id="sessionAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Session 1
          </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#sessionAccordion">
          <div class="accordion-body">
            <div class="mb-5">
              <h3 class="section-heading">Choose Date</h3>
              <div class="calendar-container">
                <div class="calendar">
                  <div class="calendar-header">
                    <i class="bi bi-chevron-left nav-arrow"></i>
                    <span class="month">February 2022</span>
                  </div>
                  <div class="calendar-grid">
                    <span class="day-name">Su</span><span class="day-name">Mo</span><span class="day-name">Tu</span><span class="day-name">We</span><span class="day-name">Th</span><span class="day-name">Fr</span><span class="day-name">Sa</span>
                    <span class="day empty"></span><span class="day empty"></span><span class="day selected">1</span><span class="day">2</span><span class="day">3</span><span class="day">4</span><span class="day">5</span>
                    <span class="day">6</span><span class="day">7</span><span class="day">8</span><span class="day">9</span><span class="day">10</span><span class="day">11</span><span class="day">12</span>
                    <span class="day">13</span><span class="day">14</span><span class="day">15</span><span class="day">16</span><span class="day">17</span><span class="day">18</span><span class="day">19</span>
                    <span class="day">20</span><span class="day">21</span><span class="day">22</span><span class="day">23</span><span class="day">24</span><span class="day">25</span><span class="day">26</span>
                    <span class="day">27</span><span class="day">28</span>
                  </div>
                </div>
                <div class="calendar">
                  <div class="calendar-header">
                    <span class="month">March 2022</span>
                    <i class="bi bi-chevron-right nav-arrow"></i>
                  </div>
                  <div class="calendar-grid">
                    <span class="day-name">Su</span><span class="day-name">Mo</span><span class="day-name">Tu</span><span class="day-name">We</span><span class="day-name">Th</span><span class="day-name">Fr</span><span class="day-name">Sa</span>
                    <span class="day empty"></span><span class="day empty"></span><span class="day">1</span><span class="day">2</span><span class="day">3</span><span class="day">4</span><span class="day">5</span>
                    <span class="day">6</span><span class="day">7</span><span class="day">8</span><span class="day">9</span><span class="day">10</span><span class="day">11</span><span class="day">12</span>
                    <span class="day">13</span><span class="day">14</span><span class="day">15</span><span class="day">16</span><span class="day">17</span><span class="day">18</span><span class="day">19</span>
                    <span class="day">20</span><span class="day">21</span><span class="day">22</span><span class="day">23</span><span class="day">24</span><span class="day">25</span><span class="day">26</span>
                    <span class="day">27</span><span class="day">28</span><span class="day">29</span><span class="day">30</span><span class="day">31</span>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <h3 class="section-heading">Select Vehicle</h3>
              <div class="row g-4">
                <div class="col-lg-6">
                  <div class="vehicle-card" data-vehicle="atv">
                    <div class="vehicle-header">
                      <div class="vehicle-info">
                        <div class="number">2</div>
                        <div class="type">ATV</div>
                        <div class="availability">Available</div>
                        <div class="">Max 2 Person Can Ride This</div><br>
                        <p class="vehicle-license-note">* Motorcycle license required</p>
                      </div>
                      <img src="{{ asset('frontEnd/images/atv-transparent.svg') }}" alt="Red ATV" class="vehicle-image">
                    </div>
                    <div class="rider-options">
                      <div class="rider-option-card">
                        <span class="rider-icon"><img src="{{ asset('frontEnd/images/Helmet.svg') }}" height="20px" alt=""></span>
                        <div class="rider-title">ATV Single Rider</div>
                        <div class="rider-desc">1 person can ride this</div>
                        <div class="price"><span class="original">TK 1500</span> TK 1200</div>
                      </div>
                      <div class="rider-option-card">
                        <span class="rider-icon">
                          <img src="{{ asset('frontEnd/images/Helmet.svg') }}" height="20px" alt="">
                          <img src="{{ asset('frontEnd/images/Helmet.svg') }}" height="20px" alt="">
                        </span>
                        <div class="rider-title">ATV Double Rider</div>
                        <div class="rider-desc">2 person can ride this</div>
                        <div class="price">2000 TK</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="vehicle-card" data-vehicle="utv">
                    <div class="vehicle-header">
                      <div class="vehicle-info">
                        <div class="number">2</div>
                        <div class="type">UTV</div>
                        <div class="availability">Available</div>
                        <div class="">Max 2 Person Can Ride This</div><br>
                        <p class="vehicle-license-note">* Motorcycle license required</p>
                      </div>
                      <img src="{{ asset('frontEnd/images/utv-transparent.svg') }}" alt="Yellow UTV" class="vehicle-image">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <a href="{{ url('/payment') }}" class="btn btn-add-to-cart w-25"> Pay Now</a>
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>
@endsection


