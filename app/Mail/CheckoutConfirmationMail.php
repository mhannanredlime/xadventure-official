<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Customer $customer;
    public array $reservations;
    public array $checkoutData;

    /**
     * Create a new message instance.
     */
    public function __construct(Customer $customer, array $reservations, array $checkoutData = [])
    {
        $this->customer = $customer;
        $this->reservations = $reservations;
        $this->checkoutData = $checkoutData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $bookingCodes = collect($this->reservations)->pluck('booking_code')->join(', ');
        return new Envelope(
            subject: 'Checkout Confirmation - ' . $bookingCodes,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.checkout-confirmation',
            with: [
                'customer' => $this->customer,
                'reservations' => $this->reservations,
                'checkoutData' => $this->checkoutData,
                'totalAmount' => $this->getTotalAmount(),
                'receiptLink' => $this->getReceiptLink(),
            ],
        );
    }

    /**
     * Calculate total amount for all reservations
     */
    protected function getTotalAmount(): float
    {
        return collect($this->reservations)->sum('total_amount');
    }

    /**
     * Get the receipt link for the email
     */
    protected function getReceiptLink(): string
    {
        $shortlinkService = app(\App\Services\ShortlinkService::class);
        return $shortlinkService->getCheckoutShortlink($this->customer, $this->reservations);
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
