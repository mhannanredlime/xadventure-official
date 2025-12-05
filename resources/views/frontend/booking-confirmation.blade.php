@extends('layouts.frontend')

@section('title', 'Booking Confirmation - ' . ($reservation->booking_code ?? 'N/A'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontEnd/css/booking-confirmation.css') }}">
    <style>
        .package-booking-details {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            background-color: #f9fafb;
        }

        .package-title {
            color: #e66000;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e66000;
        }

        .alert-info {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            color: #0066cc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-info i {
            margin-right: 8px;
        }

        .transaction-details {
            background-color: #f0f8ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px;
        }
    </style>
@endpush

@section('content')
    <div class="container" style="margin-top: 15% !important;">
        <div class="receipt-container">
            <header class="receipt-header">
                @if (isset($reservation->packageVariant->package->images) && $reservation->packageVariant->package->images->isNotEmpty())
                    <img src="{{ asset('storage/' . $reservation->packageVariant->package->images->first()->image_path) }}"
                        alt="{{ $reservation->packageVariant->package->name ?? 'Package Image' }}"
                        style="width: 100%; height: 250px; object-fit: cover;">
                @else
                    <img src="{{ asset('frontEnd/images/confirm.svg') }}" alt="Adventure confirmation">
                @endif
            </header>

            <main class="receipt-body">
                <div class="text-center">
                    <div class="success-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <p class="confirmation-title">Booking Confirmed!</p>
                    <h1 class="customer-name">Hello {{ $reservation->customer->name ?? 'Guest' }}!</h1>
                    <p class="sub-text">Booking #{{ $reservation->booking_code ?? 'N/A' }} |
                        {{ isset($reservation->created_at) ? $reservation->created_at->format('M d, Y') : '' }}</p>
                    <p class="sub-text mt-4">Thank you for choosing us for your adventure!</p>
                    <p class="sub-text">We've received your payment of</p>
                    <p class="total-paid">৳{{ number_format($totalAmount ?? 0, 2) }}</p>
                </div>

                <div class="booking-summary">
                    <h3 class="section-title">Booking Summary</h3>

                    @if (isset($allReservations) && count($allReservations) > 1)
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle"></i>
                            <strong>Multiple Packages Booked:</strong> You have booked {{ count($allReservations) }}
                            packages in this transaction.
                        </div>
                    @endif

                    @if (isset($allReservations))
                        @foreach ($allReservations as $index => $res)
                            <div class="package-booking-details {{ $index > 0 ? 'mt-4' : '' }}">
                                @if (count($allReservations) > 1)
                                    <h4 class="package-title">Package {{ $index + 1 }}:
                                        {{ $res->packageVariant->package->name ?? 'N/A' }}</h4>
                                @endif

                                <table class="details-table">
                                    <tbody>
                                        <tr>
                                            <td class="item-label">Booking Code</td>
                                            <td class="item-value">{{ $res->booking_code ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="item-label">Package</td>
                                            <td class="item-value">{{ $res->packageVariant->package->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="item-label">Variant</td>
                                            <td class="item-value">{{ $res->packageVariant->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="item-label">Adventure Date</td>
                                            <td class="item-value">
                                                {{ isset($res->date) ? \Carbon\Carbon::parse($res->date)->format('l, F d, Y') : '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="item-label">Time Slot</td>
                                            <td class="item-value">
                                                {{ isset($res->scheduleSlot->start_time) ? \Carbon\Carbon::parse($res->scheduleSlot->start_time)->format('g:i A') : '-' }}
                                                -
                                                {{ isset($res->scheduleSlot->end_time) ? \Carbon\Carbon::parse($res->scheduleSlot->end_time)->format('g:i A') : '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="item-label">Report Time</td>
                                            <td class="item-value">
                                                {{ isset($res->report_time) ? \Carbon\Carbon::parse($res->report_time)->format('g:i A') : '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="item-label">Party Size</td>
                                            <td class="item-value">{{ $res->party_size ?? 1 }}
                                                person{{ ($res->party_size ?? 1) > 1 ? 's' : '' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="item-label">Package Amount</td>
                                            <td class="item-value">৳{{ number_format($res->total_amount ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="item-label">Booking Status</td>
                                            <td class="item-value">
                                                <span class="status-badge status-{{ $res->booking_status ?? 'unknown' }}">
                                                    {{ strtoupper($res->booking_status ?? 'UNKNOWN') }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="item-label">Payment Status</td>
                                            <td class="item-value">
                                                <span class="status-badge status-{{ $res->payment_status ?? 'unknown' }}">
                                                    {{ strtoupper($res->payment_status ?? 'UNKNOWN') }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    @endif

                    <div class="transaction-details mt-4">
                        <table class="details-table">
                            <tbody>
                                <tr>
                                    <td class="item-label">Payment Method</td>
                                    <td class="item-value">{{ $paymentMethod ?? '-' }}</td>
                                </tr>
                                @if (isset($payment->transaction_id))
                                    <tr>
                                        <td class="item-label">Transaction ID</td>
                                        <td class="item-value">{{ $payment->transaction_id }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="payment-section">
                    <h3 class="section-title">Payment Details</h3>
                    <table class="details-table" style="margin-bottom: 0;">
                        <tbody>
                            <tr>
                                <td class="item-label" style="border: none;">Subtotal</td>
                                <td class="item-value" style="border: none;">৳{{ number_format($totalSubtotal ?? 0, 2) }}
                                </td>
                            </tr>
                            @if (isset($totalDiscount) && $totalDiscount > 0)
                                <tr>
                                    <td class="item-label" style="border: none;">Discount</td>
                                    <td class="item-value" style="border: none; color: #28a745;">
                                        -৳{{ number_format($totalDiscount, 2) }}</td>
                                </tr>
                            @endif
                            @if (isset($totalTax) && $totalTax > 0)
                                <tr>
                                    <td class="item-label" style="border: none;">Tax (15% VAT)</td>
                                    <td class="item-value" style="border: none;">৳{{ number_format($totalTax, 2) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="item-label" style="border: none; font-weight: 600; color: var(--text-dark);">
                                    Total Paid</td>
                                <td class="item-value" style="border: none; font-weight: 700; font-size: 1.1rem;">
                                    ৳{{ number_format($totalAmount ?? 0, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="customer-info">
                    <h3 class="section-title">Customer Information</h3>
                    <table class="details-table">
                        <tbody>
                            <tr>
                                <td class="item-label">Name</td>
                                <td class="item-value">{{ $reservation->customer->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="item-label">Email</td>
                                <td class="item-value">{{ $reservation->customer->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="item-label">Phone</td>
                                <td class="item-value">
                                    {{ isset($reservation->customer->phone) ? new \App\Services\PhoneNumberService()->formatForDisplayWithoutPrefix($reservation->customer->phone) : 'N/A' }}
                                </td>
                            </tr>
                            @if (isset($reservation->customer->address))
                                <tr>
                                    <td class="item-label">Address</td>
                                    <td class="item-value">{{ $reservation->customer->address }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
@endsection
