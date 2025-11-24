<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - {{ $reservation->booking_code }}</title>
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
            background-color: #27ae60;
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
        .payment-details {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #27ae60;
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
            background-color: #27ae60;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>💳 Payment Confirmed!</h1>
        <p>Your payment has been successfully processed</p>
    </div>

    <div class="content">
        <h2>Hello {{ $customer->name }}!</h2>
        
        <p>Thank you for your payment! We have successfully received your payment for your adventure booking.</p>

        <div class="success-badge">
            <strong>✅ Payment Successful</strong><br>
            <strong>Transaction ID:</strong> {{ $payment->transaction_id ?? 'N/A' }}
        </div>

        <div class="payment-details">
            <h3>💰 Payment Details</h3>
            
            <div class="detail-row">
                <span class="detail-label">Booking Reference:</span>
                <span class="detail-value">{{ $reservation->booking_code }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Package:</span>
                <span class="detail-value">{{ $package->name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Payment Amount:</span>
                <span class="detail-value"><strong>৳{{ number_format($payment->amount, 2) }}</strong></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value">{{ $payment->payment_method ?? 'Online Payment' }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Payment Date:</span>
                <span class="detail-value">{{ $payment->created_at->format('l, F j, Y g:i A') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value" style="color: #27ae60; font-weight: bold;">Paid</span>
            </div>
        </div>

        <div class="payment-details">
            <h3>📅 Your Adventure Details</h3>
            
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value">{{ $reservation->date->format('l, F j, Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Time:</span>
                <span class="detail-value">{{ $reservation->report_time->format('g:i A') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Participants:</span>
                <span class="detail-value">{{ $reservation->participants }} person(s)</span>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="{{ $receiptLink }}" class="cta-button">View Full Receipt</a>
        </div>

        <div class="payment-details">
            <h3>📞 Need Assistance?</h3>
            <p>If you have any questions about your payment or booking, please contact us:</p>
            <ul>
                <li><strong>Phone:</strong> +880 1712 345678</li>
                <li><strong>Email:</strong> info@atvutv.com</li>
                <li><strong>Hours:</strong> 9:00 AM - 6:00 PM (Daily)</li>
            </ul>
        </div>

        <p>We're looking forward to providing you with an amazing adventure experience!</p>

        <p>Best regards,<br>
        <strong>The ATV/UTV Adventures Team</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>© {{ date('Y') }} ATV/UTV Adventures. All rights reserved.</p>
    </div>
</body>
</html>


