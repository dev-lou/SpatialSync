<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function sales()
    {
        return view('contact-sales');
    }

    public function submit(Request $request)
    {
        // Simulate form submission
        return back()->with('success', 'Thank you! A sales representative will contact you shortly.');
    }
}
