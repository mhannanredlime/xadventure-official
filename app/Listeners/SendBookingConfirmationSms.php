<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Services\MimSmsService;
use App\Services\SmsTemplateService;
use App\Services\ShortlinkService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendBookingConfirmationSms implements ShouldQueue
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
    public function handle(BookingConfirmed $event): void
    {
        Log::info('SendBookingConfirmationSms listener started', [
            'reservation_id' => $event->reservation->id,
            'booking_code' => $event->reservation->booking_code,
            'event_data' => $event->additionalData ?? [],
        ]);
        
        try {
            $reservation = $event->reservation;
            
            // Ensure the reservation has the required relationships loaded
            if (!$reservation->relationLoaded('customer')) {
                $reservation->load(['customer', 'packageVariant.package']);
            }
            
            $customer = $reservation->customer;

            Log::info('Processing booking confirmation SMS', [
                'reservation_id' => $reservation->id,
                'booking_code' => $reservation->booking_code,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'package_name' => $reservation->packageVariant->package->name ?? 'Unknown',
                'total_amount' => $reservation->total_amount,
            ]);

            // Check if customer has a valid phone number
            if (!$customer->phone) {
                Log::warning('Cannot send booking confirmation SMS: No phone number', [
                    'reservation_id' => $reservation->id,
                    'customer_id' => $customer->id,
                ]);
                return;
            }

            // Initialize SMS services
            $smsService = new MimSmsService();
            $templateService = new SmsTemplateService();
            $shortlinkService = new ShortlinkService();

            // Generate receipt link
            $receiptLink = $shortlinkService->getSmsShortlink($reservation);
            
            // Prepare template variables
            $variables = [
                'booking_code' => $reservation->booking_code,
                'date' => $reservation->date->format('Y-m-d'),
                'time' => $reservation->report_time->format('g:i A'),
                'amount' => number_format($reservation->total_amount, 2),
                'package_name' => $reservation->packageVariant->package->name ?? 'Adventure Package',
                'contact_number' => '+880 1712 345678', // Default contact number
                'customer_name' => $customer->name,
                'receipt_link' => $receiptLink,
            ];

            Log::info('SMS template variables prepared', [
                'variables' => $variables,
                'template_name' => 'booking_confirmation',
            ]);

            // Render the SMS message
            $message = $templateService->renderTemplate('booking_confirmation', $variables);
            
            Log::info('SMS message rendered', [
                'message_length' => strlen($message),
                'message_preview' => substr($message, 0, 100) . '...',
            ]);

            // Send SMS
            Log::info('Attempting to send SMS', [
                'phone_number' => $customer->phone,
                'message_length' => strlen($message),
                'reservation_id' => $reservation->id,
            ]);
            
            $response = $smsService->sendWithLogging($customer->phone, $message, [
                'template_name' => 'booking_confirmation',
                'reservation_id' => $reservation->id,
                'customer_id' => $customer->id,
                'booking_code' => $reservation->booking_code,
            ]);

            Log::info('SMS service response received', [
                'success' => $response->isSuccess(),
                'message_id' => $response->messageId ?? null,
                'error_message' => $response->errorMessage ?? null,
                'reservation_id' => $reservation->id,
            ]);

            if ($response->isSuccess()) {
                Log::info('Booking confirmation SMS sent successfully', [
                    'reservation_id' => $reservation->id,
                    'customer_phone' => $customer->phone,
                    'message_id' => $response->messageId,
                    'booking_code' => $reservation->booking_code,
                ]);
            } else {
                Log::warning('Failed to send booking confirmation SMS (non-critical)', [
                    'reservation_id' => $reservation->id,
                    'customer_phone' => $customer->phone,
                    'error' => $response->errorMessage,
                    'booking_code' => $reservation->booking_code,
                    'note' => 'SMS failure does not affect booking process',
                ]);
                // Don't throw exception - SMS failure should not fail the entire job
            }

        } catch (\Exception $e) {
            Log::warning('Error sending booking confirmation SMS (non-critical)', [
                'reservation_id' => $event->reservation->id,
                'error' => $e->getMessage(),
                'note' => 'SMS failure does not affect booking process',
            ]);
            // Don't re-throw exception - SMS failure should not fail the entire job
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(BookingConfirmed $event, \Throwable $exception): void
    {
        Log::error('SendBookingConfirmationSms job failed', [
            'reservation_id' => $event->reservation->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

