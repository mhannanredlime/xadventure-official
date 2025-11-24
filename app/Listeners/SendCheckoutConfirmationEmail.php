<?php

namespace App\Listeners;

use App\Events\CheckoutCompleted;
use App\Mail\CheckoutConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCheckoutConfirmationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * Handle the event.
     */
    public function handle(CheckoutCompleted $event): void
    {
        // DISABLED: Checkout email notifications are disabled
        // Only payment completion emails should be sent
        Log::info('SendCheckoutConfirmationEmail listener called but DISABLED - no email sent at checkout time');
        return;
        
        try {
            $customer = $event->customer;
            $reservations = $event->reservations;
            $checkoutData = $event->checkoutData;

            // Check if customer has a valid email address
            if (!$customer->email) {
                Log::warning('Cannot send checkout confirmation email: No email address', [
                    'customer_id' => $customer->id,
                    'reservation_count' => count($reservations),
                ]);
                return;
            }

            // Check if email notifications are enabled
            if (!config('mail.enabled', true)) {
                Log::info('Email notifications disabled, skipping checkout confirmation email', [
                    'customer_id' => $customer->id,
                    'reservation_count' => count($reservations),
                ]);
                return;
            }

            Log::info('Sending checkout confirmation email', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email,
                'reservation_count' => count($reservations),
                'booking_codes' => collect($reservations)->pluck('booking_code')->toArray(),
                'payment_method' => $checkoutData['payment_method'] ?? 'unknown',
            ]);

            // Send the email
            Mail::to($customer->email)
                ->send(new CheckoutConfirmationMail($customer, $reservations, $checkoutData));

            Log::info('Checkout confirmation email sent successfully', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email,
                'reservation_count' => count($reservations),
                'booking_codes' => collect($reservations)->pluck('booking_code')->toArray(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send checkout confirmation email', [
                'customer_id' => $event->customer->id,
                'customer_email' => $event->customer->email ?? 'unknown',
                'reservation_count' => count($event->reservations),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw the exception to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(CheckoutCompleted $event, \Throwable $exception): void
    {
        Log::error('Checkout confirmation email job failed permanently', [
            'customer_id' => $event->customer->id,
            'customer_email' => $event->customer->email ?? 'unknown',
            'reservation_count' => count($event->reservations),
            'error' => $exception->getMessage(),
        ]);
    }
}
