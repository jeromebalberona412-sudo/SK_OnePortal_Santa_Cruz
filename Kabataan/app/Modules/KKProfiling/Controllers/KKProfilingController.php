<?php

namespace App\Modules\KKProfiling\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\KabataanRegistration;
use App\Models\User;
use App\Notifications\KabataanVerifyEmail;
use App\Services\RegistrationEvaluationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class KKProfilingController extends Controller
{
    /**
     * Display signup page with barangay selector
     */
    public function showSignup()
    {
        // Load barangays from DB so we have their IDs for the schedule gate
        $barangays = Barangay::orderBy('name')->get(['id', 'name']);
        return view('kkprofiling::signup', compact('barangays'));
    }

    /**
     * Return the most relevant schedule status per barangay for the signup page.
     * Priority: Ongoing > Upcoming > Rescheduled > Completed > Cancelled
     */
    public function openBarangays()
    {
        $today = now()->toDateString();

        // All schedules within date range (any status)
        $rows = DB::table('kk_profiling_schedules')
            ->where('date_start', '<=', $today)
            ->where('date_expiry', '>=', $today)
            ->get(['barangay_id', 'status', 'date_start', 'date_expiry']);

        $priority = ['Ongoing' => 0, 'Upcoming' => 1, 'Rescheduled' => 2, 'Completed' => 3, 'Cancelled' => 4];

        $map = [];
        foreach ($rows as $row) {
            $id = $row->barangay_id;
            $p  = $priority[$row->status] ?? 99;
            if (!isset($map[$id]) || $p < ($priority[$map[$id]->status] ?? 99)) {
                $map[$id] = $row;
            }
        }

        $result = array_values(array_map(fn($row) => [
            'barangay_id' => $row->barangay_id,
            'status'      => $row->status,
            'date_start'  => $row->date_start,
            'date_expiry' => $row->date_expiry,
        ], $map));

        return response()->json(['schedules' => $result]);
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

        // Map display names — must match exact names in the barangays DB table
        $barangayMap = [
            'alipit'          => 'Alipit',
            'bagumbayan'      => 'Bagumbayan',
            'poblacion-i'     => 'Poblacion I',
            'poblacion-ii'    => 'Poblacion II',
            'poblacion-iii'   => 'Poblacion III',
            'poblacion-iv'    => 'Poblacion IV',
            'poblacion-v'     => 'Poblacion V',
            'bubukal'         => 'Bubukal',
            'calios'          => 'Calios',
            'duhat'           => 'Duhat',
            'gatid'           => 'Gatid',
            'jasaan'          => 'Jasaan',
            'labuin'          => 'Labuin',
            'malinao'         => 'Malinao',
            'oogong'          => 'Oogong',
            'pagsawitan'      => 'Pagsawitan',
            'palasan'         => 'Palasan',
            'patimbao'        => 'Patimbao',
            'san-jose'        => 'San Jose',
            'san-juan'        => 'San Juan',
            'san-pablo-norte' => 'San Pablo Norte',
            'san-pablo-sur'   => 'San Pablo Sur',
            'santisima-cruz'  => 'Santisima Cruz',
            'santo-angel-central' => 'Santo Angel Central',
            'santo-angel-norte'   => 'Santo Angel Norte',
            'santo-angel-sur'     => 'Santo Angel Sur',
        ];

        if (!array_key_exists($slug, $barangayMap)) {
            abort(404);
        }

        $displayName = $barangayMap[$slug];

        // ── Schedule gate ──────────────────────────────────────────────────
        // Block access if the barangay has no active KK Profiling schedule.
        $barangayRecord = Barangay::where('name', $displayName)->first();

        if (!$barangayRecord) {
            abort(404);
        }

        $today = now()->toDateString();
        $hasActiveSchedule = DB::table('kk_profiling_schedules')
            ->where('barangay_id', $barangayRecord->id)
            ->where('status', 'Ongoing')
            ->where('date_start', '<=', $today)
            ->where('date_expiry', '>=', $today)
            ->exists();

        if (!$hasActiveSchedule) {
            return redirect()->route('kkprofiling.signup')
                ->withErrors(['schedule' => 'KK Profiling sign-up for ' . $displayName . ' is not currently open.']);
        }
        // ──────────────────────────────────────────────────────────────────

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
        \Log::info('Form submission received', [
            'barangay' => $barangay,
            'data' => $request->all()
        ]);

        $validated = $request->validate([
            'last_name'             => 'required|string|max:100',
            'first_name'            => 'required|string|max:100',
            'middle_name'           => 'nullable|string|max:100',
            'suffix'                => 'nullable|string|max:10',
            'purok_zone'            => 'required|string|max:100',
            'sex'                   => 'required|in:Male,Female',
            'age'                   => 'required|integer|min:15|max:30',
            'birthday'              => 'required|date',
            'email'                 => 'required|email|max:150',
            'contact_number'        => 'nullable|string|max:15',
            'signature'             => 'required|string',
        ]);

        // Capture all other fields without validation
        $validated['civil_status'] = $request->input('civil_status', []);
        $validated['youth_classification'] = $request->input('youth_classification', []);
        $validated['youth_age_group'] = $request->input('youth_age_group', []);
        $validated['work_status'] = $request->input('work_status', []);
        $validated['education'] = $request->input('education', []);
        $validated['sk_voter'] = $request->input('sk_voter');
        $validated['national_voter'] = $request->input('national_voter');
        $validated['sk_voted'] = $request->input('sk_voted');
        $validated['vote_frequency'] = $request->input('vote_frequency');
        $validated['kk_assembly'] = $request->input('kk_assembly');
        $validated['kk_reason'] = $request->input('kk_reason', []);
        $validated['facebook'] = $request->input('facebook');
        $validated['group_chat'] = $request->input('group_chat');

        \Log::info('Validation passed');

        // Get barangay from slug
        $slug = $this->normalizeSlug($barangay);
        $barangayName = $this->getBarangayName($slug);
        
        if (!$barangayName) {
            abort(404);
        }

        $barangayRecord = Barangay::where('name', $barangayName)->first();
        
        if (!$barangayRecord) {
            return back()->withErrors(['barangay' => 'Barangay not found in database.']);
        }

        // Create or update registration
        $registration = KabataanRegistration::updateOrCreate(
            [
                'email' => $validated['email'],
                'barangay_id' => $barangayRecord->id,
            ],
            [
                'tenant_id'         => $barangayRecord->tenant_id,
                'last_name'         => $validated['last_name'],
                'first_name'        => $validated['first_name'],
                'middle_name'       => $validated['middle_name'] ?? null,
                'suffix'            => $validated['suffix'] ?? null,
                'contact_number'    => $validated['contact_number'] ?? null,
                'form_data'         => $validated,
                'status'            => 'pending_verification',
                'evaluation_status' => null,
                'evaluation_notes'  => null,
                'review_notes'      => null,
                'submitted_at'      => now(),
            ]
        );

        // Generate signed verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'kkprofiling.verify',
            now()->addHours(24),
            [
                'id' => $registration->id,
                'hash' => sha1($registration->email),
            ]
        );

        // Send verification email
        try {
            \Log::info('Attempting to send verification email', [
                'email' => $registration->email,
                'url' => $verificationUrl
            ]);
            
            Notification::route('mail', $registration->email)
                ->notify(new KabataanVerifyEmail($verificationUrl));
                
            \Log::info('Verification email sent successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return redirect()
            ->route('kkprofiling.check-email')
            ->with('email', $registration->email);
    }

    /**
     * Show check email page after registration
     */
    public function showCheckEmail()
    {
        $email = session('email');
        
        if (!$email) {
            return redirect()->route('kkprofiling.signup');
        }

        return view('kkprofiling::check_email', ['email' => $email]);
    }

    private function normalizeSlug(string $barangay): string
    {
        $slug = strtolower(trim($barangay));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }

    private function getBarangayName(string $slug): ?string
    {
        $barangayMap = [
            'alipit'          => 'Alipit',
            'bagumbayan'      => 'Bagumbayan',
            'poblacion-i'     => 'Poblacion I',
            'poblacion-ii'    => 'Poblacion II',
            'poblacion-iii'   => 'Poblacion III',
            'poblacion-iv'    => 'Poblacion IV',
            'poblacion-v'     => 'Poblacion V',
            'bubukal'         => 'Bubukal',
            'calios'          => 'Calios',
            'duhat'           => 'Duhat',
            'gatid'           => 'Gatid',
            'jasaan'          => 'Jasaan',
            'labuin'          => 'Labuin',
            'malinao'         => 'Malinao',
            'oogong'          => 'Oogong',
            'pagsawitan'      => 'Pagsawitan',
            'palasan'         => 'Palasan',
            'patimbao'        => 'Patimbao',
            'san-jose'        => 'San Jose',
            'san-juan'        => 'San Juan',
            'san-pablo-norte' => 'San Pablo Norte',
            'san-pablo-sur'   => 'San Pablo Sur',
            'santisima-cruz'  => 'Santisima Cruz',
            'santo-angel-central' => 'Santo Angel Central',
            'santo-angel-norte'   => 'Santo Angel Norte',
            'santo-angel-sur'     => 'Santo Angel Sur',
        ];

        return $barangayMap[$slug] ?? null;
    }

    /**
     * Verify email from signed URL
     */
    public function verifyEmail(Request $request, int $id, string $hash)
    {
        if (!URL::hasValidSignature($request)) {
            return redirect()->route('kkprofiling.signup')->withErrors([
                'verification' => 'The verification link is invalid or expired.',
            ]);
        }

        $registration = KabataanRegistration::find($id);

        if (!$registration || !hash_equals($hash, sha1($registration->email))) {
            return redirect()->route('kkprofiling.signup')->withErrors([
                'verification' => 'The verification link is invalid.',
            ]);
        }

        if ($registration->status === 'pending_verification') {
            $registration->markEmailVerified();
        }

        // Store registration ID in session for password setup
        session(['kabataan_registration_id' => $registration->id]);

        return redirect()->route('kkprofiling.set-password', [
            'barangay' => $this->getBarangaySlug($registration->barangay->name)
        ])->with('success', 'Email verified! Please set your password to complete registration.');
    }

    private function getBarangaySlug(string $name): string
    {
        $slugMap = [
            'Alipit'              => 'alipit',
            'Bagumbayan'          => 'bagumbayan',
            'Poblacion I'         => 'poblacion-i',
            'Poblacion II'        => 'poblacion-ii',
            'Poblacion III'       => 'poblacion-iii',
            'Poblacion IV'        => 'poblacion-iv',
            'Poblacion V'         => 'poblacion-v',
            'Bubukal'             => 'bubukal',
            'Calios'              => 'calios',
            'Duhat'               => 'duhat',
            'Gatid'               => 'gatid',
            'Jasaan'              => 'jasaan',
            'Labuin'              => 'labuin',
            'Malinao'             => 'malinao',
            'Oogong'              => 'oogong',
            'Pagsawitan'          => 'pagsawitan',
            'Palasan'             => 'palasan',
            'Patimbao'            => 'patimbao',
            'San Jose'            => 'san-jose',
            'San Juan'            => 'san-juan',
            'San Pablo Norte'     => 'san-pablo-norte',
            'San Pablo Sur'       => 'san-pablo-sur',
            'Santisima Cruz'      => 'santisima-cruz',
            'Santo Angel Central' => 'santo-angel-central',
            'Santo Angel Norte'   => 'santo-angel-norte',
            'Santo Angel Sur'     => 'santo-angel-sur',
        ];

        return $slugMap[$name] ?? strtolower(str_replace(' ', '-', $name));
    }

    /**
     * Show the Set Password page (after email verification)
     */
    public function showSetPassword(string $barangay)
    {
        $registrationId = session('kabataan_registration_id');
        
        if (!$registrationId) {
            return redirect()->route('kkprofiling.signup')->withErrors([
                'password' => 'Please verify your email first.',
            ]);
        }

        $registration = KabataanRegistration::find($registrationId);
        
        if (!$registration || $registration->status !== 'email_verified') {
            return redirect()->route('kkprofiling.signup')->withErrors([
                'password' => 'Invalid registration session.',
            ]);
        }

        return view('kkprofiling::set_password', [
            'barangay' => $registration->barangay->name,
            'registration' => $registration,
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

        $registrationId = session('kabataan_registration_id');
        
        if (!$registrationId) {
            return redirect()->route('kkprofiling.signup')->withErrors([
                'password' => 'Session expired. Please verify your email again.',
            ]);
        }

        $registration = KabataanRegistration::find($registrationId);
        
        if (!$registration || $registration->status !== 'email_verified') {
            return redirect()->route('kkprofiling.signup')->withErrors([
                'password' => 'Invalid registration session.',
            ]);
        }

        // Create or reactivate user account
        $user = DB::transaction(function () use ($registration, $request) {
            $existing = User::where('email', $registration->email)->first();

            if ($existing) {
                // Resubmission — update existing user
                $existing->update([
                    'name'               => $registration->full_name,
                    'password'           => bcrypt($request->password),
                    'email_verified_at'  => now(),
                    'status'             => 'PENDING_APPROVAL',
                    'tenant_id'          => $registration->tenant_id,
                    'barangay_id'        => $registration->barangay_id,
                ]);
                $user = $existing;
            } else {
                $user = User::create([
                    'name'               => $registration->full_name,
                    'email'              => $registration->email,
                    'password'           => bcrypt($request->password),
                    'email_verified_at'  => now(),
                    'tenant_id'          => $registration->tenant_id,
                    'barangay_id'        => $registration->barangay_id,
                    'role'               => 'kabataan',
                    'status'             => 'PENDING_APPROVAL',
                ]);
            }

            $registration->markPasswordSet();
            $registration->markActive($user->id);

            return $user;
        });

        // Evaluate against previous kabataan records
        $evaluator = new RegistrationEvaluationService();
        $autoApproved = $evaluator->evaluate($registration);

        if ($autoApproved) {
            // Update user to ACTIVE immediately
            $user->update(['status' => 'ACTIVE']);
        }

        session()->forget('kabataan_registration_id');

        $message = $autoApproved
            ? 'Registration completed! Your account has been automatically approved. You can now log in.'
            : 'Registration completed! Your submission is pending review by SK officials.';

        return redirect()->route('login')->with('success', $message);
    }
}
