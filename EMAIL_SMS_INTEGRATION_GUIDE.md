# Email & SMS Integration Guide

## Overview

The ATV/UTV booking system now sends both **email** and **SMS** notifications to customers for all booking-related events. This ensures customers receive notifications through their preferred communication method.

## 🎯 Features Implemented

### ✅ Dual Notification System
- **Email Notifications**: Professional HTML emails with detailed booking information
- **SMS Notifications**: Concise text messages with essential details and links
- **Synchronized Events**: Both email and SMS are sent simultaneously for the same events
- **Production URLs**: All links use production URLs, never localhost

### 📧 Email Notifications

#### Email Types
1. **Booking Confirmation Email** - Sent when a new booking is created
2. **Payment Confirmation Email** - Sent when payment is successfully processed  
3. **Checkout Confirmation Email** - Sent when multiple bookings are completed in one checkout

#### Email Features
- **Professional HTML Design**: Beautiful, responsive email templates
- **Detailed Information**: Complete booking details, contact information, and instructions
- **Receipt Links**: Direct links to view full booking receipts
- **Mobile Responsive**: Optimized for viewing on all devices

### 📱 SMS Notifications

#### SMS Types
1. **Booking Confirmation SMS** - Concise booking confirmation
2. **Payment Confirmation SMS** - Payment success notification
3. **Checkout Confirmation SMS** - Multiple booking confirmation

#### SMS Features
- **Short Links**: Optimized URLs to save character space
- **Essential Information**: Key booking details in minimal characters
- **Quick Access**: Direct links to booking receipts

## 🔧 Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="ATV/UTV Adventures"
MAIL_ENABLED=true

# SMS Configuration (already configured)
SMS_PROVIDER=mim
SMS_ENABLED=true
SMS_BASE_URL=https://yourdomain.com

# URL Configuration (prevents localhost URLs)
SMS_BASE_URL=https://yourdomain.com
# Alternative: PRODUCTION_URL=https://yourdomain.com
```

### Email Service Providers

The system supports all Laravel mail drivers:

- **SMTP**: Gmail, Outlook, custom SMTP servers
- **Mailgun**: Professional email service
- **SES**: Amazon Simple Email Service
- **Postmark**: Transactional email service
- **Log**: For development (emails saved to log files)

## 📋 Event Flow

### Booking Confirmation Flow
```
1. Customer completes booking
2. BookingConfirmed event is fired
3. Both listeners are triggered:
   ├── SendBookingConfirmationSms (SMS)
   └── SendBookingConfirmationEmail (Email)
4. Customer receives both notifications
```

### Payment Confirmation Flow
```
1. Payment is processed successfully
2. PaymentConfirmed event is fired
3. Both listeners are triggered:
   ├── SendPaymentConfirmationSms (SMS)
   └── SendPaymentConfirmationEmail (Email)
4. Customer receives both notifications
```

### Checkout Confirmation Flow
```
1. Multiple bookings completed in checkout
2. CheckoutCompleted event is fired
3. Both listeners are triggered:
   ├── SendCheckoutConfirmationSms (SMS)
   └── SendCheckoutConfirmationEmail (Email)
4. Customer receives both notifications
```

## 🎨 Email Templates

### Template Structure
- **Header**: Company branding and confirmation message
- **Booking Details**: Complete booking information in organized sections
- **Important Information**: Arrival instructions, what to bring, safety info
- **Call-to-Action**: Direct link to view full receipt
- **Contact Information**: Support details and business hours
- **Footer**: Legal information and unsubscribe options

### Template Features
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Professional Styling**: Clean, modern design with company branding
- **Accessibility**: Proper contrast ratios and readable fonts
- **Cross-Client Compatibility**: Tested across major email clients

## 🔗 URL Management

### Production URL Enforcement
- **SMS URLs**: Always use production URLs (never localhost)
- **Email URLs**: Always use production URLs (never localhost)
- **Fallback System**: Multiple fallback options for URL generation
- **Error Handling**: Clear error messages if no valid URL is configured

### URL Types
- **SMS Short Links**: `/r/{id}` format for character efficiency
- **Email Full Links**: `/receipt/{booking_code}` format for user-friendliness
- **Checkout Links**: `/checkout/{checkout_id}` for multiple bookings

## 🧪 Testing

### Development Testing
```bash
# Test email sending (emails saved to log)
MAIL_MAILER=log php artisan queue:work

# Test SMS sending
php artisan sms:test --phone=8801887983638
```

### Production Testing
```bash
# Test with real email service
MAIL_MAILER=smtp php artisan queue:work

# Monitor email logs
tail -f storage/logs/laravel.log | grep "email"
```

## 📊 Monitoring

### Email Monitoring
- **Queue Jobs**: Monitor email queue jobs in the database
- **Log Files**: Check Laravel logs for email sending status
- **Email Service**: Monitor delivery rates through your email provider

### SMS Monitoring
- **SMS Logs**: Check SMS logs in the database
- **API Responses**: Monitor SMS provider API responses
- **Delivery Status**: Track SMS delivery status

## 🚨 Error Handling

### Email Errors
- **Retry Logic**: Failed emails are automatically retried (3 attempts)
- **Queue Jobs**: Failed jobs are logged for manual review
- **Fallback**: System continues to work even if email fails

### SMS Errors
- **Retry Logic**: Failed SMS are automatically retried (3 attempts)
- **Error Logging**: Detailed error logs for troubleshooting
- **Fallback**: System continues to work even if SMS fails

## 🔧 Troubleshooting

### Common Issues

#### Emails Not Sending
1. Check `MAIL_ENABLED=true` in `.env`
2. Verify SMTP credentials
3. Check queue worker is running
4. Review email logs for errors

#### SMS Not Sending
1. Check `SMS_ENABLED=true` in `.env`
2. Verify SMS provider configuration
3. Check phone number format
4. Review SMS logs for errors

#### Localhost URLs in Notifications
1. Set `SMS_BASE_URL=https://yourdomain.com` in `.env`
2. Or set `PRODUCTION_URL=https://yourdomain.com` in `.env`
3. Restart the application

### Debug Commands
```bash
# Check email configuration
php artisan config:show mail

# Check SMS configuration
php artisan config:show sms

# Test URL generation
php artisan tinker
>>> app(\App\Services\ShortlinkService::class)->getSmsShortlink($reservation)
```

## 📈 Performance

### Queue Processing
- **Asynchronous**: Both email and SMS are sent asynchronously
- **Parallel Processing**: Email and SMS are sent simultaneously
- **Retry Logic**: Automatic retry for failed notifications
- **Rate Limiting**: Built-in rate limiting for SMS

### Optimization
- **Template Caching**: Email templates are cached for performance
- **Database Indexing**: Optimized database queries for notifications
- **Memory Management**: Efficient memory usage for large notification batches

## 🔒 Security

### Data Protection
- **No Sensitive Data**: Only booking details, no payment information
- **Secure Links**: All links use HTTPS in production
- **Input Validation**: All data is validated before sending

### Privacy
- **Opt-out Support**: Customers can request to stop notifications
- **Data Retention**: Notification logs are retained according to policy
- **GDPR Compliance**: System supports data protection requirements

## 📞 Support

For technical support or questions about the email/SMS integration:

- **Email**: tech@yourdomain.com
- **Phone**: +880 1712 345678
- **Documentation**: Check this guide and inline code comments
- **Logs**: Review application logs for detailed error information

---

**Last Updated**: {{ date('Y-m-d') }}  
**Version**: 1.0  
**Status**: ✅ Production Ready



