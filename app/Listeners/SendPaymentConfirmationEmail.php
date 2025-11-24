<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Mail\PaymentConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentConfirmationEmail implements ShouldQueue
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
    public function handle(PaymentConfirmed $event): void
    {
        try {
            $payment = $event->payment;
            $customer = $payment->reservation->customer;

            // Check if customer has a valid email address
            if (!$customer->email) {
                Log::warning('Cannot send payment confirmation email: No email address', [
                    'payment_id' => $payment->id,
                    'reservation_id' => $payment->reservation_id,
                    'customer_id' => $customer->id,
                ]);
                return;
            }

            // Check if email notifications are enabled
            if (!config('mail.enabled', true)) {
                Log::info('Email notifications disabled, skipping payment confirmation email', [
                    'payment_id' => $payment->id,
                ]);
                return;
            }

            Log::info('Sending payment confirmation email', [
                'payment_id' => $payment->id,
                'reservation_id' => $payment->reservation_id,
                'booking_code' => $payment->reservation->booking_code,
                'customer_email' => $customer->email,
            ]);

            // Send the email
            Mail::to($customer->email)
                ->send(new PaymentConfirmationMail($payment, $event->paymentData));

            Log::info('Payment confirmation email sent successfully', [
                'payment_id' => $payment->id,
                'reservation_id' => $payment->reservation_id,
                'booking_code' => $payment->reservation->booking_code,
                'customer_email' => $customer->email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email', [
                'payment_id' => $event->payment->id,
                'reservation_id' => $event->payment->reservation_id,
                'customer_email' => $event->payment->reservation->customer->email ?? 'unknown',
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
    public function failed(PaymentConfirmed $event, \Throwable $exception): void
    {
        Log::error('Payment confirmation email job failed permanently', [
            'payment_id' => $event->payment->id,
            'reservation_id' => $event->payment->reservation_id,
            'customer_email' => $event->payment->reservation->customer->email ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);
    }
}
