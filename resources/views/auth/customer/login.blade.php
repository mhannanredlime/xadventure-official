@extends('layouts.frontend')

@section('title', 'Customer Login - ATV/UTV Adventures')

@section('content')
    <div class="container-fluid"
        style="background: linear-gradient(135deg, #FC692A 0%, #ff6421 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div class="row justify-content-center w-100 mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg border-0"
                    style="border-radius: 15px; backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95);">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold jatio-color mb-2">Welcome Back!</h2>
                            <p class="text-muted">Sign in to your account to continue</p>
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

                        <form method="POST" action="{{ route('customer.login.post') }}" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-envelope text-muted"></i>
                                    </span>
                                    <input type="email"
                                        class="form-control border-start-0 @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email') }}" required autofocus
                                        autocomplete="email" placeholder="Enter your email">
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <input type="password"
                                        class="form-control border-start-0 @error('password') is-invalid @enderror"
                                        id="password" name="password" required autocomplete="current-password"
                                        placeholder="Enter your password">
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">
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

                            <div class="d-grid gap-2 mb-3">
                                <button class="btn btn-orange jatio-bg-color btn-lg" type="submit"
                                    style="border: none; border-radius: 10px;">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                                </button>
                            </div>

                            <div class="text-center">
                                <p class="mb-0">Don't have an account?
                                    <a href="{{ route('customer.register') }}"
                                        class="text-decoration-none fw-semibold jatio-color">Register here</a>
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

        /* Mobile optimizations */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem 0 !important;
            }

            .col-md-6.col-lg-4 {
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
        }

        /* Tablet optimizations */
        @media (max-width: 991px) and (min-width: 769px) {
            .container-fluid {
                padding: 2rem 0 !important;
            }

            .col-md-6.col-lg-4 {
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

            .col-md-6.col-lg-4 {
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
        }

        /* Large screen optimizations */
        @media (min-width: 1200px) {
            .container-fluid {
                padding: 3rem 0 !important;
            }

            .col-md-6.col-lg-4 {
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
                margin-bottom: 0.75rem !important;
            }

            .mb-4 {
                margin-bottom: 1rem !important;
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
        }
    </style>
@endpush
