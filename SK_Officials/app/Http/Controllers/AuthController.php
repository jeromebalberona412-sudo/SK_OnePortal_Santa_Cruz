<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Predefined credentials for demo
        $validEmail    = 'jeromebalberona412@gmail.com';
        $validPassword = 'Jerome123!';

        if ($credentials['email'] === $validEmail && $credentials['password'] === $validPassword) {
            Session::put('authenticated', true);
            Session::put('user_email', $credentials['email']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Login successful',
                    'redirect' => route('dashboard'),
                ]);
            }

            return redirect()->route('dashboard');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'The credentials are incorrect.',
            ], 401);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'The credentials are incorrect.']);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
