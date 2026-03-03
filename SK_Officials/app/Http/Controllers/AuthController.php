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
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Predefined credentials for demo
        $validEmail = 'jeromebalberona412@gmail.com';
        $validPassword = 'Jerome123!';

        if ($credentials['email'] === $validEmail && $credentials['password'] === $validPassword) {
            // Create a simple session for demo purposes
            Session::put('authenticated', true);
            Session::put('user_email', $credentials['email']);
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => route('dashboard')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'The credentials are incorrect.'
        ], 401);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Session::flush();
        return redirect()->route('login');
    }
}
