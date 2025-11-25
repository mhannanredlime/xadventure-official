<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

        // Sanitize input
        $validated['name'] = strip_tags($validated['name']);
        $validated['email'] = filter_var($validated['email'], FILTER_SANITIZE_EMAIL);
        $validated['subject'] = strip_tags($validated['subject']);
        $validated['message'] = strip_tags($validated['message']);
        // If you want to allow some HTML safely, use purifier:
        // $validated['message'] = clean($validated['message']);

        // TODO: Save to database or send mail here
        Contact::create($validated);

        return redirect()->back()->with(
            'success',
            'Thank you for your message! We will get back to you soon.'
        );
    }
}
