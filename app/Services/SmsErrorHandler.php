<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * SMS Error Handler Service
 * 
 * Handles error categorization, retry logic, and error reporting for SMS services.
 */
class SmsErrorHandler
{
    /**
     * Error categories
     */
    const ERROR_CATEGORIES = [
        'authentication' => [
            'invalid_api_key',
            'invalid_username',
            'invalid_password',
            'authentication_failed',
            'unauthorized',
        ],
        'configuration' => [
            'invalid_sender_id',
            'sender_id_not_approved',
            'invalid_endpoint',
            'service_not_configured',
        ],
        'phone_number' => [
            'invalid_phone_number',
            'phone_number_not_supported',
            'phone_number_blocked',
            'invalid_country_code',
        ],
        'message' => [
            'message_too_long',
            'invalid_message_content',
            'message_contains_spam',
            'message_encoding_error',
        ],
        'balance' => [
            'insufficient_balance',
            'account_suspended',
            'payment_required',
            'credit_limit_exceeded',
        ],
        'rate_limit' => [
            'rate_limit_exceeded',
            'too_many_requests',
            'quota_exceeded',
            'throttled',
        ],
        'network' => [
            'connection_timeout',
            'network_error',
            'dns_error',
            'ssl_error',
        ],
        'server' => [
            'internal_server_error',
            'service_unavailable',
            'bad_gateway',
            'server_error',
        ],
        'temporary' => [
            'temporary_failure',
            'retry_later',
            'service_busy',
            'maintenance',
        ],
    ];

    /**
     * Permanent errors that should not be retried
     */
    const PERMANENT_ERRORS = [
        'authentication',
        'configuration',
        'phone_number',
        'message',
        'balance',
    ];

    /**
     * Categorize error based on error message
     */
    public function categorizeError(string $errorMessage): string
    {
        $errorMessage = strtolower($errorMessage);

        foreach (self::ERROR_CATEGORIES as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($errorMessage, strtolower($pattern))) {
                    return $category;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Check if error is permanent (should not retry)
     */
    public function isPermanentError(string $errorMessage): bool
    {
        $category = $this->categorizeError($errorMessage);
        return in_array($category, self::PERMANENT_ERRORS);
    }

    /**
     * Check if error is temporary (should retry)
     */
    public function isTemporaryError(string $errorMessage): bool
    {
        $category = $this->categorizeError($errorMessage);
        return in_array($category, ['temporary', 'network', 'server', 'rate_limit']);
    }

    /**
     * Get retry delay for error category
     */
    public function getRetryDelay(string $errorMessage, int $attempt): int
    {
        $category = $this->categorizeError($errorMessage);

        $baseDelays = [
            'temporary' => 5,
            'network' => 10,
            'server' => 15,
            'rate_limit' => 60,
            'unknown' => 30,
        ];

        $baseDelay = $baseDelays[$category] ?? 30;
        
        // Exponential backoff with jitter
        $delay = $baseDelay * pow(2, $attempt - 1);
        $jitter = rand(0, 1000) / 1000; // Random jitter between 0-1 seconds
        
        return (int) ($delay + $jitter);
    }

    /**
     * Get maximum retry attempts for error category
     */
    public function getMaxRetries(string $errorMessage): int
    {
        $category = $this->categorizeError($errorMessage);

        $maxRetries = [
            'temporary' => 3,
            'network' => 5,
            'server' => 3,
            'rate_limit' => 2,
            'unknown' => 2,
        ];

        return $maxRetries[$category] ?? 2;
    }

    /**
     * Log error with categorization
     */
    public function logError(string $errorMessage, array $context = []): void
    {
        $category = $this->categorizeError($errorMessage);
        $isPermanent = $this->isPermanentError($errorMessage);

        $logContext = array_merge($context, [
            'error_category' => $category,
            'is_permanent' => $isPermanent,
            'error_message' => $errorMessage,
        ]);

        if ($isPermanent) {
            Log::error('SMS permanent error', $logContext);
        } else {
            Log::warning('SMS temporary error', $logContext);
        }
    }

    /**
     * Create detailed error report
     */
    public function createErrorReport(string $errorMessage, array $context = []): array
    {
        $category = $this->categorizeError($errorMessage);
        $isPermanent = $this->isPermanentError($errorMessage);
        $maxRetries = $this->getMaxRetries($errorMessage);

        return [
            'error_message' => $errorMessage,
            'error_category' => $category,
            'is_permanent' => $isPermanent,
            'max_retries' => $maxRetries,
            'should_retry' => !$isPermanent,
            'context' => $context,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Handle HTTP status code errors
     */
    public function handleHttpError(int $statusCode, string $body = ''): array
    {
        $errorMessages = [
            400 => 'Bad Request - Invalid parameters provided',
            401 => 'Unauthorized - Invalid credentials',
            403 => 'Forbidden - Access denied or insufficient permissions',
            404 => 'Not Found - API endpoint not found',
            429 => 'Too Many Requests - Rate limit exceeded',
            500 => 'Internal Server Error - Server error occurred',
            502 => 'Bad Gateway - Service temporarily unavailable',
            503 => 'Service Unavailable - Service is down for maintenance',
            504 => 'Gateway Timeout - Request timeout',
        ];

        $errorMessage = $errorMessages[$statusCode] ?? "HTTP Error {$statusCode}";
        
        if (!empty($body)) {
            try {
                $errorData = json_decode($body, true);
                if (isset($errorData['error'])) {
                    $errorMessage .= " - " . $errorData['error'];
                } elseif (isset($errorData['message'])) {
                    $errorMessage .= " - " . $errorData['message'];
                }
            } catch (Exception $e) {
                $errorMessage .= " - " . substr($body, 0, 100);
            }
        }

        return $this->createErrorReport($errorMessage, [
            'http_status_code' => $statusCode,
            'response_body' => $body,
        ]);
    }

    /**
     * Get error category description
     */
    public function getCategoryDescription(string $category): string
    {
        $descriptions = [
            'authentication' => 'Authentication or authorization errors',
            'configuration' => 'Configuration or setup errors',
            'phone_number' => 'Phone number validation errors',
            'message' => 'Message content or format errors',
            'balance' => 'Account balance or payment errors',
            'rate_limit' => 'Rate limiting or quota errors',
            'network' => 'Network connectivity errors',
            'server' => 'Server or service errors',
            'temporary' => 'Temporary service errors',
            'unknown' => 'Unknown or uncategorized errors',
        ];

        return $descriptions[$category] ?? 'Unknown error category';
    }

    /**
     * Get recommended action for error category
     */
    public function getRecommendedAction(string $category): string
    {
        $actions = [
            'authentication' => 'Check API credentials and authentication settings',
            'configuration' => 'Verify SMS service configuration and sender ID',
            'phone_number' => 'Validate phone number format and country code',
            'message' => 'Check message content and length limits',
            'balance' => 'Recharge account balance or check payment status',
            'rate_limit' => 'Wait before sending more messages or upgrade plan',
            'network' => 'Check network connectivity and try again later',
            'server' => 'Contact SMS provider support or try again later',
            'temporary' => 'Retry sending the message after a short delay',
            'unknown' => 'Contact support for assistance',
        ];

        return $actions[$category] ?? 'Contact support for assistance';
    }
}

