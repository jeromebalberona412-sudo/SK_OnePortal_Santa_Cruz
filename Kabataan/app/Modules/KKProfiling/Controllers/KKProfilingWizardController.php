<?php

namespace App\Modules\KKProfiling\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Notifications\KabataanVerifyEmail;
use App\Services\KkRegistrationDraftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class KKProfilingWizardController extends Controller
{
    public function __construct(
        protected KkRegistrationDraftService $draftService
    ) {}

    public function saveStep1(Request $request, string $barangay)
    {
        $barangayRecord = $this->resolveBarangay($barangay);
        $validated = $this->validateStep1($request);
        $payload = $this->normalizeStep1Payload($request, $validated);

        $wizard = $this->draftService->createOrUpdateStep1(
            $barangayRecord,
            $payload,
            $request->input('respondent_number')
        );

        return response()->json([
            'success' => true,
            'token'   => $wizard['token'],
            'step'    => 2,
            'message' => 'Step 1 saved. Continue to facial verification.',
        ]);
    }

    public function saveStep2(Request $request, string $barangay)
    {
        $this->resolveBarangay($barangay);

        $request->validate([
            'verified_selfie'               => 'required|string',
            'facial_verification_completed' => 'required|in:1',
        ]);

        if ($request->input('facial_verification_completed') !== '1') {
            throw ValidationException::withMessages([
                'verified_selfie' => ['Identity verification is required.'],
            ]);
        }

        $wizard = $this->requireWizard();

        $wizard = $this->draftService->saveStep2(
            $wizard,
            $request->input('verified_selfie')
        );

        return response()->json([
            'success' => true,
            'token'   => $wizard['token'],
            'step'    => 3,
            'message' => 'Facial verification saved.',
        ]);
    }

    public function saveStep3(Request $request, string $barangay)
    {
        $this->resolveBarangay($barangay);
        $wizard = $this->requireWizard();

        $request->validate([
            'document_type'      => ['nullable', 'in:school_id,barangay_clearance'],
            'school_id'          => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:10240'],
            'barangay_clearance' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:10240'],
        ]);

        $schoolId = $request->file('school_id');
        $clearance = $request->file('barangay_clearance');
        $documentType = $request->input('document_type');

        if ($schoolId && $clearance) {
            throw ValidationException::withMessages([
                'document_type' => ['You can only upload one supporting document at a time.'],
            ]);
        }

        if ($documentType === 'school_id' && $clearance) {
            throw ValidationException::withMessages([
                'document_type' => ['Selected School ID but a Barangay Clearance file was uploaded.'],
            ]);
        }

        if ($documentType === 'barangay_clearance' && $schoolId) {
            throw ValidationException::withMessages([
                'document_type' => ['Selected Barangay Clearance but a School ID file was uploaded.'],
            ]);
        }

        $files = [
            'school_id'          => $documentType === 'school_id' ? $schoolId : null,
            'barangay_clearance' => $documentType === 'barangay_clearance' ? $clearance : null,
        ];

        $hasUpload = collect($files)->contains(fn ($file) => $file instanceof \Illuminate\Http\UploadedFile);

        if ($hasUpload) {
            $wizard = $this->draftService->saveStep3($wizard, $files);
        } else {
            $wizard = $this->draftService->skipStep3($wizard);
        }

        return response()->json([
            'success' => true,
            'token'   => $wizard['token'],
            'step'    => 4,
            'message' => $hasUpload ? 'Documents saved.' : 'Continuing without documents.',
        ]);
    }

    public function sendVerification(Request $request, string $barangay)
    {
        $barangayRecord = $this->resolveBarangay($barangay);
        $wizard = $this->requireWizard();

        if (empty($wizard['step1_data']) || empty($wizard['step2_data'])) {
            throw ValidationException::withMessages([
                'step' => ['Complete Steps 1–2 before email verification.'],
            ]);
        }

        $email = strtolower(trim($wizard['email'] ?? $wizard['step1_data']['email'] ?? ''));

        if ($email === '') {
            throw ValidationException::withMessages([
                'email' => ['Email address is missing from your registration session.'],
            ]);
        }

        $this->draftService->assertEmailAvailable($email, $barangayRecord->id);

        $verificationUrl = URL::temporarySignedRoute(
            'kkprofiling.wizard.verify',
            now()->addHours(24),
            [
                'token' => $wizard['token'],
                'hash'  => sha1($email),
            ]
        );

        Notification::route('mail', $email)
            ->notify(new KabataanVerifyEmail($verificationUrl));

        $wizard = $this->draftService->markVerificationSent($wizard);

        return response()->json([
            'success'           => true,
            'email'             => $email,
            'verification_sent' => true,
            'message'           => 'Verification email sent. Please check your inbox.',
        ]);
    }

    public function resendVerification(Request $request, string $barangay)
    {
        return $this->sendVerification($request, $barangay);
    }

    public function verifyWizardEmail(Request $request, string $token, string $hash)
    {
        if (! URL::hasValidSignature($request)) {
            return redirect()->route('kkprofiling.signup')->withErrors([
                'verification' => 'The verification link is invalid or expired.',
            ]);
        }

        $wizard = $this->draftService->loadByToken($token);

        if (! $wizard) {
            return redirect()->route('kkprofiling.signup')->withErrors([
                'verification' => 'Registration session not found or expired.',
            ]);
        }

        $email = strtolower(trim($wizard['email'] ?? $wizard['step1_data']['email'] ?? ''));

        if ($email === '' || ! hash_equals($hash, sha1($email))) {
            return redirect()->route('kkprofiling.signup')->withErrors([
                'verification' => 'The verification link is invalid.',
            ]);
        }

        try {
            $this->draftService->assertEmailAvailable($email, (int) $wizard['barangay_id']);
        } catch (ValidationException $e) {
            $slug = $this->barangaySlugFromId((int) $wizard['barangay_id']);

            return redirect()
                ->route('kkprofiling', ['barangay' => $slug])
                ->withErrors($e->errors())
                ->with('kk_wizard_email_conflict', true);
        }

        $wizard = $this->draftService->markEmailVerified($wizard);
        $this->draftService->syncWizardSession($wizard);

        $slug = $this->barangaySlugFromId((int) $wizard['barangay_id']);

        return redirect()
            ->route('kkprofiling', ['barangay' => $slug])
            ->with('success', 'Email verified! Set your password to complete registration.');
    }

    public function finalize(Request $request, string $barangay)
    {
        $this->resolveBarangay($barangay);
        $wizard = $this->requireWizard();

        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
        ]);

        if (empty($wizard['email_verified_at'])) {
            throw ValidationException::withMessages([
                'email' => ['Please verify your email before completing registration.'],
            ]);
        }

        $this->draftService->commitWizard($wizard, $request->password);

        return response()->json([
            'success'  => true,
            'message'  => 'Registration completed! Please wait for verification/approval by SK officials before logging in.',
            'redirect' => '/youth/login',
        ]);
    }

    public function status(string $barangay)
    {
        $barangayRecord = $this->resolveBarangay($barangay);
        $wizard = $this->draftService->resolveWizard();

        if ($wizard && (int) ($wizard['barangay_id'] ?? 0) !== (int) $barangayRecord->id) {
            $wizard = null;
        }

        return response()->json([
            'draft' => $this->draftService->wizardStatusPayload($wizard),
        ]);
    }

    private function requireWizard(): array
    {
        $wizard = $this->draftService->resolveWizard();

        if (! $wizard) {
            throw ValidationException::withMessages([
                'draft' => ['Your registration session expired. Please start again from Step 1.'],
            ]);
        }

        return $wizard;
    }

    private function validateStep1(Request $request): array
    {
        $validated = $request->validate([
            'last_name'             => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z.\-]{3,50}$/'],
            'first_name'            => ['required', 'string', 'min:3', 'max:50', 'regex:/^(?!\s)[A-Za-z.\-\s]+$/'],
            'middle_name'           => ['nullable', 'string', 'max:50', 'regex:/^$|^[A-Za-z.\-]{3,50}$/'],
            'suffix'                => ['required', 'string', 'in:None,Jr.,Sr.,I,II,III,IV,V,Others'],
            'custom_suffix'         => ['nullable', 'required_if:suffix,Others', 'string', 'max:30', 'regex:/^(?!\s+$)[A-Za-z.\s]+$/'],
            'purok_zone'            => ['required', 'string', 'max:100', 'regex:/^(?!\s).+/'],
            'sex'                   => 'required|in:Male,Female',
            'age'                   => 'required|integer|min:15|max:30',
            'birthday'              => 'required|date|before_or_equal:today',
            'email'                 => ['required', 'email', 'max:254', 'regex:/^[A-Za-z0-9._%+-]{6,30}@gmail\.com$/i'],
            'contact_number'        => ['required', 'string', 'regex:/^09\d{9}$/'],
            'civil_status'          => 'required|string',
            'youth_classification'  => 'required|string',
            'youth_age_group'       => 'required|string',
            'work_status'           => 'required|string',
            'education'             => 'required|string',
            'sk_voter'              => 'required|string',
            'national_voter'        => 'required|string',
            'sk_voted'              => 'required|string',
            'kk_assembly'           => 'required|string|in:Yes,No',
            'kk_times'              => 'required_if:kk_assembly,Yes|nullable|string',
            'kk_reason'             => 'required_if:kk_assembly,No|nullable|string',
            'facebook'              => ['required', 'string', 'min:3', 'max:35', 'regex:/^\S+$/'],
            'group_chat'            => 'required|string',
            'signature'             => 'required|string',
            'data_agreement'        => 'accepted',
        ]);

        if (($validated['suffix'] ?? null) === 'Others') {
            $customSuffix = trim((string) ($validated['custom_suffix'] ?? ''));
            $compact = strtoupper(str_replace(' ', '', $customSuffix));
            $validRoman = in_array($compact, ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'], true);
            $validText = (bool) preg_match('/^[A-Za-z.]+$/', str_replace(' ', '', $customSuffix));

            if (! $validRoman && ! $validText) {
                throw ValidationException::withMessages([
                    'custom_suffix' => ['Only text and valid Roman numeral suffixes are allowed.'],
                ]);
            }

            if ($validRoman && (strlen($compact) < 1 || strlen($compact) > 4)) {
                throw ValidationException::withMessages([
                    'custom_suffix' => ['Suffix must not exceed 4 characters.'],
                ]);
            }
        }

        try {
            $derivedAge = \Carbon\Carbon::parse($validated['birthday'])->age;
            if ($derivedAge < 15 || $derivedAge > 30 || (int) $validated['age'] !== (int) $derivedAge) {
                throw ValidationException::withMessages([
                    'birthday' => ['Birthday and age must match and be within 15 to 30 years old.'],
                ]);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'birthday' => ['Invalid birthday value.'],
            ]);
        }

        return $validated;
    }

    private function normalizeStep1Payload(Request $request, array $validated): array
    {
        $validated['civil_status']         = $request->input('civil_status', []);
        $validated['youth_classification'] = $request->input('youth_classification', []);
        $validated['youth_age_group']      = $request->input('youth_age_group', []);
        $validated['work_status']          = $request->input('work_status', []);
        $validated['education']            = $request->input('education', []);
        $validated['sk_voter']             = $request->input('sk_voter');
        $validated['national_voter']       = $request->input('national_voter');
        $validated['sk_voted']             = $request->input('sk_voted');
        $validated['kk_assembly']          = $request->input('kk_assembly');
        $validated['kk_times']           = $request->input('kk_assembly') === 'Yes' ? $request->input('kk_times') : null;
        $validated['kk_reason']            = $request->input('kk_assembly') === 'No' ? $request->input('kk_reason') : null;
        $validated['facebook']             = $request->input('facebook');
        $validated['group_chat']           = $request->input('group_chat');
        $validated['signature_name']       = $request->input('signature_name');

        return $validated;
    }

    private function resolveBarangay(string $barangay): Barangay
    {
        $slug = $this->normalizeSlug($barangay);
        $name = $this->getBarangayName($slug);

        if (! $name) {
            abort(404);
        }

        $record = Barangay::where('name', $name)->first();

        if (! $record) {
            abort(404);
        }

        return $record;
    }

    private function barangaySlugFromId(int $barangayId): string
    {
        $name = Barangay::whereKey($barangayId)->value('name');

        return $this->getBarangaySlug($name ?? '');
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
            'alipit'              => 'Alipit',
            'bagumbayan'          => 'Bagumbayan',
            'poblacion-i'         => 'Poblacion I',
            'poblacion-ii'        => 'Poblacion II',
            'poblacion-iii'       => 'Poblacion III',
            'poblacion-iv'        => 'Poblacion IV',
            'poblacion-v'         => 'Poblacion V',
            'bubukal'             => 'Bubukal',
            'calios'              => 'Calios',
            'duhat'               => 'Duhat',
            'gatid'               => 'Gatid',
            'jasaan'              => 'Jasaan',
            'labuin'              => 'Labuin',
            'malinao'             => 'Malinao',
            'oogong'              => 'Oogong',
            'pagsawitan'          => 'Pagsawitan',
            'palasan'             => 'Palasan',
            'patimbao'            => 'Patimbao',
            'san-jose'            => 'San Jose',
            'san-juan'            => 'San Juan',
            'san-pablo-norte'     => 'San Pablo Norte',
            'san-pablo-sur'       => 'San Pablo Sur',
            'santisima-cruz'      => 'Santisima Cruz',
            'santo-angel-central' => 'Santo Angel Central',
            'santo-angel-norte'   => 'Santo Angel Norte',
            'santo-angel-sur'     => 'Santo Angel Sur',
        ];

        return $barangayMap[$slug] ?? null;
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
}
