<?php

namespace App\Modules\Authentication\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('authentication::login');
    }

    public function login(Request $request): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    public function showPasswordRequest(): View
    {
        return view('authentication::login');
    }

    public function sendPasswordEmail(): RedirectResponse
    {
        return back()->with('status', 'Password reset link sent!');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        return redirect()->route('login');
    }
}