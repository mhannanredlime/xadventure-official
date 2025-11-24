<?php

namespace App\Listeners;

use App\Events\CheckoutCompleted;
use App\Services\MimSmsService;
use App\Services\SmsTemplateService;
use App\Services\ShortlinkService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * SendCheckoutConfirmationSms Listener
 * 
 * IMPORTANT: This listener sends ONLY ONE SMS per checkout, regardless of how many
 * packages are in the order. It combines all booking information into a single SMS.
 * 
 * This prevents SMS duplication for multi-package bookings.
 */
class SendCheckoutConfirmationSms implements ShouldQueue
{
    use InteractsWithQueue;

    public $delay = 5; // Delay SMS sending by 5 seconds

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CheckoutCompleted $event): void
    {
        // DISABLED: Checkout SMS notifications are disabled
        // Only payment completion SMS should be sent
        Log::info('SendCheckoutConfirmationSms listener called but DISABLED - no SMS sent at checkout time');
        return;
        
        // Ensure we only send ONE SMS per checkout, not per reservation
        if (empty($event->reservations)) {
            Log::warning('No reservations found in CheckoutCompleted event, skipping SMS');
            return;
        }
        
        // Get transaction ID to prevent duplicate SMS
        $transactionId = $event->checkoutData['transaction_id'] ?? null;
        if ($transactionId) {
            $cacheKey = "sms_sent_checkout_{$transactionId}";
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                Log::info('SMS already sent for this checkout, skipping duplicate', [
                    'transaction_id' => $transactionId,
                    'customer_id' => $event->customer->id,
                ]);
                return;
            }
        }
        
        try {
            $customer = $event->customer;
            $reservations = $event->reservations;

            // Check if customer has a valid phone number
            if (!$customer->phone) {
                Log::warning('Cannot send checkout confirmation SMS: No phone number', [
                    'customer_id' => $customer->id,
                ]);
                return;
            }

            // Initialize SMS services
            $smsService = new MimSmsService();
            $templateService = new SmsTemplateService();
            $shortlinkService = new ShortlinkService();

            // Calculate total amount for all reservations
            $totalAmount = 0;
            $packageNames = [];
            $bookingCodes = [];
            
            foreach ($reservations as $reservation) {
                $totalAmount += $reservation->total_amount;
                $packageNames[] = $reservation->packageVariant->package->name ?? 'Adventure Package';
                $bookingCodes[] = $reservation->booking_code;
            }

            // Generate checkout-level receipt link that shows all reservations
            $receiptLink = $shortlinkService->getCheckoutShortlink($customer, $reservations);
            
            // Get first reservation for date/time info
            $firstReservation = $reservations[0];
            
            // Prepare template variables
            $variables = [
                'booking_codes' => implode(', ', $bookingCodes),
                'package_names' => implode(', ', array_unique($packageNames)),
                'package_count' => count($reservations),
                'date' => $firstReservation->date->format('Y-m-d'),
                'time' => $firstReservation->report_time->format('g:i A'),
                'total_amount' => number_format($totalAmount, 2),
                'contact_number' => '+880 1712 345678', // Default contact number
                'customer_name' => $customer->name,
                'receipt_link' => $receiptLink,
            ];

            Log::info('SMS template variables prepared', [
                'variables' => $variables,
                'template_name' => 'checkout_confirmation',
            ]);

            // Render the SMS message
            $message = $templateService->renderTemplate('checkout_confirmation', $variables);
            
            Log::info('SMS message rendered', [
                'message_length' => strlen($message),
                'message_preview' => substr($message, 0, 100) . '...',
            ]);

            // Send SMS
            Log::info('Attempting to send checkout confirmation SMS', [
                'phone_number' => $customer->phone,
                'message_length' => strlen($message),
                'reservation_count' => count($reservations),
            ]);
            
            $response = $smsService->sendWithLogging($customer->phone, $message, [
                'template_name' => 'checkout_confirmation',
                'customer_id' => $customer->id,
                'reservation_count' => count($reservations),
                'booking_codes' => implode(',', $bookingCodes),
            ]);

            Log::info('SMS service response received', [
                'success' => $response->isSuccess(),
                'message_id' => $response->messageId ?? null,
                'error_message' => $response->errorMessage ?? null,
                'customer_id' => $customer->id,
            ]);

            if ($response->isSuccess()) {
                // Mark SMS as sent to prevent duplicates
                if ($transactionId) {
                    \Illuminate\Support\Facades\Cache::put("sms_sent_checkout_{$transactionId}", true, 3600); // 1 hour cache
                }
                
                Log::info('Checkout confirmation SMS sent successfully', [
                    'customer_id' => $customer->id,
                    'customer_phone' => $customer->phone,
                    'message_id' => $response->messageId,
                    'reservation_count' => count($reservations),
                    'booking_codes' => implode(',', $bookingCodes),
                    'transaction_id' => $transactionId,
                ]);
            } else {
                Log::warning('Failed to send checkout confirmation SMS (non-critical)', [
                    'customer_id' => $customer->id,
                    'customer_phone' => $customer->phone,
                    'error' => $response->errorMessage,
                    'reservation_count' => count($reservations),
                    'note' => 'SMS failure does not affect booking process',
                ]);
                // Don't throw exception - SMS failure should not fail the entire job
            }

        } catch (\Exception $e) {
            Log::warning('Error sending checkout confirmation SMS (non-critical)', [
                'customer_id' => $event->customer->id,
                'error' => $e->getMessage(),
                'note' => 'SMS failure does not affect booking process',
            ]);
            // Don't re-throw exception - SMS failure should not fail the entire job
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(CheckoutCompleted $event, \Throwable $exception): void
    {
        Log::error('SendCheckoutConfirmationSms job failed', [
            'customer_id' => $event->customer->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
