<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Services\MimSmsService;
use App\Services\SmsTemplateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendBookingCancellationSms implements ShouldQueue
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
    public function handle(BookingCancelled $event): void
    {
        try {
            $reservation = $event->reservation;
            $customer = $reservation->customer;

            // Check if customer has a valid phone number
            if (!$customer->phone) {
                Log::warning('Cannot send booking cancellation SMS: No phone number', [
                    'reservation_id' => $reservation->id,
                    'customer_id' => $customer->id,
                ]);
                return;
            }

            // Initialize SMS services
            $smsService = new MimSmsService();
            $templateService = new SmsTemplateService();

            // Calculate refund amount
            $refundAmount = $reservation->total_amount;
            if (isset($event->cancellationData['refund_amount'])) {
                $refundAmount = $event->cancellationData['refund_amount'];
            }

            // Prepare template variables
            $variables = [
                'booking_code' => $reservation->booking_code,
                'refund_amount' => number_format($refundAmount, 2),
                'customer_name' => $customer->name,
                'package_name' => $reservation->packageVariant->package->name ?? 'Adventure Package',
                'contact_number' => '+880 1712 345678', // Default contact number
            ];

            // Render the SMS message
            $message = $templateService->renderTemplate('booking_cancelled', $variables);

            // Send SMS
            $response = $smsService->sendWithLogging($customer->phone, $message, [
                'template_name' => 'booking_cancelled',
                'reservation_id' => $reservation->id,
                'customer_id' => $customer->id,
                'booking_code' => $reservation->booking_code,
                'refund_amount' => $refundAmount,
            ]);

            if ($response->isSuccess()) {
                Log::info('Booking cancellation SMS sent successfully', [
                    'reservation_id' => $reservation->id,
                    'customer_phone' => $customer->phone,
                    'message_id' => $response->messageId,
                    'refund_amount' => $refundAmount,
                ]);
            } else {
                Log::error('Failed to send booking cancellation SMS', [
                    'reservation_id' => $reservation->id,
                    'customer_phone' => $customer->phone,
                    'error' => $response->errorMessage,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error sending booking cancellation SMS', [
                'reservation_id' => $event->reservation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(BookingCancelled $event, \Throwable $exception): void
    {
        Log::error('SendBookingCancellationSms job failed', [
            'reservation_id' => $event->reservation->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

