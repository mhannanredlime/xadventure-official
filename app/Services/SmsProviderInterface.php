<?php

namespace App\Services;

/**
 * Interface for SMS providers
 * 
 * This interface defines the contract that all SMS providers must implement.
 * It ensures consistency across different SMS service implementations.
 */
interface SmsProviderInterface
{
    /**
     * Send an SMS message
     * 
     * @param string $to Recipient phone number
     * @param string $message SMS message content
     * @param array $options Additional options (sender_id, priority, etc.)
     * @return SmsResponse Response from the SMS provider
     * @throws \Exception When SMS sending fails
     */
    public function send(string $to, string $message, array $options = []): SmsResponse;

    /**
     * Get account balance
     * 
     * @return float Account balance amount
     * @throws \Exception When balance check fails
     */
    public function getBalance(): float;

    /**
     * Get delivery status of a message
     * 
     * @param string $messageId Provider's message ID
     * @return string Delivery status (pending, sent, delivered, failed)
     * @throws \Exception When status check fails
     */
    public function getDeliveryStatus(string $messageId): string;

    /**
     * Validate phone number format
     * 
     * @param string $phone Phone number to validate
     * @return bool True if valid, false otherwise
     */
    public function validatePhoneNumber(string $phone): bool;

    /**
     * Get provider name
     * 
     * @return string Provider name (e.g., 'mim', 'twilio')
     */
    public function getProviderName(): string;

    /**
     * Check if provider is configured and ready
     * 
     * @return bool True if ready, false otherwise
     */
    public function isConfigured(): bool;

    /**
     * Test provider connectivity
     * 
     * @return bool True if connection successful, false otherwise
     * @throws \Exception When connection test fails
     */
    public function testConnection(): bool;
}


