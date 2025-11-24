<?php

namespace App\Services;

/**
 * SMS Response Data Class
 * 
 * Represents the response from an SMS provider after sending a message.
 */
class SmsResponse
{
    /**
     * @param bool $success Whether the SMS was sent successfully
     * @param string|null $messageId Provider's message ID for tracking
     * @param string|null $errorMessage Error message if sending failed
     * @param array $metadata Additional response metadata
     * @param float|null $cost Cost of sending the SMS
     * @param string $status Delivery status (pending, sent, delivered, failed)
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?string $messageId = null,
        public readonly ?string $errorMessage = null,
        public readonly array $metadata = [],
        public readonly ?float $cost = null,
        public readonly string $status = 'pending'
    ) {}

    /**
     * Create a successful response
     */
    public static function success(string $messageId, ?float $cost = null, array $metadata = []): self
    {
        return new self(
            success: true,
            messageId: $messageId,
            cost: $cost,
            metadata: $metadata,
            status: 'sent'
        );
    }

    /**
     * Create a failed response
     */
    public static function failure(string $errorMessage, array $metadata = []): self
    {
        return new self(
            success: false,
            errorMessage: $errorMessage,
            metadata: $metadata,
            status: 'failed'
        );
    }

    /**
     * Check if the response indicates success
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Check if the response indicates failure
     */
    public function isFailure(): bool
    {
        return !$this->success;
    }

    /**
     * Get metadata value
     */
    public function getMetadata(string $key, $default = null)
    {
        return data_get($this->metadata, $key, $default);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message_id' => $this->messageId,
            'error_message' => $this->errorMessage,
            'metadata' => $this->metadata,
            'cost' => $this->cost,
            'status' => $this->status,
        ];
    }
}


