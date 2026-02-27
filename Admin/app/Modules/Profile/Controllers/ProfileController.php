<?php

namespace App\Modules\Profile\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        return view('profile::profile', ['user' => $request->user()]);
    }
}
