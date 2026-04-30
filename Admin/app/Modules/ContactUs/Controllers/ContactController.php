<?php

namespace App\Modules\ContactUs\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display the contact management page
     */
    public function index()
    {
        return view('contact-us::manage_contact');
    }
}
