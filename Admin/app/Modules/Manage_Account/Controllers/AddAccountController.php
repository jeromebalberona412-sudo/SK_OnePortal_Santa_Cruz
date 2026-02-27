<?php

namespace App\Modules\Manage_Account\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;

class AddAccountController extends Controller
{
    /**
     * Show the Add SK Federation form
     */
    public function createSkFed(Request $request)
    {
        return view('manage_account::manage_account', ['user' => $request->user()]);
    }

    /**
     * Store the new SK Federation account
     */
    public function storeSkFed(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|min:2|max:100|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            'password_confirmation' => 'required|same:password',
            'barangay' => 'required|string|min:2|max:100',
            'province' => 'required|string|min:2|max:100',
            'position' => 'required|in:sk_chairman,sk_councilor,sk_treasurer,sk_secretary,sk_auditor,sk_pio',
            'term_start' => 'required|date|before_or_equal:today',
            'term_end' => 'required|date|after:term_start',
        ], [
            'full_name.regex' => 'Full name must contain only letters, spaces, hyphens, apostrophes, and periods.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'term_start.before_or_equal' => 'Term start date must be today or in the past.',
            'term_end.after' => 'Term end date must be after term start date.',
        ]);

        // TODO: Create user account in database
        // 1. Hash password
        // 2. Create user record
        // 3. Assign SK Federation role
        // 4. Send welcome email

        return response()->json([
            'success' => true,
            'message' => 'SK Federation account created successfully!',
            'data' => $validated
        ]);
    }
}
