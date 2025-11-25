<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class CustomerContactManage extends Controller
{
    public function contacts(Request $request)
    {
        $query = Contact::query();
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%$search%")
                   ->orWhere('email', 'like', "%$search%")
                   ->orWhere('subject', 'like', "%$search%");
            });
        });
        $data = [
            'items'      => $query->latest()->paginate(15),
            'page_title' => 'Contact Messages',
            'page_desc'  => 'View all contact form messages submitted by visitors.',
        ];

        return view('admin.inbox.contacts', $data);
    }
}
