<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Phone Number Service
 * 
 * Handles phone number validation and formatting for Bangladesh numbers
 */
class PhoneNumberService
{
    /**
     * Bangladesh country code
     */
    const COUNTRY_CODE = '880';
    
    /**
     * Bangladesh mobile prefixes
     */
    const MOBILE_PREFIXES = [
        '11', '13', '14', '15', '16', '17', '18', '19', '88'
    ];

    /**
     * Validate and format phone number
     */
    public function validateAndFormat(string $phone): array
    {
        $original = $phone;
        $cleanPhone = $this->cleanPhoneNumber($phone);
        
        Log::info('Phone number validation started', [
            'original' => $original,
            'clean' => $cleanPhone,
            'length' => strlen($cleanPhone),
        ]);
        
        // Validate the phone number
        if (!$this->isValidPhoneNumber($cleanPhone)) {
            Log::warning('Phone number validation failed', [
                'original' => $original,
                'clean' => $cleanPhone,
                'reason' => 'Invalid format or prefix',
            ]);
            
            return [
                'valid' => false,
                'formatted' => null,
                'error' => 'Please enter a valid phone number (10-11 digits). The system will automatically add +880 prefix.',
                'original' => $original
            ];
        }

        // Format the phone number
        $formatted = $this->formatPhoneNumber($cleanPhone);
        
        Log::info('Phone number validation successful', [
            'original' => $original,
            'clean' => $cleanPhone,
            'formatted' => $formatted,
        ]);
        
        return [
            'valid' => true,
            'formatted' => $formatted,
            'original' => $original,
            'clean' => $cleanPhone
        ];
    }

    /**
     * Clean phone number by removing non-digit characters
     */
    public function cleanPhoneNumber(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Check if phone number is valid
     */
    public function isValidPhoneNumber(string $phone): bool
    {
        $cleanPhone = $this->cleanPhoneNumber($phone);
        
        // Check if it's a valid Bangladesh mobile number
        return $this->isValidBangladeshMobile($cleanPhone);
    }

    /**
     * Check if it's a valid Bangladesh mobile number (with +880 prefix)
     */
    public function isValidBangladeshMobile(string $phone): bool
    {
        $cleanPhone = $this->cleanPhoneNumber($phone);
        
        Log::debug('Validating Bangladesh mobile number', [
            'input' => $phone,
            'clean' => $cleanPhone,
            'length' => strlen($cleanPhone),
        ]);
        
        // Check if it has the +880 country code
        if (str_starts_with($cleanPhone, self::COUNTRY_CODE)) {
            $phoneWithoutCountryCode = substr($cleanPhone, 3);
            Log::debug('Phone with country code', ['remaining' => $phoneWithoutCountryCode]);
            
            // Accept 10-11 digits after country code
            if (strlen($phoneWithoutCountryCode) >= 10 && strlen($phoneWithoutCountryCode) <= 11) {
                Log::debug('Phone with +880 prefix validation passed', ['clean' => $phoneWithoutCountryCode]);
                return true;
            }
        }
        
        // Also accept numbers without country code (for backward compatibility)
        // Accept 10-11 digits (with or without leading 0)
        if (strlen($cleanPhone) >= 10 && strlen($cleanPhone) <= 11) {
            Log::debug('Phone without country code validation passed', ['clean' => $cleanPhone]);
            return true;
        }
        
        Log::debug('Phone number validation failed - no matching pattern', [
            'clean' => $cleanPhone,
            'length' => strlen($cleanPhone),
        ]);
        
        return false;
    }

    /**
     * Format phone number for SMS sending (always with +880 prefix)
     */
    public function formatPhoneNumber(string $phone): string
    {
        $cleanPhone = $this->cleanPhoneNumber($phone);
        
        // If it already has country code, return as is
        if (str_starts_with($cleanPhone, self::COUNTRY_CODE)) {
            return $cleanPhone;
        }
        
        // If it's 11 digits with leading 0, remove 0 and add country code
        if (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '0')) {
            return self::COUNTRY_CODE . substr($cleanPhone, 1);
        }
        
        // If it's 10 digits, add country code
        if (strlen($cleanPhone) === 10) {
            return self::COUNTRY_CODE . $cleanPhone;
        }
        
        // For any other valid length, add country code
        if (strlen($cleanPhone) >= 10 && strlen($cleanPhone) <= 11) {
            return self::COUNTRY_CODE . $cleanPhone;
        }
        
        // Return with country code prefix
        return self::COUNTRY_CODE . $cleanPhone;
    }

    /**
     * Format phone number for display
     */
    public function formatForDisplay(string $phone): string
    {
        $cleanPhone = $this->cleanPhoneNumber($phone);
        
        // If it has country code, format as +880 1X XXX XXXX
        if (str_starts_with($cleanPhone, self::COUNTRY_CODE)) {
            $number = substr($cleanPhone, 3);
            return '+880 ' . substr($number, 0, 2) . ' ' . substr($number, 2, 3) . ' ' . substr($number, 5, 4);
        }
        
        // If it's 11 digits with leading 0, format as +880 1X XXX XXXX
        if (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '0')) {
            $number = substr($cleanPhone, 1);
            return '+880 ' . substr($number, 0, 2) . ' ' . substr($number, 2, 3) . ' ' . substr($number, 5, 4);
        }
        
        // If it's 10 digits, format as +880 1X XXX XXXX
        if (strlen($cleanPhone) === 10) {
            return '+880 ' . substr($cleanPhone, 0, 2) . ' ' . substr($cleanPhone, 2, 3) . ' ' . substr($cleanPhone, 5, 4);
        }
        
        // Return original if can't format
        return $phone;
    }

    /**
     * Get phone number without country code prefix for display
     */
    public function formatForDisplayWithoutPrefix(string $phone): string
    {
        $cleanPhone = $this->cleanPhoneNumber($phone);
        
        // If it has country code, return just the number part
        if (str_starts_with($cleanPhone, self::COUNTRY_CODE)) {
            $number = substr($cleanPhone, 3);
            return $number;
        }
        
        // If it's 11 digits with leading 0, remove the 0
        if (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '0')) {
            return substr($cleanPhone, 1);
        }
        
        // Return as is if it's already 10 digits
        if (strlen($cleanPhone) === 10) {
            return $cleanPhone;
        }
        
        // Return original if can't format
        return $phone;
    }

    /**
     * Get phone number validation rules for Laravel validation
     */
    public function getValidationRules(): array
    {
        return [
            'customer_phone' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidPhoneNumber($value)) {
                        $fail('Please enter a valid phone number (10-11 digits). The system will automatically add +880 prefix.');
                    }
                },
            ]
        ];
    }

    /**
     * Log phone number validation for debugging
     */
    public function logValidation(string $phone, array $result): void
    {
        Log::info('Phone number validation', [
            'original' => $phone,
            'valid' => $result['valid'],
            'formatted' => $result['formatted'] ?? null,
            'error' => $result['error'] ?? null,
        ]);
    }
}
