# SMS Notifications Implementation for ATV/UTV Booking System

## Overview

This document outlines the complete SMS notification system implemented for the ATV/UTV booking platform. The system sends automated SMS notifications to customers and administrators at various stages of the booking and payment process.

## 🎯 Features Implemented

### ✅ Already Working
1. **Booking Confirmation SMS** - Sent when a new booking is created
2. **Payment Confirmation SMS** - Sent when payment is successfully processed
3. **Admin Notifications** - Sent to admin when new bookings are received
4. **Booking Cancellation SMS** - Sent when bookings are cancelled

### 🆕 Newly Added
1. **Booking Status Update SMS** - Sent when booking status changes in admin panel
2. **Payment Status Update SMS** - Sent when payment status changes in admin panel
3. **Enhanced Status Tracking** - Comprehensive status change monitoring

## 📱 SMS Templates

### Customer Notifications

| Template Name | Trigger | Message Variables |
|---------------|---------|-------------------|
| `booking_confirmation` | New booking created | `{booking_code}`, `{date}`, `{time}`, `{amount}`, `{customer_name}`, `{package_name}`, `{contact_number}` |
| `payment_confirmation` | Payment completed | `{booking_code}`, `{amount}`, `{payment_method}`, `{transaction_id}`, `{customer_name}`, `{package_name}` |
| `booking_cancelled` | Booking cancelled | `{booking_code}`, `{refund_amount}`, `{customer_name}`, `{package_name}`, `{contact_number}` |
| `booking_completed` | Adventure completed | `{booking_code}` |
| `booking_reactivated` | Cancelled booking reactivated | `{booking_code}`, `{date}`, `{time}` |
| `booking_status_update` | General status change | `{booking_code}`, `{new_status}` |
| `payment_failed` | Payment failed | `{booking_code}` |
| `payment_refunded` | Payment refunded | `{booking_code}`, `{amount}` |
| `payment_status_update` | Payment status change | `{booking_code}`, `{new_status}` |

### Admin Notifications

| Template Name | Trigger | Message Variables |
|---------------|---------|-------------------|
| `admin_new_booking` | New booking received | `{booking_code}`, `{date}`, `{customer_name}`, `{amount}`, `{package_name}` |

## 🔄 Event-Driven Architecture

### Events Created

1. **`BookingConfirmed`** - Fired when booking is created
2. **`PaymentConfirmed`** - Fired when payment is completed
3. **`BookingCancelled`** - Fired when booking is cancelled
4. **`BookingStatusUpdated`** - Fired when booking status changes
5. **`PaymentStatusUpdated`** - Fired when payment status changes

### Listeners Implemented

1. **`SendBookingConfirmationSms`** - Sends confirmation SMS to customer
2. **`SendPaymentConfirmationSms`** - Sends payment confirmation SMS
3. **`SendBookingCancellationSms`** - Sends cancellation SMS
4. **`SendAdminNewBookingSms`** - Sends notification to admin
5. **`SendBookingStatusUpdateSms`** - Sends status update SMS
6. **`SendPaymentStatusUpdateSms`** - Sends payment status update SMS

## 🎛️ Configuration

### Phone Number Validation

The system now includes comprehensive phone number validation and formatting:

- **Automatic +880 Prefix**: All phone numbers are automatically formatted with Bangladesh country code
- **Multiple Input Formats**: Accepts various input formats (01712345678, 1712345678, +8801712345678)
- **Real-time Validation**: Frontend validation with helpful error messages
- **Backend Validation**: Server-side validation using PhoneNumberService

### Environment Variables

Add these to your `.env` file:

```env
# SMS Provider Configuration
SMS_PROVIDER=mim
SMS_ENABLED=true
SMS_QUEUE_ENABLED=true
SMS_ADMIN_PHONE_NUMBERS=+8801712345678,+8801812345678

# MIM SMS API Configuration
MIM_SMS_API_KEY=your_api_key_here
MIM_SMS_SENDER_ID=ATVUTV
MIM_SMS_BASE_URL=https://api.mimsms.com
MIM_SMS_USERNAME=your_username
MIM_SMS_PASSWORD=your_password

# SMS Templates (optional - defaults provided)
SMS_BOOKING_CONFIRMATION_TEMPLATE=Your booking #{booking_code} is confirmed for {date} at {time}. Total: {amount} BDT
SMS_PAYMENT_CONFIRMATION_TEMPLATE=Payment received for booking #{booking_code}. Thank you!
SMS_BOOKING_CANCELLED_TEMPLATE=Your booking #{booking_code} has been cancelled. Contact us for refund.
SMS_BOOKING_COMPLETED_TEMPLATE=Your adventure #{booking_code} has been completed. Thank you for choosing us!
SMS_BOOKING_REACTIVATED_TEMPLATE=Your booking #{booking_code} has been reactivated. See you on {date} at {time}!
SMS_BOOKING_STATUS_UPDATE_TEMPLATE=Your booking #{booking_code} status updated to {new_status}. Contact us if needed.
SMS_PAYMENT_FAILED_TEMPLATE=Payment failed for booking #{booking_code}. Please contact us to resolve.
SMS_PAYMENT_REFUNDED_TEMPLATE=Refund processed for booking #{booking_code}. Amount: {amount} BDT
SMS_PAYMENT_STATUS_UPDATE_TEMPLATE=Payment status for booking #{booking_code} updated to {new_status}.
SMS_ADMIN_NEW_BOOKING_TEMPLATE=New booking #{booking_code} received for {date}. Check admin panel.
```

## 🚀 Implementation Details

### 1. Phone Number Processing Flow

```php
// In BookingController::processBooking()
$phoneService = new PhoneNumberService();
$phoneValidation = $phoneService->validateAndFormat($request->customer_phone);

if (!$phoneValidation['valid']) {
    return redirect()->back()
        ->withErrors(['customer_phone' => $phoneValidation['error']])
        ->withInput();
}

// Use formatted phone number for customer creation
$customer = Customer::updateOrCreate(
    ['email' => $request->customer_email],
    [
        'name' => $request->customer_name,
        'phone' => $phoneValidation['formatted'], // +8801712345678
        'address' => $request->customer_address,
        'user_id' => $user->id,
    ]
);
```

### 2. Booking Creation Flow

```php
// In BookingController::processBooking()
foreach ($reservations as $reservation) {
    // Load the reservation with relationships for SMS
    $reservationWithRelations = Reservation::with(['customer', 'packageVariant.package'])->find($reservation->id);
    event(new BookingConfirmed($reservationWithRelations, [
        'payment_method' => $request->payment_method,
        'transaction_id' => $transactionId,
    ]));
}
```

### 2. Status Update Flow

```php
// In ReservationController::update()
if ($oldBookingStatus !== $validated['booking_status']) {
    $reservationWithRelations = Reservation::with(['customer', 'packageVariant.package'])->find($reservation->id);
    event(new BookingStatusUpdated($reservationWithRelations, $oldBookingStatus, $validated['booking_status'], [
        'updated_by' => 'Admin',
        'update_time' => now()->toDateTimeString(),
    ]));
}
```

### 3. Payment Processing Flow

```php
// In AmarPayService::processIPN()
if ($status === 'VALID') {
    // Update payment and reservation status
    $payment->update(['status' => 'completed']);
    $reservation->update(['payment_status' => 'paid', 'booking_status' => 'confirmed']);
    
    // Fire payment confirmation event
    $paymentWithRelations = Payment::with(['reservation.customer', 'reservation.packageVariant.package'])->find($payment->id);
    event(new PaymentConfirmed($paymentWithRelations, [
        'transaction_id' => $transactionId,
        'payment_method' => 'amarpay',
        'amount' => $amount,
    ]));
}
```

## 📊 Status Change Tracking

### Booking Status Transitions

| From | To | SMS Sent | Template Used |
|------|----|----------|---------------|
| `pending` | `confirmed` | ✅ | `booking_confirmed` |
| `pending` | `cancelled` | ✅ | `booking_cancelled` |
| `confirmed` | `cancelled` | ✅ | `booking_cancelled` |
| `confirmed` | `completed` | ✅ | `booking_completed` |
| `cancelled` | `confirmed` | ✅ | `booking_reactivated` |

### Payment Status Transitions

| From | To | SMS Sent | Template Used |
|------|----|----------|---------------|
| `pending` | `completed` | ✅ | `payment_confirmation` |
| `partial` | `completed` | ✅ | `payment_confirmation` |
| `pending` | `failed` | ✅ | `payment_failed` |
| `completed` | `refunded` | ✅ | `payment_refunded` |
| `failed` | `completed` | ✅ | `payment_confirmation` |

## 🔧 Queue Configuration

SMS notifications are processed through Laravel queues for better performance:

```php
// In SMS listeners
public $delay = 5; // Delay SMS sending by 5 seconds
```

### Queue Setup

1. **Configure Queue Driver** in `.env`:
   ```env
   QUEUE_CONNECTION=database
   ```

2. **Run Queue Migration**:
   ```bash
   php artisan queue:table
   php artisan migrate
   ```

3. **Start Queue Worker**:
   ```bash
   php artisan queue:work
   ```

## 📝 Logging and Monitoring

### Comprehensive SMS Logging

The system now includes detailed logging at every step of the SMS process:

#### Phone Number Validation Logs
```php
Log::info('Phone number validation started', [
    'original' => '01 887 983638',
    'clean' => '01887983638',
    'length' => 11,
]);

Log::info('Phone number validation successful', [
    'original' => '01 887 983638',
    'clean' => '01887983638',
    'formatted' => '8801887983638',
]);
```

#### Checkout Process Logs
```php
Log::info('Starting checkout process', [
    'customer_name' => 'Shakil Ahamed',
    'customer_email' => 'revengertousif@gmail.com',
    'customer_phone' => '01 887 983638',
    'payment_method' => 'credit_card',
]);

Log::info('Phone validation result', [
    'valid' => true,
    'formatted' => '8801887983638',
]);
```

#### Event Dispatching Logs
```php
Log::info('Dispatching BookingConfirmed event', [
    'reservation_id' => 123,
    'booking_code' => 'BK20250102001',
    'customer_phone' => '8801887983638',
    'customer_name' => 'Shakil Ahamed',
    'package_name' => 'ATV/UTV Trail Rides',
    'total_amount' => 1380.00,
]);
```

#### SMS Listener Logs
```php
Log::info('SendBookingConfirmationSms listener started', [
    'reservation_id' => 123,
    'booking_code' => 'BK20250102001',
]);

Log::info('SMS template variables prepared', [
    'variables' => ['booking_code' => 'BK20250102001', ...],
    'template_name' => 'booking_confirmation',
]);

Log::info('SMS message rendered', [
    'message_length' => 156,
    'message_preview' => 'Your booking #BK20250102001 is confirmed...',
]);
```

#### SMS Service Logs
```php
Log::info('Attempting to send SMS', [
    'phone_number' => '8801887983638',
    'message_length' => 156,
    'reservation_id' => 123,
]);

Log::info('SMS service response received', [
    'success' => true,
    'message_id' => 'msg_123456',
    'reservation_id' => 123,
]);
```

### Error Handling

Comprehensive error handling with detailed logging:

```php
Log::error('Failed to send booking confirmation SMS', [
    'reservation_id' => $reservation->id,
    'customer_phone' => $customer->phone,
    'error' => $response->errorMessage,
    'booking_code' => $reservation->booking_code,
]);
```

### Real-time Log Monitoring

Use the provided log monitoring script to track SMS activity in real-time:

```bash
php monitor_sms_logs.php
```

This script will:
- Monitor the Laravel log file for SMS-related entries
- Display them with color-coded log levels
- Show real-time SMS activity during checkout

## 🧪 Testing

### Test SMS Templates

You can test SMS templates using the SMS service directly:

```php
$smsService = new \App\Services\MimSmsService();
$templateService = new \App\Services\SmsTemplateService();

$variables = [
    'booking_code' => 'BK20250102001',
    'date' => '2025-01-15',
    'time' => '09:00 AM',
    'amount' => '2,500.00',
    'customer_name' => 'John Doe',
    'package_name' => 'ATV Adventure',
];

$message = $templateService->renderTemplate('booking_confirmation', $variables);
$response = $smsService->sendWithLogging('+8801712345678', $message);
```

## 🔒 Security Considerations

1. **Phone Number Validation** - All phone numbers are validated and formatted with +880 prefix
2. **Rate Limiting** - SMS sending is rate-limited to prevent abuse
3. **Error Handling** - Failed SMS are logged and can be retried
4. **Queue Processing** - SMS are processed asynchronously to prevent blocking
5. **Input Sanitization** - Phone numbers are cleaned and validated before processing

## 📈 Performance Optimization

1. **Queue Processing** - SMS are sent asynchronously
2. **Batch Processing** - Multiple SMS can be processed in batches
3. **Caching** - SMS templates are cached for better performance
4. **Retry Logic** - Failed SMS are automatically retried

## 🚨 Troubleshooting

### Common Issues

1. **SMS Not Sending**
   - Check SMS provider credentials
   - Verify phone number format
   - Check queue worker status

2. **Template Variables Not Replaced**
   - Ensure all required variables are provided
   - Check template syntax

3. **Queue Jobs Failing**
   - Check queue worker logs
   - Verify database connection
   - Check for syntax errors in listeners

### Debug Commands

```bash
# Check queue status
php artisan queue:work --verbose

# Clear failed jobs
php artisan queue:flush

# Retry failed jobs
php artisan queue:retry all

# Check SMS logs
tail -f storage/logs/laravel.log | grep SMS
```

## 📞 Support

For SMS-related issues:
1. Check the logs in `storage/logs/laravel.log`
2. Verify SMS provider configuration
3. Test SMS templates manually
4. Contact the development team

---

**Last Updated:** January 2025  
**Version:** 1.0  
**Status:** ✅ Complete and Tested

