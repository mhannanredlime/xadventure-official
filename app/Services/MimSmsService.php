<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\SmsErrorHandler;

/**
 * MiM SMS Service Implementation
 * 
 * Handles SMS sending through the MiM SMS API.
 */
class MimSmsService extends SmsService
{
    /**
     * @var string Provider name
     */
    protected string $providerName = 'mim';

    /**
     * @var SmsErrorHandler Error handler instance
     */
    protected SmsErrorHandler $errorHandler;

    /**
     * Load configuration for MiM SMS
     */
    protected function loadConfiguration(): array
    {
        $this->errorHandler = new SmsErrorHandler();
        return config('services.sms.mim', []);
    }

    /**
     * Get required configuration keys
     */
    protected function getRequiredConfigKeys(): array
    {
        return [
            'api_key',
            'sender_id',
            'base_url',
            'username',
        ];
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }

    /**
     * Check if provider is configured and ready
     */
    public function isConfigured(): bool
    {
        return $this->validateConfiguration();
    }

    /**
     * Test provider connectivity
     */
    public function testConnection(): bool
    {
        try {
            // Try to get balance as a connectivity test
            $this->getBalance();
            return true;
        } catch (Exception $e) {
            Log::error('MiM SMS connection test failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send SMS through MiM API
     */
    public function send(string $to, string $message, array $options = []): SmsResponse
    {
        try {
            // Validate configuration
            if (!$this->isConfigured()) {
                throw new Exception('MiM SMS is not properly configured');
            }

            // Format phone number
            $formattedPhone = $this->formatPhoneNumber($to);

            // Prepare request payload
            $payload = $this->prepareSendSmsPayload($formattedPhone, $message, $options);

            // Make API request
            $response = $this->makeApiRequest('/api/SmsSending/SMS', $payload);

            // Handle response
            return $this->handleSendSmsResponse($response, $to);

        } catch (Exception $e) {
            Log::error('MiM SMS sending failed', [
                'phone' => $to,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return SmsResponse::failure($e->getMessage());
        }
    }

    /**
     * Prepare payload for send SMS request
     */
    protected function prepareSendSmsPayload(string $phone, string $message, array $options = []): array
    {
        $payload = [
            'UserName' => $this->getConfig('username'),
            'Apikey' => $this->getConfig('api_key'),
            'MobileNumber' => $phone,
            'CampaignId' => 'null',
            'SenderName' => $options['sender_id'] ?? $this->getConfig('sender_id'),
            'TransactionType' => $options['transaction_type'] ?? 'T', // T = Transactional, P = Promotional, D = Dynamic
            'Message' => $message,
        ];

        // Add optional parameters
        if (isset($options['campaign_id']) && $options['campaign_id'] !== 'null') {
            $payload['CampaignId'] = $options['campaign_id'];
        }

        return $payload;
    }

    /**
     * Make API request with proper error handling
     */
    protected function makeApiRequest(string $endpoint, array $payload): \Illuminate\Http\Client\Response
    {
        $url = $this->getConfig('base_url') . $endpoint;
        $timeout = $this->getConfig('timeout', 30);

        // Log request (hide sensitive data)
        $logPayload = array_merge($payload, ['Apikey' => '***']);
        Log::info('MiM SMS API request', [
            'url' => $url,
            'endpoint' => $endpoint,
            'payload' => $logPayload,
        ]);

        $response = Http::timeout($timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post($url, $payload);

        // Log response
        Log::info('MiM SMS API response', [
            'url' => $url,
            'status_code' => $response->status(),
            'response_body' => $response->body(),
        ]);

        return $response;
    }

    /**
     * Handle send SMS response
     */
    protected function handleSendSmsResponse(\Illuminate\Http\Client\Response $response, string $phone): SmsResponse
    {
        if ($response->successful()) {
            $responseData = $response->json();
            
            if ($this->isSuccessfulResponse($responseData)) {
                // Enhanced metadata with balance and error code handling
                $metadata = $responseData;
                
                // Add balance field if present
                if (isset($responseData['balance'])) {
                    $metadata['balance'] = $responseData['balance'];
                }
                
                // Add error code if present (for successful responses that might include warnings)
                if (isset($responseData['error_code'])) {
                    $metadata['error_code'] = $responseData['error_code'];
                }
                
                return SmsResponse::success(
                    $responseData['trxnId'] ?? $this->generateMessageId(),
                    null, // Cost not provided in response
                    $metadata
                );
            } else {
                $errorMessage = $this->extractErrorMessage($responseData);
                return SmsResponse::failure($errorMessage, $responseData);
            }
        } else {
            $errorMessage = $this->handleHttpError($response);
            return SmsResponse::failure($errorMessage);
        }
    }

    /**
     * Extract error message from response
     */
    protected function extractErrorMessage(array $responseData): string
    {
        $errorMessage = 'Unknown error from MiM SMS API';
        $errorCode = $responseData['statusCode'] ?? null;

        // Extract error message with priority order for MIM SMS API
        if (isset($responseData['responseResult'])) {
            $errorMessage = $responseData['responseResult'];
        } elseif (isset($responseData['error_message'])) {
            $errorMessage = $responseData['error_message'];
        } elseif (isset($responseData['error'])) {
            $errorMessage = $responseData['error'];
        } elseif (isset($responseData['message'])) {
            $errorMessage = $responseData['message'];
        }

        // Enhance error message with error code if available
        if ($errorCode) {
            $errorMessage = "[Error Code: {$errorCode}] {$errorMessage}";
        }

        // Log error with categorization and error code
        $this->errorHandler->logError($errorMessage, [
            'provider' => $this->getProviderName(),
            'response_data' => $responseData,
            'error_code' => $errorCode,
        ]);

        return $errorMessage;
    }

    /**
     * Handle HTTP error responses
     */
    protected function handleHttpError(\Illuminate\Http\Client\Response $response): string
    {
        $statusCode = $response->status();
        $body = $response->body();

        $errorReport = $this->errorHandler->handleHttpError($statusCode, $body);
        
        // Log the error with categorization
        $this->errorHandler->logError($errorReport['error_message'], [
            'provider' => $this->getProviderName(),
            'http_status_code' => $statusCode,
        ]);

        return $errorReport['error_message'];
    }

    /**
     * Get account balance from MiM API
     */
    public function getBalance(): float
    {
        try {
            $payload = $this->prepareBalancePayload();
            $response = $this->makeApiRequest('/api/SmsSending/balanceCheck', $payload);
            
            return $this->handleBalanceResponse($response);

        } catch (Exception $e) {
            Log::error('MiM SMS balance check failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get delivery status from MiM API
     */
    public function getDeliveryStatus(string $messageId): string
    {
        try {
            $payload = $this->prepareStatusPayload($messageId);
            $response = $this->makeApiRequest('/status', $payload);
            
            return $this->handleStatusResponse($response, $messageId);

        } catch (Exception $e) {
            Log::error('MiM SMS status check failed', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
            return 'unknown';
        }
    }

    /**
     * Prepare payload for balance request
     */
    protected function prepareBalancePayload(): array
    {
        return [
            'UserName' => $this->getConfig('username'),
            'Apikey' => $this->getConfig('api_key'),
        ];
    }

    /**
     * Prepare payload for status request
     */
    protected function prepareStatusPayload(string $messageId): array
    {
        return [
            'api_key' => $this->getConfig('api_key'),
            'message_id' => $messageId,
            'username' => $this->getConfig('username'),
            'password' => $this->getConfig('password'),
        ];
    }

        /**
     * Handle balance response
     */
    protected function handleBalanceResponse(\Illuminate\Http\Client\Response $response): float
    {
        if ($response->successful()) {
            $responseData = $response->json();

            if ($this->isSuccessfulResponse($responseData)) {
                // Extract balance from response for MIM SMS API
                $balance = $responseData['responseResult'] ?? 0;
                
                // Convert to float if it's a string
                if (is_string($balance)) {
                    $balance = (float) $balance;
                }
                
                // Log balance check with additional info
                Log::info('MiM SMS balance check successful', [
                    'balance' => $balance,
                    'currency' => 'BDT',
                    'response_data' => $responseData,
                ]);

                // Check balance thresholds and log warnings
                if ($balance < 10) {
                    Log::critical('MiM SMS balance critically low', [
                        'balance' => $balance,
                        'minimum_recommended' => 10,
                        'recommended' => 100,
                    ]);
                } elseif ($balance < 100) {
                    Log::warning('MiM SMS balance below recommended level', [
                        'balance' => $balance,
                        'recommended' => 100,
                    ]);
                }
                
                return $balance;
            } else {
                $errorMessage = $this->extractErrorMessage($responseData);
                throw new Exception("Failed to get balance: {$errorMessage}");
            }
        }

        throw new Exception('Failed to get balance from MiM SMS API');
    }

    /**
     * Handle status response
     */
    protected function handleStatusResponse(\Illuminate\Http\Client\Response $response, string $messageId): string
    {
        if ($response->successful()) {
            $responseData = $response->json();
            
            if (isset($responseData['status'])) {
                return $this->mapDeliveryStatus($responseData['status']);
            }

            if (isset($responseData['error'])) {
                Log::warning('MiM SMS status check error', [
                    'message_id' => $messageId,
                    'error' => $responseData['error'],
                ]);
            }
        }

        return 'unknown';
    }

    /**
     * Check if API response indicates success
     */
    protected function isSuccessfulResponse(array $responseData): bool
    {
        // Check for success indicators in response for MIM SMS API
        $status = $responseData['status'] ?? '';
        $statusCode = $responseData['statusCode'] ?? '';
        
        // Success conditions for MIM SMS API
        $isSuccess = ($status === 'Success' || $status === 'success' || $status === 'SUCCESS');
        $isStatusCodeSuccess = ($statusCode === '200' || $statusCode === 200);
        
        return $isSuccess || $isStatusCodeSuccess;
    }

    /**
     * Map MiM API status to our standard status
     */
    protected function mapDeliveryStatus(string $mimStatus): string
    {
        $status = strtolower($mimStatus);
        
        $statusMap = [
            'delivered' => 'delivered',
            'sent' => 'sent',
            'pending' => 'pending',
            'failed' => 'failed',
            'rejected' => 'failed',
            'error' => 'failed',
        ];

        return $statusMap[$status] ?? 'pending';
    }

    /**
     * Generate a unique message ID
     */
    protected function generateMessageId(): string
    {
        return 'mim_' . time() . '_' . uniqid();
    }

    /**
     * Validate phone number for MiM SMS
     */
    public function validatePhoneNumber(string $phone): bool
    {
        // First, use parent validation
        if (!parent::validatePhoneNumber($phone)) {
            return false;
        }

        // Additional MiM-specific validation
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // MiM typically expects Bangladesh numbers
        if (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '01')) {
            return true;
        }
        
        // International format
        if (strlen($cleanPhone) === 13 && str_starts_with($cleanPhone, '880')) {
            return true;
        }

        return false;
    }

    /**
     * Format phone number for MiM SMS
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Use the PhoneNumberService for consistent formatting
        $phoneService = new \App\Services\PhoneNumberService();
        return $phoneService->formatPhoneNumber($phone);
    }
}

