<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Reservation $reservation;
    public array $bookingData;

    /**
     * Create a new message instance.
     */
    public function __construct(Reservation $reservation, array $bookingData = [])
    {
        $this->reservation = $reservation;
        $this->bookingData = $bookingData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmation - ' . $this->reservation->booking_code,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmation',
            with: [
                'reservation' => $this->reservation,
                'customer' => $this->reservation->customer,
                'package' => $this->reservation->packageVariant->package,
                'packageVariant' => $this->reservation->packageVariant,
                'bookingData' => $this->bookingData,
                'receiptLink' => $this->getReceiptLink(),
            ],
        );
    }

    /**
     * Get the receipt link for the email
     */
    protected function getReceiptLink(): string
    {
        $shortlinkService = app(\App\Services\ShortlinkService::class);
        return $shortlinkService->generateBookingReceiptLink($this->reservation);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
