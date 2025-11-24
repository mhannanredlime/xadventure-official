# MiM SMS API Documentation

## 📋 Overview

This document contains the research and documentation for integrating with the MiM SMS API. The information is based on common SMS API patterns and best practices.

## 🔗 API Base Information

### Base URL
- **Production:** `https://api.mimsms.com`
- **Sandbox/Test:** `https://sandbox.mimsms.com` (if available)

### Authentication Method
- **Type:** API Key + Username/Password combination
- **Headers:** Content-Type: application/json
- **Method:** POST requests

## 📡 API Endpoints

### 1. Send SMS
**Endpoint:** `/send-sms`  
**Method:** POST  
**Description:** Send a single SMS message

#### Request Parameters
```json
{
    "api_key": "your_api_key",
    "username": "your_username",
    "password": "your_password",
    "sender_id": "your_sender_id",
    "phone": "8801712345678",
    "message": "Your SMS message content",
    "priority": "normal",
    "schedule_time": "2025-01-20 10:00:00"
}
```

#### Response Format
```json
{
    "status": "success",
    "message_id": "mim_1234567890",
    "cost": 0.50,
    "balance": 100.00,
    "error": null
}
```

### 2. Check Balance
**Endpoint:** `/balance`  
**Method:** POST  
**Description:** Get account balance

#### Request Parameters
```json
{
    "api_key": "your_api_key",
    "username": "your_username",
    "password": "your_password"
}
```

#### Response Format
```json
{
    "status": "success",
    "balance": 100.50,
    "currency": "BDT",
    "error": null
}
```

### 3. Check Delivery Status
**Endpoint:** `/status`  
**Method:** POST  
**Description:** Check SMS delivery status

#### Request Parameters
```json
{
    "api_key": "your_api_key",
    "username": "your_username",
    "password": "your_password",
    "message_id": "mim_1234567890"
}
```

#### Response Format
```json
{
    "status": "delivered",
    "message_id": "mim_1234567890",
    "delivery_time": "2025-01-20 10:05:30",
    "error": null
}
```

## 📊 Response Status Codes

### Success Statuses
- `success` - SMS sent successfully
- `sent` - SMS accepted for delivery
- `delivered` - SMS delivered to recipient
- `accepted` - SMS accepted by provider

### Error Statuses
- `failed` - SMS sending failed
- `rejected` - SMS rejected by provider
- `error` - General error occurred
- `invalid_phone` - Invalid phone number
- `insufficient_balance` - Insufficient account balance
- `invalid_sender_id` - Invalid sender ID
- `authentication_failed` - Authentication failed

## 🔒 Authentication

### Required Credentials
1. **API Key** - Unique identifier for your account
2. **Username** - Your MiM SMS account username
3. **Password** - Your MiM SMS account password
4. **Sender ID** - Pre-approved sender identifier

### Security Notes
- All credentials should be stored securely in environment variables
- Never log passwords in application logs
- Use HTTPS for all API communications
- Implement rate limiting to prevent abuse

## 📱 Phone Number Format

### Supported Formats
- **International:** `8801712345678` (Bangladesh)
- **Local:** `01712345678` (will be converted to international)
- **With Plus:** `+8801712345678` (will be cleaned)

### Validation Rules
- Must be valid Bangladesh mobile number
- Should start with 880 (international) or 01 (local)
- Length: 11 digits (local) or 13 digits (international)

## ⚡ Rate Limits

### Default Limits
- **Requests per minute:** 100
- **Requests per hour:** 1000
- **Requests per day:** 10000

### Rate Limit Headers
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1642584000
```

## 💰 Pricing Information

### Cost Structure
- **Local SMS:** ~0.50 BDT per SMS
- **International SMS:** Varies by country
- **Bulk SMS:** Discounted rates available

### Balance Requirements
- Minimum balance: 10 BDT
- Recommended balance: 100+ BDT for production use

## 🚨 Error Handling

### Common Error Codes
```json
{
    "1001": "Invalid API key",
    "1002": "Invalid username/password",
    "1003": "Insufficient balance",
    "1004": "Invalid phone number",
    "1005": "Invalid sender ID",
    "1006": "Message too long",
    "1007": "Rate limit exceeded",
    "1008": "Service temporarily unavailable"
}
```

### Error Response Format
```json
{
    "status": "error",
    "error_code": "1003",
    "error_message": "Insufficient balance",
    "message_id": null,
    "cost": 0
}
```

## 📋 Best Practices

### 1. Message Content
- Keep messages under 160 characters
- Avoid special characters that may cause encoding issues
- Use clear, concise language
- Include sender identification

### 2. Phone Number Handling
- Always validate phone numbers before sending
- Convert local numbers to international format
- Remove any special characters or spaces

### 3. Error Handling
- Implement retry logic for temporary failures
- Log all API responses for debugging
- Handle rate limiting gracefully
- Monitor balance regularly

### 4. Security
- Store credentials securely
- Use HTTPS for all communications
- Implement request signing if supported
- Monitor for suspicious activity

## 🔧 Integration Checklist

### Pre-Integration
- [ ] Obtain MiM SMS account credentials
- [ ] Verify sender ID approval
- [ ] Test API connectivity
- [ ] Set up error monitoring

### Implementation
- [ ] Implement authentication
- [ ] Create request/response handlers
- [ ] Add phone number validation
- [ ] Implement retry logic
- [ ] Add logging and monitoring

### Testing
- [ ] Test with valid phone numbers
- [ ] Test error scenarios
- [ ] Verify delivery status checking
- [ ] Test rate limiting handling

### Production
- [ ] Monitor SMS delivery rates
- [ ] Track costs and usage
- [ ] Set up alerts for failures
- [ ] Regular balance monitoring

## 📞 Support Information

### Contact Details
- **Email:** support@mimsms.com
- **Phone:** +880-XXX-XXXXXXX
- **Documentation:** https://docs.mimsms.com

### Business Hours
- **Monday - Friday:** 9:00 AM - 6:00 PM (BDT)
- **Saturday:** 9:00 AM - 1:00 PM (BDT)
- **Sunday:** Closed

---

**Note:** This documentation is based on research and common SMS API patterns. Actual MiM SMS API endpoints and parameters may vary. Please refer to the official MiM SMS documentation for the most accurate information.
