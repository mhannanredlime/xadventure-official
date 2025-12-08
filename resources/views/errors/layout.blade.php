@extends('layouts.frontend')

@section('title')
    @yield('error_title', 'Error')
@endsection

@push('styles')
    <style>
        .error-hero {
            background: linear-gradient(135deg, #1a2a3a 0%, #2c3e50 100%);
            /* Dark Blue Theme */
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .error-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;

            background-image: @yield('bg_image', "url('" . asset('frontEnd/images/bg-1.jpg') . "')")

            ;
            background-position: center;
            background-size: cover;
            opacity: 0.1;
            mix-blend-mode: overlay;
        }

        .error-content {
            position: relative;
            z-index: 2;
            padding: 2rem;
        }

        .error-number {
            font-size: 8rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 1rem;
            background: linear-gradient(to bottom, #ff9f43, #ff6b6b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .error-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: white;
        }

        .error-message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: rgba(255, 255, 255, 0.8);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-error-home {
            background-color: #ff6600;
            color: white;
            border: 2px solid #ff6600;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-error-home:hover {
            background-color: transparent;
            color: #ff6600;
            transform: translateY(-2px);
        }

        .btn-error-back {
            background-color: transparent;
            color: white;
            border: 2px solid white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-error-back:hover {
            background-color: white;
            color: #1a2a3a;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .error-number {
                font-size: 6rem;
            }

            .error-title {
                font-size: 2rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="error-hero">
        <div class="error-content">
            <div class="error-number">@yield('code')</div>
            <h1 class="error-title">@yield('message_title')</h1>
            <p class="error-message">
                @yield('message_description')
            </p>
            <div class="error-actions">
                <a href="{{ url('/') }}" class="btn-error-home">
                    <i class="bi bi-house me-2"></i>Go Home
                </a>
                @hasSection('action_button')
                    @yield('action_button')
                @else
                    <a href="javascript:history.back()" class="btn-error-back">
                        <i class="bi bi-arrow-left me-2"></i>Go Back
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
