<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * SMS Template Service
 * 
 * Handles SMS template management, variable substitution, and validation.
 */
class SmsTemplateService
{
    /**
     * @var array Default SMS templates
     */
    protected array $defaultTemplates = [
        'booking_confirmation' => 'Your booking #{booking_code} for {package_name} is confirmed for {date} at {time}. Total: {amount} BDT. View receipt: {receipt_link}',
        'checkout_confirmation' => 'Your checkout is confirmed! Booking codes: {booking_codes}. Packages: {package_names} ({package_count} items). Date: {date} at {time}. Total: {amount} BDT. View receipt: {receipt_link}',
        'payment_confirmation' => 'Payment received for booking #{booking_code} ({package_name}). View receipt: {receipt_link}',
        'booking_reminder' => 'Reminder: Your {package_name} adventure is tomorrow at {time}. View details: {receipt_link}',
        'booking_cancelled' => 'Your booking #{booking_code} for {package_name} has been cancelled. Contact us for refund.',
        'booking_completed' => 'Your {package_name} adventure #{booking_code} has been completed. Thank you for choosing us!',
        'booking_reactivated' => 'Your booking #{booking_code} for {package_name} has been reactivated. See you on {date} at {time}! View details: {receipt_link}',
        'booking_status_update' => 'Your booking #{booking_code} for {package_name} status updated to {new_status}. View details: {receipt_link}',
        'payment_failed' => 'Payment failed for booking #{booking_code} ({package_name}). Please contact us to resolve.',
        'payment_refunded' => 'Refund processed for booking #{booking_code} ({package_name}). Amount: {amount} BDT',
        'payment_status_update' => 'Payment status for booking #{booking_code} ({package_name}) updated to {new_status}.',
        'admin_new_booking' => 'New booking #{booking_code} for {package_name} received for {date}. Check admin panel.',
        'welcome_message' => 'Welcome to ATV/UTV Adventures! Your account is ready. Contact us for bookings.',
        'password_reset' => 'Your password reset code is: {reset_code}. Valid for 10 minutes. Do not share this code.',
        'verification_code' => 'Your verification code is: {verification_code}. Enter this code to verify your account.',
        'booking_modified' => 'Your booking #{booking_code} for {package_name} has been modified. New date: {date}, time: {time}. View details: {receipt_link}',
        'special_offer' => 'Special offer! {discount_percent}% off on {package_name}. Valid until {valid_until}. Book now!',
    ];

    /**
     * @var array Available template variables
     */
    protected array $availableVariables = [
        'booking_confirmation' => [
            'booking_code' => 'Booking reference code',
            'date' => 'Booking date',
            'time' => 'Booking time',
            'amount' => 'Total amount',
            'customer_name' => 'Customer name',
            'package_name' => 'Package name',
            'receipt_link' => 'Receipt link',
            'contact_number' => 'Contact phone number',
        ],
        'checkout_confirmation' => [
            'booking_codes' => 'Comma-separated booking codes',
            'package_names' => 'Comma-separated package names',
            'package_count' => 'Number of packages booked',
            'date' => 'Booking date',
            'time' => 'Booking time',
            'total_amount' => 'Total amount for all packages',
            'contact_number' => 'Contact phone number',
            'customer_name' => 'Customer name',
            'receipt_link' => 'Receipt link',
        ],
        'payment_confirmation' => [
            'booking_code' => 'Booking reference code',
            'amount' => 'Payment amount',
            'payment_method' => 'Payment method used',
            'customer_name' => 'Customer name',
            'package_name' => 'Package name',
            'receipt_link' => 'Receipt link',
        ],
        'booking_reminder' => [
            'date' => 'Booking date',
            'time' => 'Booking time',
            'customer_name' => 'Customer name',
            'package_name' => 'Package name',
            'receipt_link' => 'Receipt link',
            'contact_number' => 'Contact phone number',
        ],
        'booking_cancelled' => [
            'booking_code' => 'Booking reference code',
            'customer_name' => 'Customer name',
            'package_name' => 'Package name',
            'cancellation_reason' => 'Reason for cancellation',
        ],
        'booking_completed' => [
            'booking_code' => 'Booking reference code',
            'customer_name' => 'Customer name',
            'package_name' => 'Package name',
        ],
        'booking_reactivated' => [
            'booking_code' => 'Booking reference code',
            'date' => 'Booking date',
            'time' => 'Booking time',
            'customer_name' => 'Customer name',
            'package_name' => 'Package name',
            'receipt_link' => 'Receipt link',
        ],
        'booking_status_update' => [
            'booking_code' => 'Booking reference code',
            'customer_name' => 'Customer name',
            'package_name' => 'Package name',
            'new_status' => 'New booking status',
            'receipt_link' => 'Receipt link',
        ],
        'payment_failed' => [
            'booking_code' => 'Booking reference code',
            'customer_name' => 'Customer name',
            'package_name' => 'Package name',
        ],
        'payment_refunded' => [
            'booking_code' => 'Booking reference code',
            'amount' => 'Refund amount',
            'customer_name' => 'Customer name',
            'package_name' => 'Package name',
        ],
        'payment_status_update' => [
            'booking_code' => 'Booking reference code',
            'customer_name' => 'Customer name',
            'package_name' => 'Package name',
            'new_status' => 'New payment status',
        ],
        'admin_new_booking' => [
            'booking_code' => 'Booking reference code',
            'date' => 'Booking date',
            'time' => 'Booking time',
            'customer_name' => 'Customer name',
            'amount' => 'Booking amount',
            'package_name' => 'Package name',
            'receipt_link' => 'Receipt link',
        ],
        'welcome_message' => [
            'customer_name' => 'Customer name',
        ],
        'password_reset' => [
            'reset_code' => 'Password reset code',
            'customer_name' => 'Customer name',
        ],
        'verification_code' => [
            'verification_code' => 'Verification code',
            'customer_name' => 'Customer name',
        ],
        'booking_modified' => [
            'booking_code' => 'Booking reference code',
            'date' => 'New booking date',
            'time' => 'New booking time',
            'customer_name' => 'Customer name',
            'package_name' => 'Package name',
            'receipt_link' => 'Receipt link',
        ],
        'special_offer' => [
            'discount_percent' => 'Discount percentage',
            'package_name' => 'Package name',
            'valid_until' => 'Offer validity date',
            'customer_name' => 'Customer name',
        ],
    ];

    /**
     * Get template content
     */
    public function getTemplate(string $templateName): string
    {
        // First check environment variables
        $envTemplate = env('SMS_' . strtoupper($templateName) . '_TEMPLATE');
        if ($envTemplate) {
            return $envTemplate;
        }

        // Then check cached templates
        $cachedTemplate = Cache::get("sms_template_{$templateName}");
        if ($cachedTemplate) {
            return $cachedTemplate;
        }

        // Finally, return default template
        return $this->defaultTemplates[$templateName] ?? '';
    }

    /**
     * Set template content
     */
    public function setTemplate(string $templateName, string $content): bool
    {
        try {
            // Validate template
            $this->validateTemplate($templateName, $content);

            // Cache the template
            Cache::put("sms_template_{$templateName}", $content, 3600); // Cache for 1 hour

            Log::info('SMS template updated', [
                'template' => $templateName,
                'content_length' => strlen($content),
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to set SMS template', [
                'template' => $templateName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Render template with variables
     */
    public function renderTemplate(string $templateName, array $variables = []): string
    {
        $template = $this->getTemplate($templateName);
        
        if (empty($template)) {
            throw new Exception("Template '{$templateName}' not found");
        }

        // Replace variables in template
        foreach ($variables as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }

        // Remove any remaining unmatched variables
        $template = preg_replace('/\{[^}]+\}/', '', $template);

        return trim($template);
    }

    /**
     * Validate template content
     */
    public function validateTemplate(string $templateName, string $content): bool
    {
        // Check if template name is valid
        if (!array_key_exists($templateName, $this->defaultTemplates)) {
            throw new Exception("Invalid template name: {$templateName}");
        }

        // Check content length (SMS limit is typically 160 characters)
        if (strlen($content) > 160) {
            throw new Exception("Template content exceeds 160 characters limit");
        }

        // Check for required variables
        $requiredVariables = $this->getRequiredVariables($templateName);
        foreach ($requiredVariables as $variable) {
            if (!str_contains($content, "{{$variable}")) {
                throw new Exception("Template missing required variable: {$variable}");
            }
        }

        // Check for invalid variables
        $availableVariables = $this->getAvailableVariables($templateName);
        preg_match_all('/\{([^}]+)\}/', $content, $matches);
        
        foreach ($matches[1] as $variable) {
            if (!in_array($variable, $availableVariables)) {
                throw new Exception("Invalid variable in template: {$variable}");
            }
        }

        return true;
    }

    /**
     * Get available variables for a template
     */
    public function getAvailableVariables(string $templateName): array
    {
        return array_keys($this->availableVariables[$templateName] ?? []);
    }

    /**
     * Get required variables for a template
     */
    public function getRequiredVariables(string $templateName): array
    {
        $requiredMap = [
            'booking_confirmation' => ['booking_code', 'date', 'time', 'amount', 'package_name', 'receipt_link'],
            'checkout_confirmation' => ['booking_codes', 'package_names', 'package_count', 'date', 'time', 'total_amount', 'receipt_link'],
            'payment_confirmation' => ['booking_code', 'package_name', 'receipt_link'],
            'booking_reminder' => ['time', 'package_name', 'receipt_link'],
            'booking_cancelled' => ['booking_code', 'package_name'],
            'booking_completed' => ['booking_code', 'package_name'],
            'booking_reactivated' => ['booking_code', 'date', 'time', 'package_name', 'receipt_link'],
            'booking_status_update' => ['booking_code', 'package_name', 'new_status', 'receipt_link'],
            'payment_failed' => ['booking_code', 'package_name'],
            'payment_refunded' => ['booking_code', 'amount', 'package_name'],
            'payment_status_update' => ['booking_code', 'package_name', 'new_status'],
            'admin_new_booking' => ['booking_code', 'date', 'package_name'],
            'welcome_message' => [],
            'password_reset' => ['reset_code'],
            'verification_code' => ['verification_code'],
            'booking_modified' => ['booking_code', 'date', 'time', 'package_name', 'receipt_link'],
            'special_offer' => ['discount_percent', 'package_name', 'valid_until'],
        ];

        return $requiredMap[$templateName] ?? [];
    }

    /**
     * Get variable documentation
     */
    public function getVariableDocumentation(string $templateName): array
    {
        return $this->availableVariables[$templateName] ?? [];
    }

    /**
     * Preview template with sample data
     */
    public function previewTemplate(string $templateName, array $sampleData = []): string
    {
        // Generate sample data if not provided
        if (empty($sampleData)) {
            $sampleData = $this->generateSampleData($templateName);
        }

        return $this->renderTemplate($templateName, $sampleData);
    }

    /**
     * Generate sample data for template preview
     */
    public function generateSampleData(string $templateName): array
    {
        $sampleData = [
            'booking_code' => 'BK' . strtoupper(substr(md5(uniqid()), 0, 6)),
            'date' => now()->format('Y-m-d'),
            'time' => '10:00 AM',
            'amount' => '5000',
            'customer_name' => 'John Doe',
            'package_name' => 'ATV Adventure Package',
            'receipt_link' => 'https://example.com/r/123',
            'contact_number' => '+880 1712 345678',
            'payment_method' => 'Credit Card',
            'refund_amount' => '5000',
            'cancellation_reason' => 'Customer request',
            'new_status' => 'confirmed',
            'reset_code' => strtoupper(substr(md5(uniqid()), 0, 6)),
            'verification_code' => strtoupper(substr(md5(uniqid()), 0, 6)),
            'discount_percent' => '20',
            'valid_until' => now()->addDays(7)->format('Y-m-d'),
        ];

        // Return only variables that are available for this template
        $availableVariables = $this->getAvailableVariables($templateName);
        return array_intersect_key($sampleData, array_flip($availableVariables));
    }

    /**
     * Get all template names
     */
    public function getAllTemplateNames(): array
    {
        return array_keys($this->defaultTemplates);
    }

    /**
     * Get template statistics
     */
    public function getTemplateStats(): array
    {
        $stats = [];

        foreach ($this->getAllTemplateNames() as $templateName) {
            $template = $this->getTemplate($templateName);
            $stats[$templateName] = [
                'length' => strlen($template),
                'variables' => count($this->getAvailableVariables($templateName)),
                'required_variables' => count($this->getRequiredVariables($templateName)),
                'is_custom' => !array_key_exists($templateName, $this->defaultTemplates),
            ];
        }

        return $stats;
    }

    /**
     * Reset template to default
     */
    public function resetTemplate(string $templateName): bool
    {
        if (!array_key_exists($templateName, $this->defaultTemplates)) {
            return false;
        }

        Cache::forget("sms_template_{$templateName}");
        return true;
    }

    /**
     * Clear all template cache
     */
    public function clearTemplateCache(): void
    {
        foreach ($this->getAllTemplateNames() as $templateName) {
            Cache::forget("sms_template_{$templateName}");
        }
    }

    /**
     * Export templates
     */
    public function exportTemplates(): array
    {
        $templates = [];

        foreach ($this->getAllTemplateNames() as $templateName) {
            $templates[$templateName] = [
                'content' => $this->getTemplate($templateName),
                'variables' => $this->getVariableDocumentation($templateName),
                'required_variables' => $this->getRequiredVariables($templateName),
            ];
        }

        return $templates;
    }

    /**
     * Import templates
     */
    public function importTemplates(array $templates): bool
    {
        try {
            foreach ($templates as $templateName => $templateData) {
                if (isset($templateData['content'])) {
                    $this->setTemplate($templateName, $templateData['content']);
                }
            }

            return true;

        } catch (Exception $e) {
            Log::error('Failed to import SMS templates', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

