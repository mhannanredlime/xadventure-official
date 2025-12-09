@extends('layouts.frontend')

@section('title', 'Payment Failed - Xtreme Adventure')

@push('styles')
    <style>
        .payment-failed-container {
            margin-top: 15%;
            min-height: 60vh;
        }

        .payment-failed-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }

        .payment-failed-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 20px;
        }

        .payment-failed-title {
            color: #dc3545;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .payment-failed-message {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .payment-details {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }

        .payment-details h5 {
            color: #495057;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .payment-details p {
            margin-bottom: 8px;
            color: #6c757d;
        }

        .payment-details strong {
            color: #495057;
        }

        .action-buttons {
            margin-top: 30px;
        }

        .btn-retry {
            background-color: #e66000;
            border-color: #e66000;
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-right: 15px;
            transition: all 0.3s ease;
        }

        .btn-retry:hover {
            background-color: #d55a00;
            border-color: #d55a00;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(230, 96, 0, 0.3);
        }

        .btn-home {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            background-color: #5a6268;
            border-color: #5a6268;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
        }

        .alert-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .help-section {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
            text-align: left;
        }

        .help-section h5 {
            color: #0066cc;
            margin-bottom: 15px;
        }

        .help-section ul {
            margin-bottom: 0;
            padding-left: 20px;
        }

        .help-section li {
            margin-bottom: 8px;
            color: #495057;
        }
    </style>
@endpush

@section('content')
    <div class="container payment-failed-container">
        <div class="payment-failed-card">
            <div class="payment-failed-icon">
                <i class="bi  bi-times-circle"></i>
            </div>

            <h1 class="payment-failed-title">Payment Failed</h1>

            <p class="payment-failed-message">
                We're sorry, but your payment could not be processed at this time.
                This could be due to various reasons such as insufficient funds,
                incorrect card details, or network issues.
            </p>

            @if (session('error'))
                <div class="alert-error">
                    <i class="bi  bi-exclamation-triangle"></i>
                    <strong>Error:</strong> {{ session('error') }}
                </div>
            @endif

            @if (session('payment_details'))
                <div class="payment-details">
                    <h5><i class="bi  bi-info-circle"></i> Payment Details</h5>
                    @foreach (session('payment_details') as $key => $value)
                        <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</p>
                    @endforeach
                </div>
            @endif

            <div class="action-buttons">
                <a href="{{ route('checkout.index') }}" class="btn-retry">
                    <i class="bi  bi-redo"></i> Try Again
                </a>
                <a href="{{ route('packages.custom.index') }}" class="btn-home">
                    <i class="bi  bi-home"></i> Back to Packages
                </a>
            </div>

            <div class="help-section">
                <h5><i class="bi  bi-question-circle"></i> Need Help?</h5>
                <p>If you continue to experience payment issues, please:</p>
                <ul>
                    <li>Check that your card details are correct</li>
                    <li>Ensure you have sufficient funds in your account</li>
                    <li>Try using a different payment method</li>
                    <li>Contact your bank if the issue persists</li>
                    <li>Reach out to our support team for assistance</li>
                </ul>
                <p class="mt-3 mb-0">
                    <strong>Contact Support:</strong>
                    <a href="mailto:support@xtremeadventure.com" style="color: #0066cc;">support@xtremeadventure.com</a> |
                    <a href="tel:+1234567890" style="color: #0066cc;">+1 (234) 567-890</a>
                </p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Clear any payment-related session data
        document.addEventListener('DOMContentLoaded', function() {
            // Optional: Clear payment session data after showing the error
            // This can be done via AJAX if needed
        });
    </script>
@endpush
