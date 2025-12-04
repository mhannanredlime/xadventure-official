@extends('layouts.frontend')

@section('title', 'Payment Options')

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontEnd/css/payment.css') }}">
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

        .price-tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-right: 8px;
        }

        .price-tag.premium {
            background-color: #8b5cf6;
            color: white;
        }

        .price-tag.discounted {
            background-color: #10b981;
            color: white;
        }

        .original-price {
            text-decoration: line-through;
            color: #6b7280;
            font-size: 0.9rem;
            margin-right: 8px;
        }

        .order-summary {
            background: white;
            border-radius: 8px;
            padding: 25px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .payment-section {
            background: white;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .guest-info-section {
            background: white;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        /* Payment method selection styles */
        .form-check {
            padding: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .form-check:hover {
            border-color: #e66000;
            background-color: #fef7f0;
        }

        .form-check-input:checked+.form-check-label {
            color: #e66000;
            font-weight: bold;
        }

        .form-check-input:checked~.form-check {
            border-color: #e66000;
            background-color: #fef7f0;
        }

        .form-check-input {
            margin-right: 10px;
        }

        .form-check-label {
            cursor: pointer;
            font-size: 16px;
        }

        /* Check payment form styles */
        .alert-info {
            background-color: #f0f8ff;
            border-color: #bee5eb;
            color: #0c5460;
        }

        .alert-info ul {
            padding-left: 20px;
        }

        .alert-info li {
            margin-bottom: 5px;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
            color: white;
        }

        /* Payment form visibility */
        .payment-form {
            display: none;
        }

        .payment-form.active {
            display: block;
        }

        /* Form validation styles */
        .is-invalid {
            border-color: #dc3545 !important;
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-4 default-page-marign-top">
        <h2>Payment Options</h2>

        @if (session('error'))
            <div class="error-message">
                <i class="bi  bi-exclamation-triangle"></i>
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="success-message">
                <i class="bi  bi-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="error-message">
                <i class="bi  bi-exclamation-triangle"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <!-- Guest Information Section -->
                <div class="guest-info-section">
                    <h2>Guest Information</h2>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                                id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="customer_email" class="form-label">Email *</label>
                            <input type="email" class="form-control @error('customer_email') is-invalid @enderror"
                                id="customer_email" name="customer_email" value="{{ old('customer_email') }}" required>
                            @error('customer_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_phone" class="form-label">Phone Number *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-telephone text-muted"></i>
                                    <span class="ms-1 text-muted fw-bold">+880</span>
                                </span>
                                <input type="tel"
                                    class="form-control border-start-0 @error('customer_phone') is-invalid @enderror"
                                    id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}"
                                    placeholder="1X XXX XXXX" maxlength="15" required>
                            </div>
                            <div class="form-text text-muted">
                                Enter your mobile number (10-11 digits). +880 prefix is already included above.
                            </div>
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="customer_address" class="form-label">Address</label>
                            <textarea class="form-control @error('customer_address') is-invalid @enderror" id="customer_address"
                                name="customer_address" rows="2">{{ old('customer_address') }}</textarea>
                            @error('customer_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Create Account Section -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="create_account" name="create_account"
                                    value="1" {{ old('create_account') ? 'checked' : '' }}>
                                <label class="form-check-label" for="create_account">
                                    <strong>Create an account for future bookings</strong>
                                    <small class="text-muted d-block">You'll be able to track your bookings and get faster
                                        checkout next time</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Password Field (shown when create account is checked) -->
                    <div id="password-section" class="row mb-3" style="display: none;">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input type="password"
                                    class="form-control border-start-0 @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="Enter your password" minlength="8">
                                <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword">
                                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted">
                                Password must be at least 8 characters long
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm Password *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input type="password"
                                    class="form-control border-start-0 @error('password_confirmation') is-invalid @enderror"
                                    id="password_confirmation" name="password_confirmation"
                                    placeholder="Confirm your password" minlength="8">
                                <button class="btn btn-outline-secondary border-start-0" type="button"
                                    id="togglePasswordConfirmation">
                                    <i class="bi bi-eye" id="togglePasswordConfirmationIcon"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Payment Section -->
                <div class="payment-section">
                    <h2>Payment Method</h2>

                    <!-- Payment Method Selection -->
                    <div class="mb-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="amarpay"
                                value="amarpay" checked>
                            <label class="form-check-label" for="amarpay">
                                <strong>Amar Pay</strong>
                            </label>
                        </div>
                    </div>

                    <!-- Amar Pay Payment Form -->
                    <div id="amarpay-form" class="payment-form active">
                        <form action="{{ route('frontend.checkout.process') }}" method="POST"
                            id="amarpay-payment-form">
                            @csrf
                            <input type="hidden" name="payment_method" value="amarpay">

                            <!-- Hidden fields for guest info -->
                            <input type="hidden" name="customer_name" id="hidden_customer_name_amarpay">
                            <input type="hidden" name="customer_email" id="hidden_customer_email_amarpay">
                            <input type="hidden" name="customer_phone" id="hidden_customer_phone_amarpay">
                            <input type="hidden" name="customer_address" id="hidden_customer_address_amarpay">
                            <input type="hidden" name="create_account" id="hidden_create_account_amarpay">
                            <input type="hidden" name="password" id="hidden_password_amarpay">
                            <input type="hidden" name="password_confirmation" id="hidden_password_confirmation_amarpay">

                            <div class="alert alert-info">
                                <h5><i class="bi  bi-shield-alt"></i> Secure Payment with Amar Pay</h5>
                                <p class="mb-0">You will be redirected to Amar Pay's secure payment gateway to complete
                                    your transaction. Your payment information is encrypted and secure.</p>
                            </div>
                            <div class="text-center">
                                <img src="{{ asset('frontEnd/images/payment.svg') }}" alt="Amar Pay" class="mb-3"
                                    style="max-height: 40px;">
                                <p class="text-muted">Supports all major credit cards, mobile banking, and digital wallets
                                </p>
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-warning btn-lg px-5">
                                    <i class="bi  bi-credit-card me-2"></i>Continue to Payment
                                </button>
                            </div>
                        </form>
                    </div>


                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-lg-4">
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <div class="order-summary-content">
                        <div class="order-summary-item">
                            <span>Subtotal</span>
                            <span>TK.100</span>
                        </div>
                        <div class="order-summary-item">
                            <span>Shipping</span>
                            <span>TK.1000</span>
                        </div>
                        <div class="order-summary-item">
                            <span>Tax</span>
                            <span>TK.1000</span>
                        </div>
                        <div class="order-summary-item">
                            <span>Total</span>
                            <span>TK.1000</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('frontend.cart.index') }}" class="btn btn-outline-secondary w-100">
                    Back to Cart
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Test if jQuery is loaded
        if (typeof jQuery === 'undefined') {
            toastNotifications.error('jQuery is not loaded!');
        } else {
            // jQuery version logged
        }

        $(document).ready(function() {
            // Checkout page loaded

            // Auto-hide messages
            setTimeout(function() {
                $('.success-message, .error-message').fadeOut();
            }, 5000);

            // Payment method is now only Amar Pay, no switching needed

            // Amar Pay form submission
            $('#amarpay-payment-form').on('submit', function(e) {
                e.preventDefault();

                // Copy guest info to hidden fields
                $('#hidden_customer_name_amarpay').val($('#customer_name').val());
                $('#hidden_customer_email_amarpay').val($('#customer_email').val());
                $('#hidden_customer_phone_amarpay').val($('#customer_phone').val());
                $('#hidden_customer_address_amarpay').val($('#customer_address').val());
                $('#hidden_create_account_amarpay').val($('#create_account').is(':checked') ? '1' : '0');
                $('#hidden_password_amarpay').val($('#password').val());
                $('#hidden_password_confirmation_amarpay').val($('#password_confirmation').val());

                // Validate guest info
                let isValid = true;
                const guestFields = ['customer_name', 'customer_email', 'customer_phone'];

                guestFields.forEach(function(field) {
                    const value = $('#' + field).val().trim();
                    if (!value) {
                        $('#' + field).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $('#' + field).removeClass('is-invalid');
                    }
                });

                // Validate password fields if create account is checked
                if ($('#create_account').is(':checked')) {
                    const password = $('#password').val().trim();
                    const passwordConfirmation = $('#password_confirmation').val().trim();

                    if (!password || password.length < 8) {
                        $('#password').addClass('is-invalid');
                        isValid = false;
                    } else {
                        $('#password').removeClass('is-invalid');
                    }

                    if (!passwordConfirmation || password !== passwordConfirmation) {
                        $('#password_confirmation').addClass('is-invalid');
                        isValid = false;
                    } else {
                        $('#password_confirmation').removeClass('is-invalid');
                    }
                }

                if (isValid) {
                    // Validation passed, submitting Amar Pay form
                    this.submit();
                } else {
                    // Validation failed
                    toastNotifications.warning('Please fill in all required fields correctly.');
                }
            });



            // Phone number formatting and validation (simplified)
            $('#customer_phone').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');

                // Limit to 11 digits
                if (value.length > 11) {
                    value = value.substring(0, 11);
                }

                // Simple formatting - just add spaces for readability
                if (value.length >= 8) {
                    value = value.substring(0, 2) + ' ' + value.substring(2, 5) + ' ' + value.substring(5,
                        8) + ' ' + value.substring(8);
                } else if (value.length >= 5) {
                    value = value.substring(0, 2) + ' ' + value.substring(2, 5) + ' ' + value.substring(5);
                } else if (value.length >= 2) {
                    value = value.substring(0, 2) + ' ' + value.substring(2);
                }

                $(this).val(value);
            });

            // Phone number validation on blur (with +880 prefix support)
            $('#customer_phone').on('blur', function() {
                const value = $(this).val().replace(/\s/g, '');

                // Check if it has 880 prefix
                if (value.startsWith('880')) {
                    const phoneWithoutPrefix = value.substring(3);
                    // Should be 10-11 digits after 880
                    const phoneRegex = /^\d{10,11}$/;

                    if (phoneWithoutPrefix && !phoneRegex.test(phoneWithoutPrefix)) {
                        $(this).addClass('is-invalid');
                        if (!$(this).next('.invalid-feedback').length) {
                            $(this).after(
                                '<div class="invalid-feedback">Please enter a valid phone number (10-11 digits). +880 prefix will be added automatically.</div>'
                            );
                        }
                    } else {
                        $(this).removeClass('is-invalid');
                        $(this).next('.invalid-feedback').remove();
                    }
                } else {
                    // Accept 10-11 digits (more flexible validation)
                    const phoneRegex = /^\d{10,11}$/;

                    if (value && !phoneRegex.test(value)) {
                        $(this).addClass('is-invalid');
                        if (!$(this).next('.invalid-feedback').length) {
                            $(this).after(
                                '<div class="invalid-feedback">Please enter a valid phone number (10-11 digits). +880 prefix will be added automatically.</div>'
                            );
                        }
                    } else {
                        $(this).removeClass('is-invalid');
                        $(this).next('.invalid-feedback').remove();
                    }
                }
            });

            // Handle create account checkbox
            $('#create_account').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#password-section').slideDown();
                    $('#password, #password_confirmation').attr('required', 'required');
                } else {
                    $('#password-section').slideUp();
                    $('#password, #password_confirmation').removeAttr('required').val('');
                }
            });

            // Toggle password visibility
            $('#togglePassword').on('click', function() {
                const passwordField = $('#password');
                const icon = $('#togglePasswordIcon');

                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                }
            });

            $('#togglePasswordConfirmation').on('click', function() {
                const passwordField = $('#password_confirmation');
                const icon = $('#togglePasswordConfirmationIcon');

                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                }
            });

            // Password confirmation validation
            $('#password_confirmation').on('input', function() {
                const password = $('#password').val();
                const confirmation = $(this).val();

                if (confirmation && password !== confirmation) {
                    $(this).addClass('is-invalid');
                    if (!$(this).next('.invalid-feedback').length) {
                        $(this).after('<div class="invalid-feedback">Passwords do not match</div>');
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).next('.invalid-feedback').remove();
                }
            });

            // Initialize password section visibility based on checkbox state
            if ($('#create_account').is(':checked')) {
                $('#password-section').show();
                $('#password, #password_confirmation').attr('required', 'required');
            }

            // Initialize on page load
        });
    </script>
@endpush
