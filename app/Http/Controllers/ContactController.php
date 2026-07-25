<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;

class ContactController extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email',
            'phone' => 'required|digits_between:7,15',
            'organization' => 'nullable|max:150',
            'message' => 'required|max:1000',
        ]);

        Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->service,
            'organization' => $request->organization,
            'message' => $request->message,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your message has been sent successfully.'
        ]);

    }
}