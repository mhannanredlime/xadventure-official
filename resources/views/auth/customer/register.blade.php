@extends('layouts.frontend')

@section('title', 'Customer Registration - ATV/UTV Adventures')

@section('content')
    <div class="container-fluid "
        style="background: linear-gradient(135deg, #FC692A 0%, #ff6421 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 0;">
        <div class="row justify-content-center w-100 mt-5">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg border-0"
                    style="border-radius: 15px; backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95);">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold jatio-color mb-2">Join ATV/UTV Adventures</h2>
                            <p class="text-muted">Create your account to start booking amazing adventures</p>
                        </div>

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('customer.register') }}" novalidate>
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label fw-semibold">Full Name <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-person text-muted"></i>
                                        </span>
                                        <input type="text"
                                            class="form-control border-start-0 @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name') }}" required autofocus
                                            autocomplete="name" placeholder="Enter your full name">
                                    </div>
                                    @error('name')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-semibold">Email Address <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-envelope text-muted"></i>
                                        </span>
                                        <input type="email"
                                            class="form-control border-start-0 @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email') }}" required
                                            autocomplete="email" placeholder="Enter your email">
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-telephone text-muted"></i>
                                        </span>
                                        <input type="tel"
                                            class="form-control border-start-0 @error('phone') is-invalid @enderror"
                                            id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel"
                                            placeholder="Enter your phone number">
                                    </div>
                                    @error('phone')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label fw-semibold">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-geo-alt text-muted"></i>
                                        </span>
                                        <input type="text"
                                            class="form-control border-start-0 @error('address') is-invalid @enderror"
                                            id="address" name="address" value="{{ old('address') }}"
                                            autocomplete="street-address" placeholder="Enter your address">
                                    </div>
                                    @error('address')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label fw-semibold">Password <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-lock text-muted"></i>
                                        </span>
                                        <input type="password"
                                            class="form-control border-start-0 @error('password') is-invalid @enderror"
                                            id="password" name="password" required autocomplete="new-password"
                                            placeholder="Create a password">
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label fw-semibold">Confirm Password
                                        <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-lock-fill text-muted"></i>
                                        </span>
                                        <input type="password" class="form-control border-start-0"
                                            id="password_confirmation" name="password_confirmation" required
                                            autocomplete="new-password" placeholder="Confirm your password">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and
                                    <a href="#" class="text-decoration-none">Privacy Policy</a>
                                </label>
                            </div>

                            <div class="d-grid gap-2 mb-3">
                                <button class="btn btn-orange jatio-bg-color btn-lg" type="submit"
                                    style="border: none; border-radius: 10px;">
                                    <i class="bi bi-person-plus me-2"></i>Create Account
                                </button>
                            </div>

                            <div class="text-center">
                                <p class="mb-0">Already have an account?
                                    <a href="{{ route('customer.login') }}"
                                        class="text-decoration-none fw-semibold jatio-color">Sign in here</a>
                                </p>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <a href="{{ route('home') }}" class="text-decoration-none text-muted">
                                <i class="bi bi-arrow-left me-1"></i>Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95) !important;
        }

        .form-control:focus {
            border-color: #FC692A;
            box-shadow: 0 0 0 0.2rem rgba(252, 105, 42, 0.25);
        }

        .btn-orange:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(252, 105, 42, 0.4);
            transition: all 0.3s ease;
        }

        .input-group-text {
            border-color: #ced4da;
        }

        .form-control {
            border-color: #ced4da;
        }

        /* Mobile optimizations */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem 0 !important;
            }

            .col-md-8.col-lg-6 {
                padding: 0 1rem;
            }

            .card-body {
                padding: 2rem !important;
            }

            h2 {
                font-size: 1.75rem !important;
            }

            .form-label {
                font-size: 0.9rem;
            }

            .form-control {
                font-size: 0.9rem;
                padding: 0.75rem 0.75rem;
            }

            .input-group-text {
                font-size: 0.9rem;
                padding: 0.75rem 0.75rem;
            }

            .btn-lg {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }

            .form-check-label {
                font-size: 0.85rem;
            }

            p {
                font-size: 0.9rem;
            }

            a {
                font-size: 0.9rem;
            }

            /* Stack form fields on mobile */
            .row .col-md-6 {
                margin-bottom: 1rem;
            }
        }

        /* Tablet optimizations */
        @media (max-width: 991px) and (min-width: 769px) {
            .container-fluid {
                padding: 2rem 0 !important;
            }

            .col-md-8.col-lg-6 {
                padding: 0 2rem;
            }

            .card-body {
                padding: 3rem !important;
            }

            h2 {
                font-size: 2rem !important;
            }

            .form-control {
                padding: 0.875rem 0.875rem;
            }

            .input-group-text {
                padding: 0.875rem 0.875rem;
            }

            .btn-lg {
                padding: 0.875rem 1.25rem;
            }
        }

        /* Small mobile optimizations */
        @media (max-width: 575px) {
            .container-fluid {
                padding: 0.5rem 0 !important;
            }

            .col-md-8.col-lg-6 {
                padding: 0 0.5rem;
            }

            .card-body {
                padding: 1.5rem !important;
            }

            h2 {
                font-size: 1.5rem !important;
            }

            .form-label {
                font-size: 0.85rem;
            }

            .form-control {
                font-size: 0.85rem;
                padding: 0.625rem 0.625rem;
            }

            .input-group-text {
                font-size: 0.85rem;
                padding: 0.625rem 0.625rem;
            }

            .btn-lg {
                padding: 0.625rem 0.875rem;
                font-size: 0.85rem;
            }

            .form-check-label {
                font-size: 0.8rem;
            }

            p {
                font-size: 0.85rem;
            }

            a {
                font-size: 0.85rem;
            }

            .alert {
                font-size: 0.85rem;
                padding: 0.75rem;
            }

            /* Further optimize form layout for small screens */
            .row .col-md-6 {
                margin-bottom: 0.75rem;
            }

            .mb-3 {
                margin-bottom: 0.75rem !important;
            }

            .mb-4 {
                margin-bottom: 1rem !important;
            }
        }

        /* Large screen optimizations */
        @media (min-width: 1200px) {
            .container-fluid {
                padding: 3rem 0 !important;
            }

            .col-md-8.col-lg-6 {
                padding: 0 3rem;
            }

            .card-body {
                padding: 4rem !important;
            }

            h2 {
                font-size: 2.5rem !important;
            }

            .form-control {
                padding: 1rem 1rem;
            }

            .input-group-text {
                padding: 1rem 1rem;
            }

            .btn-lg {
                padding: 1rem 1.5rem;
                font-size: 1.1rem;
            }
        }

        /* Landscape mobile optimizations */
        @media (max-width: 768px) and (orientation: landscape) {
            .container-fluid {
                padding: 0.5rem 0 !important;
            }

            .card-body {
                padding: 1.5rem !important;
            }

            h2 {
                font-size: 1.25rem !important;
            }

            .form-control {
                padding: 0.5rem 0.5rem;
            }

            .input-group-text {
                padding: 0.5rem 0.5rem;
            }

            .btn-lg {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }

            .mb-3 {
                margin-bottom: 0.5rem !important;
            }

            .mb-4 {
                margin-bottom: 0.75rem !important;
            }

            .row .col-md-6 {
                margin-bottom: 0.5rem;
            }
        }

        /* Form-specific responsive improvements */
        @media (max-width: 767px) {

            /* Improve input group layout on mobile */
            .input-group {
                flex-direction: column;
            }

            .input-group-text {
                border-radius: 10px 10px 0 0 !important;
                border-bottom: none;
                width: 100%;
            }

            .input-group .form-control {
                border-radius: 0 0 10px 10px !important;
                border-top: none;
                width: 100%;
            }

            /* Optimize form spacing */
            .mb-3 {
                margin-bottom: 1rem !important;
            }

            .mb-4 {
                margin-bottom: 1.5rem !important;
            }

            /* Improve button layout */
            .d-grid.gap-2 {
                gap: 0.75rem !important;
            }

            .btn-lg {
                width: 100%;
            }

            /* Optimize alert messages */
            .alert {
                margin-bottom: 1rem;
                border-radius: 10px;
            }

            .alert-dismissible .btn-close {
                padding: 0.75rem;
            }

            /* Improve checkbox layout */
            .form-check {
                margin-bottom: 1rem;
            }

            .form-check-label {
                line-height: 1.4;
            }

            .form-check-label a {
                word-break: break-word;
            }
        }

        /* Form field specific optimizations */
        @media (max-width: 575px) {

            /* Optimize text-danger asterisk */
            .text-danger {
                font-size: 0.8rem;
            }

            /* Improve placeholder text */
            .form-control::placeholder {
                font-size: 0.8rem;
            }

            /* Optimize form validation messages */
            .invalid-feedback {
                font-size: 0.75rem;
                margin-top: 0.25rem;
            }

            /* Improve terms and conditions text */
            .form-check-label {
                font-size: 0.8rem;
                line-height: 1.3;
            }
        }

        /* Accessibility improvements */
        @media (prefers-reduced-motion: reduce) {
            .btn-orange:hover {
                transform: none;
                transition: none;
            }

            .card {
                transition: none;
            }
        }

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .card {
                border: 2px solid #000 !important;
                background: #fff !important;
            }

            .form-control {
                border: 2px solid #000 !important;
            }

            .btn-orange {
                border: 2px solid #000 !important;
                background: #FC692A !important;
                color: #000 !important;
            }

            .form-check-input:checked {
                background-color: #000 !important;
                border-color: #000 !important;
            }
        }

        /* Print styles */
        @media print {
            .container-fluid {
                background: white !important;
                padding: 0 !important;
            }

            .card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
                background: white !important;
            }

            .btn,
            .form-check-input {
                display: none !important;
            }

            .form-control {
                border: 1px solid #000 !important;
                background: white !important;
            }

            .text-danger {
                color: #000 !important;
            }
        }
    </style>
@endpush
