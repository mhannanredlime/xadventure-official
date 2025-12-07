@extends('layouts.frontend')

@section('title', 'Admin Login')

@push('styles')
<style>
  /* Fix login page header issues */
  .navbar {
    position: relative !important;
    background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.9)) !important;
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 10px 0;
  }
  
  .navbar-brand {
    padding: 10px 0;
    display: flex;
    align-items: center;
  }
  
  .navbar-brand img {
    max-height: 40px;
    width: auto;
  }
  
  /* Remove any unwanted elements */
  .navbar .fa-caret-down,
  .navbar .fa-chevron-down,
  .navbar .dropdown-toggle::after {
    display: none !important;
  }
  
  /* Ensure proper spacing */
  .navbar .container {
    padding: 0 15px;
  }
  
  /* Fix button styling */
  .btn-outline-light {
    border-color: rgba(255, 255, 255, 0.3);
    color: white;
  }
  
  .btn-outline-light:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.5);
  }
  
  /* Ensure proper z-index */
  .navbar {
    z-index: 1030 !important;
  }
  
  /* Fix navbar collapse on mobile */
  @media (max-width: 991.98px) {
    .navbar-collapse {
      background: rgba(0, 0, 0, 0.95);
      margin-top: 10px;
      border-radius: 8px;
      padding: 15px;
    }
  }
  
  /* Remove any floating elements */
  .navbar::before,
  .navbar::after {
    display: none !important;
  }
  
  /* Ensure logo displays properly */
  .navbar-brand img {
    display: block;
    max-height: 40px;
    width: auto;
    object-fit: contain;
  }
  
  /* Fix any potential navbar alignment issues */
  .navbar-nav {
    align-items: center;
  }
  
  .navbar-nav .nav-link {
    padding: 8px 15px;
  }
  
  /* Ensure proper navbar height */
  .navbar {
    min-height: 70px;
  }
  
  /* Fix any potential overflow issues */
  .navbar .container {
    max-width: 1200px;
    margin: 0 auto;
  }
  
  /* Fix Bootstrap dropdown issues */
  .dropdown-toggle::after {
    display: none !important;
  }
  
  /* Ensure proper navbar structure */
  .navbar > .container {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  
  /* Fix any potential margin issues */
  .navbar-nav {
    margin: 0;
  }
  
  /* Ensure proper button alignment */
  .d-flex.align-items-center {
    gap: 15px;
  }
  
  /* Fix any potential padding issues */
  .navbar-nav .nav-item {
    margin: 0;
  }
</style>
@endpush

@section('content')
  <div class="container" style="margin-top: 12% !important; max-width: 520px;">
    <div class="bg-white rounded p-4 shadow border">
      <div class="text-center mb-4">
        <h1 class="h4 mb-2">Admin Login</h1>
        <p class="text-muted small">Access the admin panel</p>
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

      @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fas fa-exclamation-triangle"></i> Please correct the following errors:
          <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <form method="POST" action="{{ url('/login') }}" novalidate>
        @csrf
        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" 
                 class="form-control @error('email') is-invalid @enderror" 
                 id="email" 
                 name="email" 
                 value="{{ old('email') }}"
                 required 
                 autofocus
                 autocomplete="email">
          @error('email')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
          @enderror
        </div>
        
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" 
                 class="form-control @error('password') is-invalid @enderror" 
                 id="password" 
                 name="password" 
                 required
                 autocomplete="current-password">
          @error('password')
            <div class="invalid-feedback">
              {{ $message }}
            </div>
          @enderror
        </div>

        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="remember" name="remember">
          <label class="form-check-label" for="remember">
            Remember me
          </label>
        </div>

        <div class="d-grid gap-2">
          <button class="btn jatio-bg-color text-white" type="submit">
            <i class="fas fa-sign-in-alt me-2"></i>Sign In
          </button>
        </div>

        <div class="text-center mt-3">
          <a href="{{ url('/') }}" class="text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i>Back to Home
          </a>
        </div>
      </form>
    </div>
  </div>
@endsection


