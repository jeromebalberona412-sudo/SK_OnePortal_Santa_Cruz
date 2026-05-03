<?php

namespace App\Modules\Profile\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user         = Auth::user();
        $registration = KabataanRegistration::where('user_id', $user->id)->latest()->first();
        $formData     = $registration?->form_data ?? [];

        $profileUser = (object) [
            'id'             => $user->id,
            'first_name'     => $registration?->first_name  ?? $user->name,
            'last_name'      => $registration?->last_name   ?? '',
            'middle_initial' => $registration?->middle_name ? substr($registration->middle_name, 0, 1) : '',
            'suffix'         => $registration?->suffix      ?? '',
            'email'          => $user->email,
            'contact_number' => $registration?->contact_number ?? '',
            'barangay'       => $registration?->barangay?->name ?? '',
            'municipality'   => 'Santa Cruz',
            'province'       => 'Laguna',
            'birthdate'      => $formData['birthday'] ?? '',
            'age'            => $formData['age']      ?? '',
            'sex'            => $formData['sex']      ?? '',
            'purok_zone'     => $formData['purok_zone'] ?? '',
        ];

        return view('profile::index', [
            'user'              => $profileUser,
            'programs'          => collect(),
            'totalPrograms'     => 0,
            'ongoingPrograms'   => 0,
            'completedPrograms' => 0,
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }

    public function update(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'middle_initial' => 'nullable|string|max:1',
            'suffix'         => 'nullable|string|max:10',
            'contact_number' => 'nullable|string|max:15',
            'birthdate'      => 'nullable|date',
        ]);

        $registration = KabataanRegistration::where('user_id', Auth::id())->latest()->first();

        if ($registration) {
            $registration->update([
                'first_name'     => $validated['first_name'],
                'last_name'      => $validated['last_name'],
                'middle_name'    => $validated['middle_initial'] ?? null,
                'suffix'         => $validated['suffix'] ?? null,
                'contact_number' => $validated['contact_number'] ?? null,
                'form_data'      => array_merge($registration->form_data ?? [], [
                    'birthday' => $validated['birthdate'] ?? ($registration->form_data['birthday'] ?? null),
                ]),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function settings()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user         = Auth::user();
        $registration = KabataanRegistration::where('user_id', $user->id)->latest()->first();

        $profileUser = (object) [
            'id'         => $user->id,
            'first_name' => $registration?->first_name ?? $user->name,
            'last_name'  => $registration?->last_name  ?? '',
            'email'      => $user->email,
        ];

        return view('profile::settings', ['user' => $profileUser]);
    }
}
