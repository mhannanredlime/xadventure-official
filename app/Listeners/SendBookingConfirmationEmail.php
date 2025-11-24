<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Mail\BookingConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmationEmail implements ShouldQueue
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
    public function handle(BookingConfirmed $event): void
    {
        try {
            $reservation = $event->reservation;
            $customer = $reservation->customer;

            // Check if customer has a valid email address
            if (!$customer->email) {
                Log::warning('Cannot send booking confirmation email: No email address', [
                    'reservation_id' => $reservation->id,
                    'customer_id' => $customer->id,
                ]);
                return;
            }

            // Check if email notifications are enabled
            if (!config('mail.enabled', true)) {
                Log::info('Email notifications disabled, skipping booking confirmation email', [
                    'reservation_id' => $reservation->id,
                ]);
                return;
            }

            Log::info('Sending booking confirmation email', [
                'reservation_id' => $reservation->id,
                'booking_code' => $reservation->booking_code,
                'customer_email' => $customer->email,
            ]);

            // Send the email
            Mail::to($customer->email)
                ->send(new BookingConfirmationMail($reservation, $event->bookingData));

            Log::info('Booking confirmation email sent successfully', [
                'reservation_id' => $reservation->id,
                'booking_code' => $reservation->booking_code,
                'customer_email' => $customer->email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation email', [
                'reservation_id' => $event->reservation->id,
                'customer_email' => $event->reservation->customer->email ?? 'unknown',
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
    public function failed(BookingConfirmed $event, \Throwable $exception): void
    {
        Log::error('Booking confirmation email job failed permanently', [
            'reservation_id' => $event->reservation->id,
            'customer_email' => $event->reservation->customer->email ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);
    }
}
