# SMS Service Environment Variables Setup

## 📋 Required Environment Variables

Add the following variables to your `.env` file to configure the SMS service:

### 🔧 Core SMS Configuration

```env
# SMS Service Configuration
SMS_PROVIDER=mim
SMS_ENABLED=true
SMS_QUEUE_ENABLED=true
SMS_MAX_RETRIES=3
SMS_RETRY_DELAY=5
SMS_RATE_LIMIT=100
SMS_RATE_LIMIT_HOUR=1000
SMS_RATE_LIMIT_DAY=10000
SMS_ADMIN_PHONE_NUMBERS=+8801712345678,+8801812345678
```

### 🔑 MiM SMS API Credentials

```env
# MiM SMS API Configuration
MIM_SMS_API_KEY=your_api_key_here
MIM_SMS_SENDER_ID=your_sender_id_here
MIM_SMS_BASE_URL=https://api.mimsms.com
MIM_SMS_USERNAME=your_username_here
MIM_SMS_PASSWORD=your_password_here
MIM_SMS_TIMEOUT=30
```

### 📝 SMS Templates (Optional - Override Defaults)

```env
# SMS Template Overrides (Optional)
SMS_BOOKING_CONFIRMATION_TEMPLATE=Your booking #{booking_code} is confirmed for {date} at {time}. Total: {amount} BDT. Location: {location}. Contact: {contact_number}
SMS_PAYMENT_CONFIRMATION_TEMPLATE=Payment received for booking #{booking_code}. Thank you for choosing our adventure service!
SMS_BOOKING_REMINDER_TEMPLATE=Reminder: Your adventure is tomorrow at {time}. Location: {location}. Contact: {contact_number}
SMS_BOOKING_CANCELLED_TEMPLATE=Your booking #{booking_code} has been cancelled. Contact us for refund details.
SMS_ADMIN_NEW_BOOKING_TEMPLATE=New booking #{booking_code} received for {date} at {time}. Amount: {amount} BDT. Customer: {customer_name}
```

## 🔍 How to Get MiM SMS Credentials

### 1. Account Registration
- Visit [MiM SMS](https://www.mimsms.com)
- Register for an account
- Complete account verification

### 2. API Credentials
- **API Key**: Generated in your MiM SMS dashboard
- **Username**: Your MiM SMS account username
- **Password**: Your MiM SMS account password
- **Sender ID**: Pre-approved sender identifier (contact MiM SMS support)

### 3. Sender ID Approval
- Contact MiM SMS support to get your sender ID approved
- Provide business documentation if required
- Wait for approval (usually 1-3 business days)

## ⚙️ Configuration Validation

After setting up the environment variables, run the following command to test the configuration:

```bash
php artisan sms:test
```

This will:
- ✅ Check if all required environment variables are set
- ✅ Validate phone number formatting
- ✅ Test API connectivity
- ✅ Verify template rendering
- ✅ Optionally send a test SMS (if phone number provided)

## 🚨 Security Notes

### Environment Variable Security
- Never commit `.env` file to version control
- Use strong, unique passwords for API credentials
- Rotate API keys regularly
- Monitor API usage for suspicious activity

### Production Checklist
- [ ] All environment variables are set
- [ ] API credentials are valid and active
- [ ] Sender ID is approved
- [ ] Account has sufficient balance
- [ ] Rate limits are configured appropriately
- [ ] Error monitoring is set up

## 📊 Testing Your Setup

### 1. Basic Configuration Test
```bash
php artisan sms:test
```

### 2. Test with Specific Phone Number
```bash
php artisan sms:test --phone=+8801712345678
```

### 3. Test Specific Template
```bash
php artisan sms:test --template=booking_confirmation
```

### 4. Test Custom Message
```bash
php artisan sms:test --phone=+8801712345678 --message="Test message from ATV/UTV system"
```

## 🔧 Troubleshooting

### Common Issues

#### 1. "SMS service is not configured"
- Check if all required environment variables are set
- Verify API credentials are correct
- Ensure `.env` file is in the correct location

#### 2. "Authentication failed"
- Verify API key, username, and password
- Check if account is active
- Contact MiM SMS support if credentials are correct

#### 3. "Invalid sender ID"
- Contact MiM SMS support for sender ID approval
- Provide business documentation if required
- Wait for approval process

#### 4. "Insufficient balance"
- Recharge your MiM SMS account
- Check balance using the test command
- Monitor usage to prevent depletion

#### 5. "Rate limit exceeded"
- Reduce SMS sending frequency
- Implement proper rate limiting
- Contact MiM SMS for higher limits

## 📞 Support

### MiM SMS Support
- **Email**: support@mimsms.com
- **Phone**: +880-XXX-XXXXXXX
- **Documentation**: https://docs.mimsms.com

### Application Support
- Check Laravel logs: `storage/logs/laravel.log`
- Run SMS test command for diagnostics
- Review SMS logs in database: `sms_logs` table

## 🔄 Next Steps

After setting up environment variables:

1. **Test Configuration**: Run `php artisan sms:test`
2. **Verify Templates**: Check all SMS templates render correctly
3. **Test Integration**: Send test SMS to verify end-to-end functionality
4. **Monitor Logs**: Check SMS logs for any issues
5. **Proceed to Phase 3**: Booking System Integration

---

**Note**: Keep your API credentials secure and never share them publicly. If credentials are compromised, contact MiM SMS immediately to regenerate them.
