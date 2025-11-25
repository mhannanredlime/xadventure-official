@extends('layouts.frontend')

@section('title', 'Admin Login')

@push('styles')
    <style>
        /* Login Card */
        .navbar {
            position: relative !important;
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.9)) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 0;
        }

        .login-box {
            margin: 100px auto 0;
            max-width: 480px;
            background: #fff;
            border-radius: 12px;
            padding: 2rem 2rem 2.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 1;
            /* lower than navbar */
        }


        .login-box h2 {
            font-weight: 700;
        }

        .login-box p {
            color: #6c757d;
        }

        /* Password wrapper & toggle */
        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 45px;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .password-toggle i {
            color: #666;
            font-size: 18px;
            transition: 0.3s ease;
        }

        .password-toggle i:hover {
            transform: scale(1.2);
            color: #fc692a;
        }

        /* Floating label focus color */
        .form-floating>.form-control:focus~label {
            color: #fc692a;
        }

        /* Input focus border */
        .form-control:focus {
            border-color: #fc692a;
            box-shadow: 0 0 0 0.2rem rgba(10, 151, 176, 0.25);
        }

        /* Buttons */
        .btn-primary {
            background-color: #fc692a;
            border-color: #fc692a;
        }

        .btn-primary:hover {
            background-color: #fc692a;
            border-color: #fc692a;
        }

        /* Checkbox */
        .form-check-label {
            cursor: pointer;
        }

        /* Alerts */
        .alert i {
            margin-right: 0.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="container login-box">
        <div class="text-center mb-4">
            <h2 class="fw-bold jatio-color">Admin Login</h2>
            <p class="small">Sign in to access your dashboard</p>
        </div>

        {{-- Success --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Error --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> Please correct the following errors:
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ url('/login') }}" method="POST" novalidate>
            @csrf

            {{-- Email --}}
            <div class="form-floating mb-4">
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                    name="email" placeholder="Email" value="{{ old('email') }}" required autofocus>
                <label for="email">Email Address</label>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-floating mb-4 password-wrapper">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                    name="password" placeholder="Password" required>
                <label for="password">Password</label>
                <span class="password-toggle"><i id="toggleIcon" class="fas fa-eye"></i></span>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Remember Me --}}
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>

            {{-- Submit --}}
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i> Sign In
                </button>
            </div>

            <div class="text-center">
                <a href="{{ url('/') }}" class="text-decoration-none text-primary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Home
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.password-toggle').click(function() {
                let password = $('#password');
                let icon = $('#toggleIcon');
                if (password.attr('type') === 'password') {
                    password.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash text-primary');
                } else {
                    password.attr('type', 'password');
                    icon.removeClass('fa-eye-slash text-primary').addClass('fa-eye text-secondary');
                }
            });
        });
    </script>
@endpush
