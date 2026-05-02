<?php

namespace App\Modules\KKProfiling\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KKProfilingController extends Controller
{
    /**
     * Display signup page with barangay selector
     */
    public function showSignup()
    {
        return view('kkprofiling::signup');
    }

    /**
     * Display the KK Profiling form for a specific barangay
     */
    public function show(string $barangay)
    {
        // Normalize slug — strip poblacion suffix if present
        $slug = strtolower(trim($barangay));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Map display names
        $barangayMap = [
            'alipit'                    => 'Alipit',
            'bagumbayan'                => 'Bagumbayan',
            'barangay-i'                => 'Barangay I (Poblacion I)',
            'barangay-i-poblacion-i'    => 'Barangay I (Poblacion I)',
            'barangay-ii'               => 'Barangay II (Poblacion II)',
            'barangay-ii-poblacion-ii'  => 'Barangay II (Poblacion II)',
            'barangay-iii'              => 'Barangay III (Poblacion III)',
            'barangay-iii-poblacion-iii'=> 'Barangay III (Poblacion III)',
            'barangay-iv'               => 'Barangay IV (Poblacion IV)',
            'barangay-iv-poblacion-iv'  => 'Barangay IV (Poblacion IV)',
            'barangay-v'                => 'Barangay V (Poblacion V)',
            'barangay-v-poblacion-v'    => 'Barangay V (Poblacion V)',
            'bubukal'                   => 'Bubukal',
            'calios'                    => 'Calios',
            'duhat'                     => 'Duhat',
            'gatid'                     => 'Gatid',
            'jasaan'                    => 'Jasaan',
            'labuin'                    => 'Labuin',
            'malinao'                   => 'Malinao',
            'oogong'                    => 'Oogong',
            'pagsawitan'                => 'Pagsawitan',
            'palasan'                   => 'Palasan',
            'patimbao'                  => 'Patimbao',
            'san-jose'                  => 'San Jose',
            'san-juan'                  => 'San Juan',
            'san-pablo-norte'           => 'San Pablo Norte',
            'san-pablo-sur'             => 'San Pablo Sur',
            'santisima-cruz'            => 'Santisima Cruz',
            'santo-angel-central'       => 'Santo Angel Central',
            'santo-angel-norte'         => 'Santo Angel Norte',
            'santo-angel-sur'           => 'Santo Angel Sur',
        ];

        if (!array_key_exists($slug, $barangayMap)) {
            abort(404);
        }

        $displayName = $barangayMap[$slug];

        return view('kkprofiling::kkprofiling', [
            'barangay' => $displayName,
            'slug'     => $slug,
        ]);
    }

    /**
     * Handle KK Profiling form submission
     */
    public function submit(Request $request, string $barangay)
    {
        $validated = $request->validate([
            'last_name'             => 'required|string|max:100',
            'first_name'            => 'required|string|max:100',
            'middle_name'           => 'nullable|string|max:100',
            'suffix'                => 'nullable|string|max:10',
            'purok_zone'            => 'required|string|max:100',
            'sex'                   => 'required|in:Male,Female',
            'age'                   => 'required|integer|min:15|max:30',
            'birthday'              => 'required|date',
            'email'                 => 'nullable|email|max:150',
            'contact_number'        => 'nullable|string|max:15',
            'civil_status'          => 'nullable|array',
            'youth_classification'  => 'nullable|array',
            'youth_age_group'       => 'nullable|array',
            'work_status'           => 'nullable|array',
            'education'             => 'nullable|array',
            'sk_voter'              => 'nullable|in:Yes,No',
            'national_voter'        => 'nullable|in:Yes,No',
            'sk_voted'              => 'nullable|in:Yes,No',
            'vote_frequency'        => 'nullable|in:1-2,3-4,5+',
            'kk_assembly'           => 'nullable|in:Yes,No',
            'kk_reason'             => 'nullable|array',
            'facebook'              => 'nullable|string|max:150',
            'group_chat'            => 'nullable|in:Yes,No',
            'signature'             => 'required|string|max:150',
        ]);

        // TODO: Store the data in database
        // For now, just flash success message and redirect back

        return redirect()
            ->route('kkprofiling', ['barangay' => $barangay])
            ->with('success', 'Your KK Profiling submission has been received! It will be reviewed by your barangay SK officials.');
    }

    /**
     * Show the Set Password page (after email verification)
     */
    public function showSetPassword(string $barangay)
    {
        return view('kkprofiling::set_password', [
            'barangay' => $barangay,
        ]);
    }

    /**
     * Handle password creation after email verification
     */
    public function storePassword(Request $request, string $barangay)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        // TODO: Save password to the pending KK profile record
        // For now redirect to login with success message
        return redirect('/youth/login')
            ->with('success', 'Registration completed successfully! You can now login with your credentials.');
    }
}
