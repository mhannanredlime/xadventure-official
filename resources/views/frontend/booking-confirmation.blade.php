@extends('layouts.frontend')

@section('title', 'Booking Confirmation - ' . $reservation->booking_code)

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
        @if($reservation->packageVariant->package->images->isNotEmpty())
          <img src="{{ asset('storage/' . $reservation->packageVariant->package->images->first()->image_path) }}" 
               alt="{{ $reservation->packageVariant->package->name }}" 
               style="width: 100%; height: 250px; object-fit: cover;">
        @else
          <img src="{{ asset('frontEnd/images/confirm.svg') }}" alt="Adventure confirmation">
        @endif
      </header>
      
      <main class="receipt-body">
        <!-- Success Message -->
        <div class="text-center">
          <div class="success-icon">
            <i class="fas fa-check-circle"></i>
          </div>
          <p class="confirmation-title">Booking Confirmed!</p>
          <h1 class="customer-name">Hello {{ $reservation->customer->name }}!</h1>
          <p class="sub-text">Booking #{{ $reservation->booking_code }} | {{ $reservation->created_at->format('M d, Y') }}</p>
          <p class="sub-text mt-4">Thank you for choosing us for your adventure!</p>
          <p class="sub-text">We've received your payment of</p>
          <p class="total-paid">৳{{ number_format($totalAmount, 2) }}</p>
        </div>

        <!-- Booking Summary -->
        <div class="booking-summary">
          <h3 class="section-title">Booking Summary</h3>
          
          @if(count($allReservations) > 1)
            <div class="alert alert-info mb-4">
              <i class="fas fa-info-circle"></i>
              <strong>Multiple Packages Booked:</strong> You have booked {{ count($allReservations) }} packages in this transaction.
            </div>
          @endif
          
          @foreach($allReservations as $index => $res)
            <div class="package-booking-details {{ $index > 0 ? 'mt-4' : '' }}">
              @if(count($allReservations) > 1)
                <h4 class="package-title">Package {{ $index + 1 }}: {{ $res->packageVariant->package->name }}</h4>
              @endif
              
              <table class="details-table">
                <tbody>
                  <tr>
                    <td class="item-label">Booking Code</td>
                    <td class="item-value">{{ $res->booking_code }}</td>
                  </tr>
                  <tr>
                    <td class="item-label">Package</td>
                    <td class="item-value">{{ $res->packageVariant->package->name }}</td>
                  </tr>
                  <tr>
                    <td class="item-label">Variant</td>
                    <td class="item-value">{{ $res->packageVariant->name }}</td>
                  </tr>
                  <tr>
                    <td class="item-label">Adventure Date</td>
                    <td class="item-value">{{ $res->date->format('l, F d, Y') }}</td>
                  </tr>
                  <tr>
                    <td class="item-label">Time Slot</td>
                    <td class="item-value">{{ $res->scheduleSlot->start_time->format('g:i A') }} - {{ $res->scheduleSlot->end_time->format('g:i A') }}</td>
                  </tr>
                  <tr>
                    <td class="item-label">Report Time</td>
                    <td class="item-value">{{ $res->report_time->format('g:i A') }}</td>
                  </tr>
                  <tr>
                    <td class="item-label">Party Size</td>
                    <td class="item-value">1 person</td>
                  </tr>
                  <tr>
                    <td class="item-label">Package Amount</td>
                    <td class="item-value">৳{{ number_format($res->total_amount, 2) }}</td>
                  </tr>
                  <tr>
                    <td class="item-label">Booking Status</td>
                    <td class="item-value">
                      <span class="status-badge status-{{ $res->booking_status }}">
                        {{ strtoupper($res->booking_status) }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="item-label">Payment Status</td>
                    <td class="item-value">
                      <span class="status-badge status-{{ $res->payment_status }}">
                        {{ strtoupper($res->payment_status) }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          @endforeach
          
          <!-- Transaction Details -->
          <div class="transaction-details mt-4">
            <table class="details-table">
              <tbody>
                <tr>
                  <td class="item-label">Payment Method</td>
                  <td class="item-value">{{ $paymentMethod }}</td>
                </tr>
                @if($payment->transaction_id)
                <tr>
                  <td class="item-label">Transaction ID</td>
                  <td class="item-value">{{ $payment->transaction_id }}</td>
                </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>

        <!-- Payment Breakdown -->
        <div class="payment-section">
          <h3 class="section-title">Payment Details</h3>
          <table class="details-table" style="margin-bottom: 0;">
            <tbody>
              <tr>
                <td class="item-label" style="border: none;">Subtotal</td>
                <td class="item-value" style="border: none;">৳{{ number_format($totalSubtotal, 2) }}</td>
              </tr>
              @if($totalDiscount > 0)
              <tr>
                <td class="item-label" style="border: none;">Discount</td>
                <td class="item-value" style="border: none; color: #28a745;">-৳{{ number_format($totalDiscount, 2) }}</td>
              </tr>
              @endif
              @if($totalTax > 0)
              <tr>
                <td class="item-label" style="border: none;">Tax (15% VAT)</td>
                <td class="item-value" style="border: none;">৳{{ number_format($totalTax, 2) }}</td>
              </tr>
              @endif
              <tr>
                <td class="item-label" style="border: none; font-weight: 600; color: var(--text-dark);">Total Paid</td>
                <td class="item-value" style="border: none; font-weight: 700; font-size: 1.1rem;">৳{{ number_format($totalAmount, 2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Customer Information -->
        <div class="customer-info">
          <h3 class="section-title">Customer Information</h3>
          <table class="details-table">
            <tbody>
              <tr>
                <td class="item-label">Name</td>
                <td class="item-value">{{ $reservation->customer->name }}</td>
              </tr>
              <tr>
                <td class="item-label">Email</td>
                <td class="item-value">{{ $reservation->customer->email }}</td>
              </tr>
              <tr>
                <td class="item-label">Phone</td>
                <td class="item-value">{{ $reservation->customer->phone ? (new \App\Services\PhoneNumberService())->formatForDisplayWithoutPrefix($reservation->customer->phone) : 'N/A' }}</td>
              </tr>
              @if($reservation->customer->address)
              <tr>
                <td class="item-label">Address</td>
                <td class="item-value">{{ $reservation->customer->address }}</td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>

        <!-- Package Details -->
        <div class="package-details">
          <h3 class="section-title">Package Details</h3>
          <div class="package-info">
            <h4>{{ $reservation->packageVariant->package->name }}</h4>
            @if($reservation->packageVariant->package->description)
              <p class="package-description">{{ $reservation->packageVariant->package->description }}</p>
            @endif
            @if($reservation->packageVariant->package->details)
              <div class="package-details-content">
                {!! nl2br(e($reservation->packageVariant->package->details)) !!}
              </div>
            @endif
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <a href="{{ route('customer.dashboard') }}" class="btn btn-custom-orange">VIEW MY BOOKINGS</a>
          <a href="{{ route('frontend.packages.index') }}" class="btn btn-custom-secondary">BOOK ANOTHER ADVENTURE</a>
        </div>

        @if($reservation->acknowledgment_data || $reservation->signature_data)
        <!-- Booking Acknowledgment -->
        <section class="acknowledgment-section" style="margin: 40px 0; padding: 30px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px; border: 2px solid #28a745;">
          <h2 class="section-title" style="color: #28a745; text-align: center; margin-bottom: 25px;">
            <i class="fas fa-handshake me-2"></i>Booking Acknowledgment
          </h2>
          
          <div class="acknowledgment-content" style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="alert alert-success" style="border-left: 4px solid #28a745;">
              <i class="fas fa-check-circle me-2"></i>
              <strong>You have successfully agreed to the following terms and conditions:</strong>
            </div>
            
            @if($reservation->acknowledgment_data)
              <div class="acknowledgment-details" style="margin: 20px 0;">
                @if(isset($reservation->acknowledgment_data['driver_license_requirement']) && $reservation->acknowledgment_data['driver_license_requirement'])
                  <div class="d-flex align-items-start mb-3" style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #28a745;">
                    <i class="fas fa-check-circle text-success me-3 mt-1" style="font-size: 1.2rem;"></i>
                    <div>
                      <strong style="color: #28a745;">Driver's License Requirement</strong><br>
                      <small class="text-muted">I understand that for Each vehicle reservation, we will have at least one person have Motorcycle or Car Driver's licenses</small>
                    </div>
                  </div>
                @endif
                
                @if(isset($reservation->acknowledgment_data['license_show_requirement']) && $reservation->acknowledgment_data['license_show_requirement'])
                  <div class="d-flex align-items-start mb-3" style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #28a745;">
                    <i class="fas fa-check-circle text-success me-3 mt-1" style="font-size: 1.2rem;"></i>
                    <div>
                      <strong style="color: #28a745;">License Show Requirement</strong><br>
                      <small class="text-muted">I understand I have to carry show my driver's license before the ride start. Failure the show my physical license, Xadventure will Deny the Ride, and I will not get my money back.</small>
                    </div>
                  </div>
                @endif
              </div>
            @endif

            @if($reservation->signature_data)
              <div class="signature-display" style="text-align: center; margin-top: 25px;">
                <h4 style="color: #495057; margin-bottom: 15px;">
                  <i class="fas fa-signature me-2"></i>Your Digital Signature
                </h4>
                <div class="signature-container" style="border: 3px solid #28a745; border-radius: 12px; padding: 20px; background: white; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                  <img src="{{ $reservation->signature_data }}" alt="Digital Signature" style="max-width: 300px; max-height: 120px; border-radius: 6px;">
                  <div class="mt-3">
                    <small class="text-muted" style="font-weight: 500;">
                      <i class="fas fa-calendar-alt me-1"></i>
                      Signed on {{ $reservation->created_at->format('M d, Y \a\t g:i A') }}
                    </small>
                  </div>
                </div>
              </div>
            @endif
          </div>
        </section>
        @endif

        <!-- What to Expect -->
        <section class="expectations">
          <h2 class="section-title">What to Expect</h2>
          <div class="expectation-content">
            <h3>{{ $reservation->packageVariant->package->name }}</h3>
            <p class="sub-text">Get ready for an unforgettable adventure experience! Our professional team is committed to providing you with the best possible adventure while ensuring your safety and enjoyment throughout the journey.</p>
            
            <div class="highlights-grid">
              <div class="highlight-item">
                <i class="fas fa-map-marked-alt"></i>
                <h4>Expert Guides</h4>
                <p>Professional and experienced guides will lead your adventure</p>
              </div>
              <div class="highlight-item">
                <i class="fas fa-shield-alt"></i>
                <h4>Safety First</h4>
                <p>All safety equipment and briefings provided</p>
              </div>
              <div class="highlight-item">
                <i class="fas fa-camera"></i>
                <h4>Photo Opportunities</h4>
                <p>Capture amazing moments throughout your adventure</p>
              </div>
              <div class="highlight-item">
                <i class="fas fa-heart"></i>
                <h4>Memorable Experience</h4>
                <p>Create lasting memories with friends and family</p>
              </div>
            </div>
          </div>
        </section>

        <!-- Important Information -->
        <section class="important-info">
          <h2 class="section-title">Important Information</h2>
          
          <div class="info-grid">
            <div class="info-card">
              <h4><i class="fas fa-clock"></i> Check-in Time</h4>
              <p>Please arrive at least 30 minutes before your scheduled time slot for check-in and safety briefing.</p>
            </div>
            
            <div class="info-card">
              <h4><i class="fas fa-map-marker-alt"></i> Meeting Point</h4>
              <p>Our adventure center is located at [Address]. Look for our branded signage and staff members.</p>
            </div>
            
            <div class="info-card">
              <h4><i class="fas fa-phone"></i> Contact Information</h4>
              <p>For any questions or changes, contact us at:<br>
              Phone: +880 1234-567890<br>
              Email: info@atvutvadventures.com</p>
            </div>
            
            <div class="info-card">
              <h4><i class="fas fa-exclamation-triangle"></i> Weather Policy</h4>
              <p>Adventures may be rescheduled due to severe weather conditions. We'll notify you in advance if needed.</p>
            </div>
          </div>
        </section>

        <!-- Terms & Conditions -->
        <section class="terms-section">
          <h2 class="section-title">Terms & Conditions</h2>
          
          <div class="terms-content">
            <div class="term-group">
              <h4><i class="fas fa-calendar-check"></i> Booking Policy</h4>
              <ul class="content-list">
                <li>Bookings must be made at least 24 hours in advance</li>
                <li>Full payment is required at the time of booking</li>
                <li>A confirmation email will be sent upon successful booking</li>
                <li>Booking codes are unique and non-transferable</li>
              </ul>
            </div>

            <div class="term-group">
              <h4><i class="fas fa-user-check"></i> Participant Requirements</h4>
              <ul class="content-list">
                <li>Minimum age for participants is 18 years (or as specified for the package)</li>
                <li>Participants must have a valid government-issued ID</li>
                <li>Participants must be in good physical health</li>
                <li>No prior experience is necessary for most packages</li>
                <li>All participants must sign a liability waiver before the adventure</li>
              </ul>
            </div>

            <div class="term-group">
              <h4><i class="fas fa-shield-alt"></i> Safety Rules</h4>
              <ul class="content-list">
                <li>All provided safety equipment must be worn at all times</li>
                <li>Follow the guide's instructions strictly</li>
                <li>Reckless behavior is strictly prohibited</li>
                <li>Alcohol or drugs are not permitted before or during the adventure</li>
                <li>Stay within designated areas and follow marked trails</li>
              </ul>
            </div>

            <div class="term-group">
              <h4><i class="fas fa-undo"></i> Cancellation Policy</h4>
              <ul class="content-list">
                <li>Cancellations made more than 72 hours before the adventure: Full refund</li>
                <li>Cancellations made between 24-72 hours before: 50% refund</li>
                <li>Cancellations made less than 24 hours before: No refund</li>
                <li>No-shows will not be eligible for refunds</li>
                <li>Weather-related cancellations may be rescheduled at no additional cost</li>
              </ul>
            </div>

            <div class="term-group">
              <h4><i class="fas fa-camera"></i> Photography & Media</h4>
              <ul class="content-list">
                <li>Professional photos may be taken during your adventure</li>
                <li>Photos may be used for promotional purposes unless you opt out</li>
                <li>Personal cameras and phones are allowed but use at your own risk</li>
                <li>We recommend securing your devices before the adventure</li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Contact & Support -->
        <section class="contact-section">
          <h2 class="section-title">Need Help?</h2>
          <div class="contact-grid">
            <div class="contact-item">
              <i class="fas fa-phone"></i>
              <h4>Call Us</h4>
              <p>+880 1234-567890</p>
              <small>Available 9 AM - 6 PM</small>
            </div>
            <div class="contact-item">
              <i class="fas fa-envelope"></i>
              <h4>Email Us</h4>
              <p>info@atvutvadventures.com</p>
              <small>Response within 24 hours</small>
            </div>
            <div class="contact-item">
              <i class="fas fa-comments"></i>
              <h4>Live Chat</h4>
              <p>Available on our website</p>
              <small>Real-time support</small>
            </div>
          </div>
        </section>
      </main>
    </div>
  </div>
@endsection


