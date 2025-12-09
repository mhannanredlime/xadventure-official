@extends('layouts.frontend')

@section('title', 'Payment Options')

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontEnd/css/payment.css') }}">
    <link rel="stylesheet" href="{{ asset('frontEnd/css/custom-packages.css') }}">

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
    <div class="container mt-4 default-page-marign-top" x-data="checkoutComponent()">
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

        <template x-if="errors.length > 0">
            <div class="error-message">
                <i class="bi  bi-exclamation-triangle"></i>
                <ul class="mb-0">
                    <template x-for="error in errors">
                        <li x-text="error"></li>
                    </template>
                </ul>
            </div>
        </template>

        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('checkout.process') }}" method="POST" @submit.prevent="submitForm">
                    @csrf
                    <!-- Guest Information Section -->
                    <div class="guest-info-section">
                        <h2>Guest Information</h2>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer_name" class="form-label">Full Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control"
                                    :class="{ 'is-invalid': formErrors.customer_name }" id="customer_name"
                                    name="customer_name" x-model="form.customer_name" required>
                                <div class="invalid-feedback" x-show="formErrors.customer_name"
                                    x-text="formErrors.customer_name"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="customer_email" class="form-label">Email <span
                                        class="text-danger">*</span></label>
                                <input type="email" class="form-control"
                                    :class="{ 'is-invalid': formErrors.customer_email }" id="customer_email"
                                    name="customer_email" x-model="form.customer_email" required>
                                <div class="invalid-feedback" x-show="formErrors.customer_email"
                                    x-text="formErrors.customer_email"></div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer_phone" class="form-label">Phone Number <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-telephone text-muted"></i>
                                        <span class="ms-1 text-muted fw-bold">+880</span>
                                    </span>
                                    <input type="tel" class="form-control border-start-0"
                                        :class="{ 'is-invalid': formErrors.customer_phone }" id="customer_phone"
                                        name="customer_phone" x-model="form.customer_phone" @input="formatPhone"
                                        placeholder="1X XXX XXXX" maxlength="15" required>
                                </div>
                                <div class="form-text text-muted">
                                    Enter your mobile number (10-11 digits). +880 prefix is already included above.
                                </div>
                                <div class="invalid-feedback d-block" x-show="formErrors.customer_phone"
                                    x-text="formErrors.customer_phone"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="customer_address" class="form-label">Address</label>
                                <textarea class="form-control" id="customer_address" name="customer_address" x-model="form.customer_address"
                                    rows="2"></textarea>
                            </div>
                        </div>

                        <!-- Create Account Section -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="create_account"
                                        name="create_account" value="1" x-model="form.create_account">
                                    <label class="form-check-label" for="create_account">
                                        <strong>Create an account for future bookings</strong>
                                        <small class="text-muted d-block">You'll be able to track your bookings and get
                                            faster
                                            checkout next time</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Password Field (shown when create account is checked) -->
                        <div class="row mb-3" x-show="form.create_account" x-transition>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <input :type="showPassword ? 'text' : 'password'" class="form-control border-start-0"
                                        :class="{ 'is-invalid': formErrors.password }" id="password" name="password"
                                        x-model="form.password" placeholder="Enter your password"
                                        :required="form.create_account">
                                    <button class="btn btn-outline-secondary border-start-0" type="button"
                                        @click="showPassword = !showPassword">
                                        <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                                    </button>
                                </div>
                                <div class="form-text text-muted">Password must be at least 8 characters long</div>
                                <div class="invalid-feedback d-block" x-show="formErrors.password"
                                    x-text="formErrors.password"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <input :type="showConfirmPassword ? 'text' : 'password'"
                                        class="form-control border-start-0"
                                        :class="{ 'is-invalid': formErrors.password_confirmation }"
                                        id="password_confirmation" name="password_confirmation"
                                        x-model="form.password_confirmation" placeholder="Confirm your password"
                                        :required="form.create_account">
                                    <button class="btn btn-outline-secondary border-start-0" type="button"
                                        @click="showConfirmPassword = !showConfirmPassword">
                                        <i class="bi" :class="showConfirmPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback d-block" x-show="formErrors.password_confirmation"
                                    x-text="formErrors.password_confirmation"></div>
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
                                    value="amarpay" x-model="form.payment_method">
                                <label class="form-check-label" for="amarpay">
                                    <strong>Amar Pay</strong>
                                </label>
                            </div>
                        </div>

                        <!-- Amar Pay Info -->
                        <div x-show="form.payment_method === 'amarpay'">
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
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-warning btn-lg px-5" :disabled="submitting">
                                <span x-show="!submitting"><i class="bi bi-credit-card me-2"></i>Continue to
                                    Payment</span>
                                <span x-show="submitting"><i
                                        class="bi bi-arrow-repeat fa-spin me-2"></i>Processing...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-lg-4">
                @php
                    $subtotal = $guestCartItems->sum(function ($item) {
                        return $item->cart_amount * $item->quantity;
                    });
                @endphp

                @include('frontend._order_summary', [
                    'guestCartItems' => $guestCartItems ?? [],
                    'subtotal' => $subtotal ?? 0,
                    'showPlaceOrder' => false,
                ])

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkoutComponent', () => ({
                form: {
                    customer_name: '{{ old('customer_name') }}',
                    customer_email: '{{ old('customer_email') }}',
                    customer_phone: '{{ old('customer_phone') }}',
                    customer_address: '{{ old('customer_address') }}',
                    create_account: {{ old('create_account') ? 'true' : 'false' }},
                    password: '',
                    password_confirmation: '',
                    payment_method: 'amarpay'
                },
                formErrors: {},
                errors: [], // Global errors
                showPassword: false,
                showConfirmPassword: false,
                submitting: false,

                formatPhone() {
                    let value = this.form.customer_phone.replace(/\D/g, '');
                    if (value.length > 11) value = value.substring(0, 11);

                    // Format
                    if (value.length >= 8) {
                        value = value.substring(0, 2) + ' ' + value.substring(2, 5) + ' ' + value
                            .substring(5, 8) + ' ' + value.substring(8);
                    } else if (value.length >= 5) {
                        value = value.substring(0, 2) + ' ' + value.substring(2, 5) + ' ' + value
                            .substring(5);
                    } else if (value.length >= 2) {
                        value = value.substring(0, 2) + ' ' + value.substring(2);
                    }
                    this.form.customer_phone = value;
                },

                validate() {
                    this.formErrors = {};
                    let isValid = true;

                    if (!this.form.customer_name) {
                        this.formErrors.customer_name = 'Name is required';
                        isValid = false;
                    }
                    if (!this.form.customer_email) {
                        this.formErrors.customer_email = 'Email is required';
                        isValid = false;
                    }

                    // Phone validation
                    const phoneClean = this.form.customer_phone.replace(/\s/g, '');
                    if (!phoneClean || phoneClean.length < 10) {
                        this.formErrors.customer_phone = 'Valid phone number is required';
                        isValid = false;
                    }

                    if (this.form.create_account) {
                        if (!this.form.password || this.form.password.length < 8) {
                            this.formErrors.password = 'Password must be at least 8 chars';
                            isValid = false;
                        }
                        if (this.form.password !== this.form.password_confirmation) {
                            this.formErrors.password_confirmation = 'Passwords do not match';
                            isValid = false;
                        }
                    }

                    return isValid;
                },

                submitForm(e) {
                    if (this.validate()) {
                        this.submitting = true;
                        e.target.submit();
                    } else {
                        // Scroll to first error
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                }
            }))
        });
    </script>
@endpush
