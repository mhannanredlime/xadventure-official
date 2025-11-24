<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Services\MimSmsService;
use App\Services\SmsTemplateService;
use App\Services\ShortlinkService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendPaymentConfirmationSms implements ShouldQueue
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
    public function handle(PaymentConfirmed $event): void
    {
        try {
            $payment = $event->payment;
            $reservation = $payment->reservation;
            $customer = $reservation->customer;
            
            Log::info('SendPaymentConfirmationSms listener triggered', [
                'payment_id' => $payment->id,
                'reservation_id' => $reservation->id,
                'customer_id' => $customer->id,
                'transaction_id' => $payment->transaction_id
            ]);

            // Check if customer has a valid phone number
            if (!$customer->phone) {
                Log::warning('Cannot send payment confirmation SMS: No phone number', [
                    'payment_id' => $payment->id,
                    'reservation_id' => $reservation->id,
                    'customer_id' => $customer->id,
                ]);
                return;
            }

            // Initialize SMS services
            $smsService = new MimSmsService();
            $templateService = new SmsTemplateService();
            $shortlinkService = app(\App\Services\ShortlinkService::class);

            // Generate booking receipt link with fallback
            $receiptLink = $shortlinkService->generateBookingReceiptLink($reservation);
            if (!$receiptLink) {
                $receiptLink = url('/receipt/' . $reservation->booking_code);
            }
            
            Log::info('Generated receipt link for SMS', [
                'reservation_id' => $reservation->id,
                'booking_code' => $reservation->booking_code,
                'receipt_link' => $receiptLink,
            ]);

            // Prepare template variables
            $variables = [
                'booking_code' => $reservation->booking_code,
                'amount' => number_format($payment->amount, 2),
                'payment_method' => $this->formatPaymentMethod($payment->payment_method),
                'transaction_id' => $payment->transaction_id,
                'customer_name' => $customer->name,
                'package_name' => $reservation->packageVariant->package->name ?? 'Adventure Package',
                'receipt_link' => $receiptLink,
            ];
            
            Log::info('SMS template variables prepared', [
                'reservation_id' => $reservation->id,
                'variables' => $variables,
            ]);

            // Render the SMS message
            $message = $templateService->renderTemplate('payment_confirmation', $variables);
            
            Log::info('SMS message rendered', [
                'reservation_id' => $reservation->id,
                'message' => $message,
                'message_length' => strlen($message),
            ]);

            // Send SMS
            $response = $smsService->sendWithLogging($customer->phone, $message, [
                'template_name' => 'payment_confirmation',
                'payment_id' => $payment->id,
                'reservation_id' => $reservation->id,
                'customer_id' => $customer->id,
                'transaction_id' => $payment->transaction_id,
            ]);

            if ($response->isSuccess()) {
                Log::info('Payment confirmation SMS sent successfully', [
                    'payment_id' => $payment->id,
                    'reservation_id' => $reservation->id,
                    'customer_phone' => $customer->phone,
                    'message_id' => $response->messageId,
                ]);
            } else {
                Log::error('Failed to send payment confirmation SMS', [
                    'payment_id' => $payment->id,
                    'reservation_id' => $reservation->id,
                    'customer_phone' => $customer->phone,
                    'error' => $response->errorMessage,
                ]);
            }

            // Only send payment confirmation SMS - no checkout confirmation SMS needed

        } catch (\Exception $e) {
            Log::error('Error sending payment confirmation SMS', [
                'payment_id' => $event->payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Format payment method for display
     */
    private function formatPaymentMethod($method): string
    {
        $methods = [
            'credit_card' => 'Credit Card',
            'check_payment' => 'Check Payment',
            'bkash' => 'bKash',
            'nagad' => 'Nagad',
            'rocket' => 'Rocket',
            'upay' => 'Upay',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash Payment'
        ];

        return $methods[$method] ?? ucfirst(str_replace('_', ' ', $method));
    }


    /**
     * Handle a job failure.
     */
    public function failed(PaymentConfirmed $event, \Throwable $exception): void
    {
        Log::error('SendPaymentConfirmationSms job failed', [
            'payment_id' => $event->payment->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

