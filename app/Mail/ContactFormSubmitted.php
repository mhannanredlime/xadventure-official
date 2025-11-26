<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $subject;
    public $user_message; // ✅ NOT "message"
    public $received_at;

    public function __construct($name, $email, $subject, $user_message, $received_at)
    {
        $this->name = $name;
        $this->email = $email;
        $this->subject = $subject;
        $this->user_message = $user_message; // ✅ safe
        $this->received_at = $received_at;
    }

    public function build()
    {
        return $this->subject('New Contact Form Submission: ' . $this->subject)
                    ->view('emails.contact-notification');
    }
}
