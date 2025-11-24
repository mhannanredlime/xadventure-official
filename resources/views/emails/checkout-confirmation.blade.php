<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Confirmation - Multiple Bookings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #8e44ad;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .booking-summary {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #8e44ad;
        }
        .booking-item {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin: 10px 0;
            border-left: 3px solid #8e44ad;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 6px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        .detail-value {
            color: #333;
        }
        .cta-button {
            display: inline-block;
            background-color: #8e44ad;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        .success-badge {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #27ae60;
            margin: 20px 0;
            text-align: center;
        }
        .total-section {
            background-color: #e8f5e8;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #27ae60;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Checkout Complete!</h1>
        <p>Multiple adventures booked successfully</p>
    </div>

    <div class="content">
        <h2>Hello {{ $customer->name }}!</h2>
        
        <p>Fantastic! You've successfully booked multiple adventures with us. We're excited to provide you with amazing experiences!</p>

        <div class="success-badge">
            <strong>✅ {{ count($reservations) }} Booking(s) Confirmed</strong><br>
            <strong>Total Amount:</strong> ৳{{ number_format($totalAmount, 2) }}
        </div>

        <div class="booking-summary">
            <h3>📋 Your Bookings Summary</h3>
            
            @foreach($reservations as $reservation)
            <div class="booking-item">
                <h4>{{ $reservation->packageVariant->package->name }}</h4>
                
                <div class="detail-row">
                    <span class="detail-label">Booking Code:</span>
                    <span class="detail-value"><strong>{{ $reservation->booking_code }}</strong></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">{{ $reservation->date->format('l, F j, Y') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Time:</span>
                    <span class="detail-value">{{ $reservation->report_time->format('g:i A') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Duration:</span>
                    <span class="detail-value">{{ $reservation->packageVariant->duration }} hours</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Participants:</span>
                    <span class="detail-value">{{ $reservation->participants }} person(s)</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Amount:</span>
                    <span class="detail-value"><strong>৳{{ number_format($reservation->total_amount, 2) }}</strong></span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="total-section">
            <h3>💰 Total Amount Paid</h3>
            <h2 style="color: #27ae60; margin: 10px 0;">৳{{ number_format($totalAmount, 2) }}</h2>
            <p>All payments have been processed successfully</p>
        </div>

        <div style="text-align: center;">
            <a href="{{ $receiptLink }}" class="cta-button">View All Receipts & Details</a>
        </div>

        <div class="booking-summary">
            <h3>📍 Important Information</h3>
            <ul>
                <li><strong>Arrival Time:</strong> Please arrive 15 minutes before each scheduled time</li>
                <li><strong>What to Bring:</strong> Comfortable clothes, closed-toe shoes, and a sense of adventure!</li>
                <li><strong>Safety:</strong> All safety equipment will be provided for each adventure</li>
                <li><strong>Weather:</strong> Adventures run rain or shine (unless severe weather)</li>
                <li><strong>Multiple Bookings:</strong> Each booking is independent - you can attend them separately</li>
            </ul>
        </div>

        <div class="booking-summary">
            <h3>📞 Need Help?</h3>
            <p>If you have any questions about your bookings or need to make changes, please contact us:</p>
            <ul>
                <li><strong>Phone:</strong> +880 1712 345678</li>
                <li><strong>Email:</strong> info@atvutv.com</li>
                <li><strong>Hours:</strong> 9:00 AM - 6:00 PM (Daily)</li>
            </ul>
        </div>

        <p>Thank you for choosing us for multiple adventures! We can't wait to provide you with unforgettable experiences.</p>

        <p>Best regards,<br>
        <strong>The ATV/UTV Adventures Team</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>© {{ date('Y') }} ATV/UTV Adventures. All rights reserved.</p>
    </div>
</body>
</html>


