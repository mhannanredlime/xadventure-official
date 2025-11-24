# Amar Pay Integration Guide

## Overview

This document describes the Amar Pay payment gateway integration for the ATV/UTV Adventure Tours booking system. The integration uses Amar Pay's JSON API for secure online payments.

## Features

- ✅ Secure online payment processing
- ✅ Support for all major credit cards, mobile banking, and digital wallets
- ✅ Real-time payment status updates
- ✅ Automatic booking confirmation on successful payment
- ✅ SMS notifications for payment confirmations
- ✅ Comprehensive logging and error handling

## Configuration

### Environment Variables

Add the following variables to your `.env` file:

```env
# Amar Pay Configuration
AMARPAY_STORE_ID=aamarpaytest
AMARPAY_SIGNATURE_KEY=dbb74894e82415a2f7ff0ec3a97e4183
AMARPAY_SANDBOX=true
AMARPAY_SUCCESS_URL=/payment/amarpay/success
AMARPAY_FAIL_URL=/payment/amarpay/fail
AMARPAY_CANCEL_URL=/payment/amarpay/cancel
AMARPAY_IPN_URL=/payment/amarpay/ipn
```

### Sandbox vs Live Environment

**Sandbox (Testing):**
- Store ID: `aamarpaytest`
- Signature Key: `dbb74894e82415a2f7ff0ec3a97e4183`
- Base URL: `https://sandbox.aamarpay.com/jsonpost.php`

**Live (Production):**
- Contact Amar Pay support for live credentials
- Set `AMARPAY_SANDBOX=false`
- Use live Store ID and Signature Key

## Integration Details

### 1. Payment Flow

1. Customer selects "Amar Pay" as payment method during checkout
2. System creates reservation with "pending" status
3. Amar Pay payment is initiated via JSON API
4. Customer is redirected to Amar Pay's secure payment gateway
5. After payment completion, customer is redirected back to our system
6. Payment status is verified and booking is confirmed
7. SMS notification is sent to customer

### 2. API Endpoints

#### Payment Initiation
- **URL:** `https://sandbox.aamarpay.com/jsonpost.php`
- **Method:** POST
- **Content-Type:** application/json

#### Callback URLs
- **Success:** `/payment/amarpay/success` (accepts GET and POST)
- **Failure:** `/payment/amarpay/fail` (accepts GET and POST)
- **Cancel:** `/payment/amarpay/cancel` (accepts GET and POST)
- **IPN:** `/payment/amarpay/ipn` (POST only)

**Note:** All callback URLs are excluded from CSRF protection to allow Amar Pay to send POST requests.

### 3. Database Schema

The integration uses the existing `payments` table with the following fields:
- `method`: 'amarpay'
- `payment_method`: 'amarpay'
- `status`: 'pending' → 'completed' or 'failed'
- `transaction_id`: Unique transaction identifier
- `payment_details`: JSON data from Amar Pay

**Note:** The `payment_status` field in the `reservations` table uses these enum values:
- `pending`: Payment not yet completed
- `partial`: Partial payment received
- `paid`: Payment completed successfully
- `refunded`: Payment has been refunded

## Testing

### Test Cards (Sandbox)

Use these test card numbers in sandbox mode:
- **Visa:** 4111111111111111
- **Mastercard:** 5555555555554444
- **American Express:** 378282246310005

### Test Scenarios

1. **Successful Payment:**
   - Use any test card with future expiry date
   - Payment should complete successfully
   - Booking should be confirmed
   - SMS notification should be sent

2. **Failed Payment:**
   - Use invalid card details
   - Payment should fail
   - Booking should remain pending
   - Customer should be redirected to checkout

3. **Cancelled Payment:**
   - Cancel payment on Amar Pay gateway
   - Customer should be redirected to checkout
   - Booking should remain pending

## Error Handling

### Common Issues

1. **Payment Initiation Failed:**
   - Check API credentials
   - Verify callback URLs are accessible
   - Check network connectivity

2. **"Method Not Allowed" Error:**
   - This occurs when Amar Pay sends POST requests to callback URLs
   - Ensure routes accept both GET and POST methods
   - Verify CSRF protection is disabled for callback routes

3. **Callback Verification Failed:**
   - Verify signature key
   - Check transaction ID format
   - Ensure payment record exists

4. **SMS Notifications Not Sent:**
   - Check SMS service configuration
   - Verify customer phone number format
   - Check SMS service logs

### Logging

All payment activities are logged in Laravel's log files:
- Payment initiation requests
- API responses
- Callback processing
- Error conditions

## Security Considerations

1. **HTTPS Required:** All callback URLs must use HTTPS in production
2. **Signature Verification:** All callbacks are verified using signature keys
3. **Transaction ID Validation:** Unique transaction IDs prevent duplicate processing
4. **Amount Verification:** Payment amounts are verified against booking totals

## Monitoring

### Key Metrics to Monitor

1. **Payment Success Rate:** Percentage of successful payments
2. **Average Processing Time:** Time from initiation to completion
3. **Failed Payment Reasons:** Common failure causes
4. **Callback Response Times:** Performance of callback processing

### Alerts

Set up alerts for:
- High payment failure rates
- Callback processing errors
- SMS notification failures
- API connectivity issues

## Support

### Amar Pay Support
- Email: support@aamarpay.com
- Documentation: https://aamarpay.readme.io/

### Technical Support
- Check Laravel logs for detailed error information
- Verify environment configuration
- Test with sandbox credentials first

## Migration to Live Environment

1. **Obtain Live Credentials:**
   - Contact Amar Pay support
   - Complete merchant verification process
   - Receive live Store ID and Signature Key

2. **Update Configuration:**
   - Set `AMARPAY_SANDBOX=false`
   - Update Store ID and Signature Key
   - Verify callback URLs are HTTPS

3. **Testing:**
   - Test with small amounts first
   - Verify all payment flows work correctly
   - Monitor logs for any issues

4. **Go Live:**
   - Update DNS if needed
   - Monitor payment processing closely
   - Have rollback plan ready

## Troubleshooting

### Payment Not Processing
1. Check API credentials
2. Verify callback URLs
3. Check network connectivity
4. Review error logs

### Callbacks Not Received
1. Verify URL accessibility
2. Check firewall settings
3. Ensure HTTPS is working
4. Test with webhook testing tools

### SMS Notifications Issues
1. Check SMS service configuration
2. Verify phone number formats
3. Check SMS service logs
4. Test SMS service independently
