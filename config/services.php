<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'amarpay' => [
        'store_id' => env('AMARPAY_STORE_ID', 'aamarpaytest'),
        'signature_key' => env('AMARPAY_SIGNATURE_KEY', 'dbb74894e82415a2f7ff0ec3a97e4183'),
        'sandbox' => env('AMARPAY_SANDBOX', true),
        'success_url' => env('AMARPAY_SUCCESS_URL', '/payment/amarpay/success'),
        'fail_url' => env('AMARPAY_FAIL_URL', '/payment/amarpay/fail'),
        'cancel_url' => env('AMARPAY_CANCEL_URL', '/payment/amarpay/cancel'),
        'ipn_url' => env('AMARPAY_IPN_URL', '/payment/amarpay/ipn'),
        'api_url' => env('AMARPAY_SANDBOX', true) 
            ? 'https://sandbox.aamarpay.com/jsonpost.php' 
            : 'https://secure.aamarpay.com/jsonpost.php',
    ],

    'sms' => [
        'provider' => env('SMS_PROVIDER', 'mim'),
        'enabled' => env('SMS_ENABLED', true),
        'queue_enabled' => env('SMS_QUEUE_ENABLED', true),
        'max_retries' => env('SMS_MAX_RETRIES', 3),
        'retry_delay' => env('SMS_RETRY_DELAY', 5),
        'rate_limit' => env('SMS_RATE_LIMIT', 100), // SMS per minute
        'rate_limit_hour' => env('SMS_RATE_LIMIT_HOUR', 1000), // SMS per hour
        'rate_limit_day' => env('SMS_RATE_LIMIT_DAY', 10000), // SMS per day
        'admin_phone_numbers' => env('SMS_ADMIN_PHONE_NUMBERS', ''), // Comma-separated
        
        'mim' => [
            'api_key' => env('MIM_SMS_API_KEY'),
            'sender_id' => env('MIM_SMS_SENDER_ID'),
            'base_url' => env('MIM_SMS_BASE_URL', 'https://api.mimsms.com'),
            'username' => env('MIM_SMS_USERNAME'),
            'timeout' => env('MIM_SMS_TIMEOUT', 30),
        ],
        
        'templates' => [
            'booking_confirmation' => env('SMS_BOOKING_CONFIRMATION_TEMPLATE', 'Your booking #{booking_code} for {package_name} is confirmed for {date} at {time}. Total: {amount} BDT. View receipt: {receipt_link}'),
            'payment_confirmation' => env('SMS_PAYMENT_CONFIRMATION_TEMPLATE', 'Payment received for booking #{booking_code} ({package_name}). View receipt: {receipt_link}'),
            'booking_reminder' => env('SMS_BOOKING_REMINDER_TEMPLATE', 'Reminder: Your {package_name} adventure is tomorrow at {time}. View details: {receipt_link}'),
            'booking_cancelled' => env('SMS_BOOKING_CANCELLED_TEMPLATE', 'Your booking #{booking_code} for {package_name} has been cancelled. Contact us for refund.'),
            'booking_completed' => env('SMS_BOOKING_COMPLETED_TEMPLATE', 'Your {package_name} adventure #{booking_code} has been completed. Thank you for choosing us!'),
            'booking_reactivated' => env('SMS_BOOKING_REACTIVATED_TEMPLATE', 'Your booking #{booking_code} for {package_name} has been reactivated. See you on {date} at {time}! View details: {receipt_link}'),
            'booking_status_update' => env('SMS_BOOKING_STATUS_UPDATE_TEMPLATE', 'Your booking #{booking_code} for {package_name} status updated to {new_status}. View details: {receipt_link}'),
            'payment_failed' => env('SMS_PAYMENT_FAILED_TEMPLATE', 'Payment failed for booking #{booking_code} ({package_name}). Please contact us to resolve.'),
            'payment_refunded' => env('SMS_PAYMENT_REFUNDED_TEMPLATE', 'Refund processed for booking #{booking_code} ({package_name}). Amount: {amount} BDT'),
            'payment_status_update' => env('SMS_PAYMENT_STATUS_UPDATE_TEMPLATE', 'Payment status for booking #{booking_code} ({package_name}) updated to {new_status}.'),
            'admin_new_booking' => env('SMS_ADMIN_NEW_BOOKING_TEMPLATE', 'New booking #{booking_code} for {package_name} received for {date}. Check admin panel.'),
        ],
    ],

];
