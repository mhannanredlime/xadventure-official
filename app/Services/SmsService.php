<?php

namespace App\Services;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;
use App\Services\SmsErrorHandler;

/**
 * Abstract SMS Service Class
 * 
 * Provides common functionality and utilities for SMS services.
 * All SMS provider implementations should extend this class.
 */
abstract class SmsService implements SmsProviderInterface
{
    /**
     * @var array Configuration for the SMS service
     */
    protected array $config;

    /**
     * @var int Maximum retry attempts for failed SMS
     */
    protected int $maxRetries = 3;

    /**
     * @var int Delay between retries in seconds
     */
    protected int $retryDelay = 5;

    /**
     * @var SmsErrorHandler Error handler instance
     */
    protected SmsErrorHandler $errorHandler;

    /**
     * @var int Rate limit per minute
     */
    protected int $rateLimitPerMinute;

    /**
     * @var int Rate limit per hour
     */
    protected int $rateLimitPerHour;

    /**
     * @var int Rate limit per day
     */
    protected int $rateLimitPerDay;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->errorHandler = new SmsErrorHandler();
        $this->config = $this->loadConfiguration();
        
        // Initialize rate limiting from config
        $this->rateLimitPerMinute = config('services.sms.rate_limit', 100);
        $this->rateLimitPerHour = config('services.sms.rate_limit_hour', 1000);
        $this->rateLimitPerDay = config('services.sms.rate_limit_day', 10000);
    }

    /**
     * Load configuration for the SMS service
     */
    abstract protected function loadConfiguration(): array;

    /**
     * Send SMS with logging and error handling
     */
    public function sendWithLogging(string $to, string $message, array $options = []): SmsResponse
    {
        // Create SMS log entry
        $smsLog = SmsLog::create([
            'phone_number' => $to,
            'message' => $message,
            'template_name' => $options['template_name'] ?? null,
            'provider' => $this->getProviderName(),
            'status' => SmsLog::STATUS_PENDING,
            'metadata' => $options,
        ]);

        try {
            // Validate phone number
            if (!$this->validatePhoneNumber($to)) {
                throw new Exception("Invalid phone number: {$to}");
            }

            // Check if SMS is enabled
            if (!$this->isSmsEnabled()) {
                throw new Exception('SMS service is disabled');
            }

            // Check rate limiting
            if (!$this->checkRateLimit()) {
                throw new Exception('SMS rate limit exceeded. Please try again later.');
            }

            // Send SMS with retry logic
            $response = $this->sendWithRetry($to, $message, $options);

            // Increment rate limit counters on successful send
            if ($response->isSuccess()) {
                $this->incrementRateLimit();
            }

            // Update SMS log based on response
            if ($response->isSuccess()) {
                $smsLog->markAsSent($response->messageId);
                $smsLog->setMetadata('cost', $response->cost);
                $smsLog->setMetadata('provider_response', $response->toArray());
                
                Log::info('SMS sent successfully', [
                    'provider' => $this->getProviderName(),
                    'phone' => $to,
                    'message_id' => $response->messageId,
                    'cost' => $response->cost,
                ]);
            } else {
                $smsLog->markAsFailed($response->errorMessage);
                $smsLog->setMetadata('provider_response', $response->toArray());
                
                Log::error('SMS sending failed', [
                    'provider' => $this->getProviderName(),
                    'phone' => $to,
                    'error' => $response->errorMessage,
                ]);
            }

            return $response;

        } catch (Exception $e) {
            // Log the error and mark SMS as failed
            $smsLog->markAsFailed($e->getMessage());
            
            Log::error('SMS sending exception', [
                'provider' => $this->getProviderName(),
                'phone' => $to,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return SmsResponse::failure($e->getMessage());
        }
    }

    /**
     * Send SMS with retry logic
     */
    protected function sendWithRetry(string $to, string $message, array $options = []): SmsResponse
    {
        $attempts = 0;
        $lastException = null;
        $lastErrorResponse = null;

        while ($attempts < $this->maxRetries) {
            try {
                $attempts++;
                
                Log::info('Sending SMS attempt', [
                    'provider' => $this->getProviderName(),
                    'phone' => $to,
                    'attempt' => $attempts,
                ]);

                $response = $this->send($to, $message, $options);

                if ($response->isSuccess()) {
                    return $response;
                }

                $lastErrorResponse = $response;

                // Check if it's a permanent failure
                if ($this->isPermanentFailure($response)) {
                    Log::info('SMS permanent failure, stopping retries', [
                        'provider' => $this->getProviderName(),
                        'phone' => $to,
                        'attempt' => $attempts,
                        'error' => $response->errorMessage,
                    ]);
                    return $response;
                }

                $lastException = new Exception($response->errorMessage);

            } catch (Exception $e) {
                $lastException = $e;
                
                // Check if it's a permanent exception
                if ($this->isPermanentException($e)) {
                    Log::info('SMS permanent exception, stopping retries', [
                        'provider' => $this->getProviderName(),
                        'phone' => $to,
                        'attempt' => $attempts,
                        'error' => $e->getMessage(),
                    ]);
                    break;
                }

                Log::warning('SMS sending attempt failed', [
                    'provider' => $this->getProviderName(),
                    'phone' => $to,
                    'attempt' => $attempts,
                    'error' => $e->getMessage(),
                ]);
            }

            // Wait before retrying (except on last attempt)
            if ($attempts < $this->maxRetries) {
                $delay = $this->calculateRetryDelay($lastException, $attempts);
                Log::info('Waiting before retry', [
                    'provider' => $this->getProviderName(),
                    'phone' => $to,
                    'attempt' => $attempts,
                    'delay_seconds' => $delay,
                ]);
                sleep($delay);
            }
        }

        $errorMessage = $lastException ? $lastException->getMessage() : 
                       ($lastErrorResponse ? $lastErrorResponse->errorMessage : 'Max retry attempts exceeded');

        return SmsResponse::failure($errorMessage);
    }

    /**
     * Calculate retry delay based on error type
     */
    protected function calculateRetryDelay(?Exception $exception, int $attempt): int
    {
        if ($exception) {
            $errorMessage = $exception->getMessage();
            return $this->errorHandler->getRetryDelay($errorMessage, $attempt);
        }

        // Default exponential backoff
        return $this->retryDelay * pow(2, $attempt - 1);
    }

    /**
     * Check if SMS service is enabled
     */
    protected function isSmsEnabled(): bool
    {
        return config('services.sms.enabled', true);
    }

    /**
     * Check if the failure is permanent (should not retry)
     */
    protected function isPermanentFailure(SmsResponse $response): bool
    {
        if (!$response->errorMessage) {
            return false;
        }

        return $this->errorHandler->isPermanentError($response->errorMessage);
    }

    /**
     * Check if the exception is permanent (should not retry)
     */
    protected function isPermanentException(Exception $e): bool
    {
        $permanentExceptions = [
            'InvalidArgumentException',
            'AuthenticationException',
        ];

        foreach ($permanentExceptions as $exceptionClass) {
            if ($e instanceof $exceptionClass) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get cached balance
     */
    public function getCachedBalance(): float
    {
        $cacheKey = "sms_balance_{$this->getProviderName()}";
        
        return Cache::remember($cacheKey, 300, function () { // Cache for 5 minutes
            return $this->getBalance();
        });
    }

    /**
     * Clear balance cache
     */
    public function clearBalanceCache(): void
    {
        $cacheKey = "sms_balance_{$this->getProviderName()}";
        Cache::forget($cacheKey);
    }

    /**
     * Validate phone number format (basic validation)
     */
    public function validatePhoneNumber(string $phone): bool
    {
        // Remove all non-digit characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid length (7-15 digits)
        if (strlen($cleanPhone) < 7 || strlen($cleanPhone) > 15) {
            return false;
        }

        // Check if it starts with a valid digit
        if (!preg_match('/^[1-9]/', $cleanPhone)) {
            return false;
        }

        return true;
    }

    /**
     * Format phone number for sending
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-digit characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if not present (assuming Bangladesh +880)
        if (!str_starts_with($cleanPhone, '880') && strlen($cleanPhone) === 11) {
            $cleanPhone = '880' . substr($cleanPhone, 1);
        }
        
        return $cleanPhone;
    }

    /**
     * Get configuration value
     */
    protected function getConfig(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Check if configuration is valid
     */
    protected function validateConfiguration(): bool
    {
        $requiredKeys = $this->getRequiredConfigKeys();
        
        foreach ($requiredKeys as $key) {
            if (empty($this->getConfig($key))) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get required configuration keys
     */
    abstract protected function getRequiredConfigKeys(): array;

    /**
     * Check rate limiting before sending SMS
     */
    protected function checkRateLimit(): bool
    {
        $provider = $this->getProviderName();
        $now = now();
        
        // Check per-minute rate limit
        $minuteKey = "sms_rate_limit_minute_{$provider}_{$now->format('Y-m-d-H-i')}";
        $minuteCount = Cache::get($minuteKey, 0);
        
        if ($minuteCount >= $this->rateLimitPerMinute) {
            Log::warning('SMS rate limit exceeded (per minute)', [
                'provider' => $provider,
                'limit' => $this->rateLimitPerMinute,
                'current' => $minuteCount,
            ]);
            return false;
        }
        
        // Check per-hour rate limit
        $hourKey = "sms_rate_limit_hour_{$provider}_{$now->format('Y-m-d-H')}";
        $hourCount = Cache::get($hourKey, 0);
        
        if ($hourCount >= $this->rateLimitPerHour) {
            Log::warning('SMS rate limit exceeded (per hour)', [
                'provider' => $provider,
                'limit' => $this->rateLimitPerHour,
                'current' => $hourCount,
            ]);
            return false;
        }
        
        // Check per-day rate limit
        $dayKey = "sms_rate_limit_day_{$provider}_{$now->format('Y-m-d')}";
        $dayCount = Cache::get($dayKey, 0);
        
        if ($dayCount >= $this->rateLimitPerDay) {
            Log::warning('SMS rate limit exceeded (per day)', [
                'provider' => $provider,
                'limit' => $this->rateLimitPerDay,
                'current' => $dayCount,
            ]);
            return false;
        }
        
        return true;
    }

    /**
     * Increment rate limit counters
     */
    protected function incrementRateLimit(): void
    {
        $provider = $this->getProviderName();
        $now = now();
        
        // Increment per-minute counter
        $minuteKey = "sms_rate_limit_minute_{$provider}_{$now->format('Y-m-d-H-i')}";
        Cache::increment($minuteKey);
        Cache::put($minuteKey, Cache::get($minuteKey), 120); // Expire in 2 minutes
        
        // Increment per-hour counter
        $hourKey = "sms_rate_limit_hour_{$provider}_{$now->format('Y-m-d-H')}";
        Cache::increment($hourKey);
        Cache::put($hourKey, Cache::get($hourKey), 7200); // Expire in 2 hours
        
        // Increment per-day counter
        $dayKey = "sms_rate_limit_day_{$provider}_{$now->format('Y-m-d')}";
        Cache::increment($dayKey);
        Cache::put($dayKey, Cache::get($dayKey), 86400); // Expire in 24 hours
    }

    /**
     * Get current rate limit status
     */
    public function getRateLimitStatus(): array
    {
        $provider = $this->getProviderName();
        $now = now();
        
        $minuteKey = "sms_rate_limit_minute_{$provider}_{$now->format('Y-m-d-H-i')}";
        $hourKey = "sms_rate_limit_hour_{$provider}_{$now->format('Y-m-d-H')}";
        $dayKey = "sms_rate_limit_day_{$provider}_{$now->format('Y-m-d')}";
        
        return [
            'per_minute' => [
                'current' => Cache::get($minuteKey, 0),
                'limit' => $this->rateLimitPerMinute,
                'remaining' => max(0, $this->rateLimitPerMinute - Cache::get($minuteKey, 0)),
            ],
            'per_hour' => [
                'current' => Cache::get($hourKey, 0),
                'limit' => $this->rateLimitPerHour,
                'remaining' => max(0, $this->rateLimitPerHour - Cache::get($hourKey, 0)),
            ],
            'per_day' => [
                'current' => Cache::get($dayKey, 0),
                'limit' => $this->rateLimitPerDay,
                'remaining' => max(0, $this->rateLimitPerDay - Cache::get($dayKey, 0)),
            ],
        ];
    }
}

