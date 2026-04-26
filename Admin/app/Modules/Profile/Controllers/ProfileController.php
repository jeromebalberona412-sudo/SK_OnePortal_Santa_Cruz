<?php

namespace App\Modules\Profile\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        return view('profile::profile', ['user' => $request->user()]);
    }

    public function showChangePassword()
    {
        return view('profile::change-password');
    }

    public function sendChangePasswordLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showChangeEmail()
    {
        return view('profile::change-email');
    }
}
