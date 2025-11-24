<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Services\MimSmsService;
use App\Services\SmsTemplateService;
use App\Services\ShortlinkService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendAdminNewBookingSms implements ShouldQueue
{
    use InteractsWithQueue;

    public $delay = 10; // Delay SMS sending by 10 seconds

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
        Log::info('SendAdminNewBookingSms listener started', [
            'reservation_id' => $event->reservation->id,
            'booking_code' => $event->reservation->booking_code,
        ]);
        
        try {
            $reservation = $event->reservation;
            
            // Ensure the reservation has the required relationships loaded
            if (!$reservation->relationLoaded('customer')) {
                $reservation->load(['customer', 'packageVariant.package']);
            }
            
            $customer = $reservation->customer;

            Log::info('Processing admin new booking SMS', [
                'reservation_id' => $reservation->id,
                'booking_code' => $reservation->booking_code,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'total_amount' => $reservation->total_amount,
            ]);

            // Get admin phone numbers from configuration
            $adminPhones = config('services.sms.admin_phone_numbers', '');
            
            Log::info('Admin phone configuration', [
                'admin_phones_config' => $adminPhones,
                'reservation_id' => $reservation->id,
            ]);
            if (empty($adminPhones)) {
                Log::warning('No admin phone numbers configured for SMS notifications', [
                    'reservation_id' => $reservation->id,
                ]);
                return;
            }

            // Parse admin phone numbers (comma-separated)
            $adminPhoneList = array_map('trim', explode(',', $adminPhones));
            $adminPhoneList = array_filter($adminPhoneList); // Remove empty values

            if (empty($adminPhoneList)) {
                Log::warning('No valid admin phone numbers found', [
                    'reservation_id' => $reservation->id,
                    'admin_phones' => $adminPhones,
                ]);
                return;
            }

            // Initialize SMS services
            $smsService = new MimSmsService();
            $templateService = new SmsTemplateService();
            $shortlinkService = new ShortlinkService();

            // Generate receipt link for admin
            $receiptLink = $shortlinkService->generateBookingReceiptLink($reservation);
            
            // Prepare template variables
            $variables = [
                'booking_code' => $reservation->booking_code,
                'date' => $reservation->date->format('Y-m-d'),
                'time' => $reservation->report_time->format('g:i A'),
                'customer_name' => $customer->name,
                'amount' => number_format($reservation->total_amount, 2),
                'package_name' => $reservation->packageVariant->package->name ?? 'Adventure Package',
                'receipt_link' => $receiptLink,
            ];

            // Render the SMS message
            $message = $templateService->renderTemplate('admin_new_booking', $variables);

            // Send SMS to all admin phone numbers
            $successCount = 0;
            $failureCount = 0;

            foreach ($adminPhoneList as $adminPhone) {
                try {
                    $response = $smsService->sendWithLogging($adminPhone, $message, [
                        'template_name' => 'admin_new_booking',
                        'reservation_id' => $reservation->id,
                        'customer_id' => $customer->id,
                        'booking_code' => $reservation->booking_code,
                        'admin_phone' => $adminPhone,
                    ]);

                    if ($response->isSuccess()) {
                        $successCount++;
                        Log::info('Admin new booking SMS sent successfully', [
                            'reservation_id' => $reservation->id,
                            'admin_phone' => $adminPhone,
                            'message_id' => $response->messageId,
                        ]);
                    } else {
                        $failureCount++;
                        Log::warning('Failed to send admin new booking SMS (non-critical)', [
                            'reservation_id' => $reservation->id,
                            'admin_phone' => $adminPhone,
                            'error' => $response->errorMessage,
                            'note' => 'SMS failure does not affect booking process',
                        ]);
                        // Don't throw exception - SMS failure should not fail the entire job
                    }
                } catch (\Exception $e) {
                    $failureCount++;
                    Log::warning('Error sending admin new booking SMS (non-critical)', [
                        'reservation_id' => $reservation->id,
                        'admin_phone' => $adminPhone,
                        'error' => $e->getMessage(),
                        'note' => 'SMS failure does not affect booking process',
                    ]);
                    // Don't re-throw exception - SMS failure should not fail the entire job
                }
            }

            // Log summary
            Log::info('Admin new booking SMS summary', [
                'reservation_id' => $reservation->id,
                'total_admin_phones' => count($adminPhoneList),
                'success_count' => $successCount,
                'failure_count' => $failureCount,
            ]);

        } catch (\Exception $e) {
            Log::warning('Error in SendAdminNewBookingSms (non-critical)', [
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
        Log::error('SendAdminNewBookingSms job failed', [
            'reservation_id' => $event->reservation->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

