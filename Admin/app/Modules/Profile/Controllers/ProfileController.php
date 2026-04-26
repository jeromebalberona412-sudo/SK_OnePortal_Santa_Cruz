<?php

namespace App\Modules\Profile\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        return view('profile::profile', ['user' => $request->user()]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password'      => ['required', 'string'],
            'password'              => ['required', 'string', Password::min(8), 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->forceFill(['password' => Hash::make($validated['password'])])->save();

        return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
    }
}
