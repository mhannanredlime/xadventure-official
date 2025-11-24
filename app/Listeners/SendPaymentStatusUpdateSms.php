<?php

namespace App\Listeners;

use App\Events\PaymentStatusUpdated;
use App\Services\MimSmsService;
use App\Services\SmsTemplateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendPaymentStatusUpdateSms implements ShouldQueue
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
    public function handle(PaymentStatusUpdated $event): void
    {
        try {
            $payment = $event->payment;
            $reservation = $payment->reservation;
            $customer = $reservation->customer;
            $oldStatus = $event->oldStatus;
            $newStatus = $event->newStatus;

            // Check if customer has a valid phone number
            if (!$customer->phone) {
                Log::warning('Cannot send payment status update SMS: No phone number', [
                    'payment_id' => $payment->id,
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
                Log::info('Skipping SMS for payment status change', [
                    'payment_id' => $payment->id,
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
                'amount' => number_format($payment->amount, 2),
                'payment_method' => $this->formatPaymentMethod($payment->payment_method),
                'transaction_id' => $payment->transaction_id,
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
                'payment_id' => $payment->id,
                'reservation_id' => $reservation->id,
                'customer_id' => $customer->id,
                'booking_code' => $reservation->booking_code,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            if ($response->isSuccess()) {
                Log::info('Payment status update SMS sent successfully', [
                    'payment_id' => $payment->id,
                    'reservation_id' => $reservation->id,
                    'customer_phone' => $customer->phone,
                    'message_id' => $response->messageId,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]);
            } else {
                Log::error('Failed to send payment status update SMS', [
                    'payment_id' => $payment->id,
                    'reservation_id' => $reservation->id,
                    'customer_phone' => $customer->phone,
                    'error' => $response->errorMessage,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error sending payment status update SMS', [
                'payment_id' => $event->payment->id,
                'reservation_id' => $event->payment->reservation->id,
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
        // Send SMS for important payment status changes
        $importantChanges = [
            'pending' => ['completed', 'failed', 'refunded'],
            'partial' => ['completed', 'refunded'],
            'completed' => ['refunded'],
            'failed' => ['completed'], // If payment is retried and succeeds
        ];

        return isset($importantChanges[$oldStatus]) && in_array($newStatus, $importantChanges[$oldStatus]);
    }

    /**
     * Get the appropriate template name for the status change
     */
    private function getTemplateForStatusChange(string $oldStatus, string $newStatus): string
    {
        $templateMap = [
            'pending_completed' => 'payment_confirmation',
            'partial_completed' => 'payment_confirmation',
            'pending_failed' => 'payment_failed',
            'completed_refunded' => 'payment_refunded',
            'failed_completed' => 'payment_confirmation',
        ];

        $key = $oldStatus . '_' . $newStatus;
        return $templateMap[$key] ?? 'payment_status_update';
    }

    /**
     * Format payment method for display
     */
    private function formatPaymentMethod(string $method): string
    {
        $formatted = [
            'credit_card' => 'Credit Card',
            'check_payment' => 'Check Payment',
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'amarpay' => 'AmarPay',
        ];

        return $formatted[$method] ?? ucfirst(str_replace('_', ' ', $method));
    }

    /**
     * Handle a job failure.
     */
    public function failed(PaymentStatusUpdated $event, \Throwable $exception): void
    {
        Log::error('SendPaymentStatusUpdateSms job failed', [
            'payment_id' => $event->payment->id,
            'reservation_id' => $event->payment->reservation->id,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

