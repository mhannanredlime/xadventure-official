<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - {{ $reservation->booking_code }}</title>
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
            background-color: #2c3e50;
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
        .booking-details {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
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
            background-color: #3498db;
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
        .highlight {
            background-color: #e8f5e8;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #27ae60;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Booking Confirmed!</h1> 
        <p>Your adventure awaits</p>
    </div>

    <div class="content">
        <h2>Hello {{ $customer->name }}!</h2>
        
        <p>Great news! Your booking has been confirmed and we're excited to have you join us for an amazing adventure.</p>

        <div class="highlight">
            <strong>Booking Reference:</strong> {{ $reservation->booking_code }}<br>
            <strong>Status:</strong> Confirmed ✅
        </div>

        <div class="booking-details">
            <h3>📋 Booking Details</h3>
            
            <div class="detail-row">
                <span class="detail-label">Package:</span>
                <span class="detail-value">{{ $package->name }}</span>
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
                <span class="detail-value">{{ $packageVariant->duration }} hours</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Participants:</span>
                <span class="detail-value">{{ $reservation->participants }} person(s)</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Total Amount:</span>
                <span class="detail-value"><strong>৳{{ number_format($reservation->total_amount, 2) }}</strong></span>
            </div>
        </div>

        <div class="booking-details">
            <h3>📍 Important Information</h3>
            <ul>
                <li><strong>Arrival Time:</strong> Please arrive 15 minutes before your scheduled time</li>
                <li><strong>What to Bring:</strong> Comfortable clothes, closed-toe shoes, and a sense of adventure!</li>
                <li><strong>Safety:</strong> All safety equipment will be provided</li>
                <li><strong>Weather:</strong> Adventures run rain or shine (unless severe weather)</li>
            </ul>
        </div>

        <div style="text-align: center;">
            <a href="{{ $receiptLink }}" class="cta-button">View Full Receipt & Details</a>
        </div>

        <div class="booking-details">
            <h3>📞 Need Help?</h3>
            <p>If you have any questions or need to make changes to your booking, please contact us:</p>
            <ul>
                <li><strong>Phone:</strong> +880 1712 345678</li>
                <li><strong>Email:</strong> info@atvutv.com</li>
                <li><strong>Hours:</strong> 9:00 AM - 6:00 PM (Daily)</li>
            </ul>
        </div>

        <p>We can't wait to see you for your adventure! Get ready for an unforgettable experience.</p>

        <p>Best regards,<br>
        <strong>The ATV/UTV Adventures Team</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>© {{ date('Y') }} ATV/UTV Adventures. All rights reserved.</p>
    </div>
</body>
</html>


