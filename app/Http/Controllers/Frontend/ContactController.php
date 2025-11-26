<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormSubmitted;

class ContactController extends Controller
{
    public function index()
    {
        return view('frontend.contact');
    }

    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        Log::info('Validated contact form data', $validated);

        try {
            // Save to database
            $contact = Contact::create($validated);

            Log::info('Contact form saved successfully', [
                'contact_id' => $contact->id
            ]);

            // Admin email
            $adminEmail = 'xadventurebandarbanbd@yopmail.com';

            // Send email using Mailable
            Mail::to($adminEmail)->send(
                new ContactFormSubmitted(
                    $validated['name'],
                    $validated['email'],
                    $validated['subject'],
                    $contact->message,
                    now()->format('F j, Y \a\t g:i A')
                )
            );

            Log::info('Email sent to admin for contact form submission', [
                'admin_email' => $adminEmail
            ]);

            return redirect()->back()->with('success', 'Thank you! Your message has been sent successfully.');

        } catch (\Exception $e) {

            Log::error('Contact form submission failed', [
                'error_message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()->back()->with('error', 'Something went wrong! Please try again later.');
        }
    }
}
