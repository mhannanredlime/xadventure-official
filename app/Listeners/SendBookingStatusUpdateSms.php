<?php

namespace App\Listeners;

use App\Events\BookingStatusUpdated;
use App\Services\MimSmsService;
use App\Services\SmsTemplateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendBookingStatusUpdateSms implements ShouldQueue
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
    public function handle(BookingStatusUpdated $event): void
    {
        try {
            $reservation = $event->reservation;
            $customer = $reservation->customer;
            $oldStatus = $event->oldStatus;
            $newStatus = $event->newStatus;

            // Check if customer has a valid phone number
            if (!$customer->phone) {
                Log::warning('Cannot send booking status update SMS: No phone number', [
                    'reservation_id' => $reservation->id,
                    'customer_id' => $customer->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]);
                return;
            }

            // Only send SMS for specific status transitions
            $shouldSendSms = $this->shouldSendSmsForStatusChange($oldStatus, $newStatus);
            if (!$shouldSendSms) {
                Log::info('Skipping SMS for status change', [
                    'reservation_id' => $reservation->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]);
                return;
            }

            // Initialize SMS services
            $smsService = new MimSmsService();
            $templateService = new SmsTemplateService();

            // Prepare template variables
            $variables = [
                'booking_code' => $reservation->booking_code,
                'old_status' => ucfirst($oldStatus),
                'new_status' => ucfirst($newStatus),
                'date' => $reservation->date->format('Y-m-d'),
                'time' => $reservation->report_time->format('g:i A'),
                'customer_name' => $customer->name,
                'package_name' => $reservation->packageVariant->package->name ?? 'Adventure Package',
                'contact_number' => '+880 1712 345678', // Default contact number
            ];

            // Determine template based on status change
            $templateName = $this->getTemplateForStatusChange($oldStatus, $newStatus);

            // Render the SMS message
            $message = $templateService->renderTemplate($templateName, $variables);

            // Send SMS
            $response = $smsService->sendWithLogging($customer->phone, $message, [
                'template_name' => $templateName,
                'reservation_id' => $reservation->id,
                'customer_id' => $customer->id,
                'booking_code' => $reservation->booking_code,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            if ($response->isSuccess()) {
                Log::info('Booking status update SMS sent successfully', [
                    'reservation_id' => $reservation->id,
                    'customer_phone' => $customer->phone,
                    'message_id' => $response->messageId,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]);
            } else {
                Log::error('Failed to send booking status update SMS', [
                    'reservation_id' => $reservation->id,
                    'customer_phone' => $customer->phone,
                    'error' => $response->errorMessage,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error sending booking status update SMS', [
                'reservation_id' => $event->reservation->id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Determine if SMS should be sent for this status change
     */
    private function shouldSendSmsForStatusChange(string $oldStatus, string $newStatus): bool
    {
        // Send SMS for important status changes
        $importantChanges = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['cancelled', 'completed'],
            'cancelled' => ['confirmed'], // If booking is reactivated
        ];

        return isset($importantChanges[$oldStatus]) && in_array($newStatus, $importantChanges[$oldStatus]);
    }

    /**
     * Get the appropriate template name for the status change
     */
    private function getTemplateForStatusChange(string $oldStatus, string $newStatus): string
    {
        $templateMap = [
            'pending_confirmed' => 'booking_confirmed',
            'pending_cancelled' => 'booking_cancelled',
            'confirmed_cancelled' => 'booking_cancelled',
            'confirmed_completed' => 'booking_completed',
            'cancelled_confirmed' => 'booking_reactivated',
        ];

        $key = $oldStatus . '_' . $newStatus;
        return $templateMap[$key] ?? 'booking_status_update';
    }

    /**
     * Handle a job failure.
     */
    public function failed(BookingStatusUpdated $event, \Throwable $exception): void
    {
        Log::error('SendBookingStatusUpdateSms job failed', [
            'reservation_id' => $event->reservation->id,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

