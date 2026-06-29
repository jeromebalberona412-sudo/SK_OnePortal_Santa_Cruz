<?php

namespace App\Modules\KKProfiling\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\KabataanRegistration;
use App\Models\User;
use App\Notifications\KabataanSetPasswordEmail;
use App\Rules\FacebookProfileUrl;
use App\Services\BarangayZoneService;
use App\Services\DuplicateKabataanRegistrationService;
use App\Services\KkRegistrationDraftService;
use App\Services\RegistrationEvaluationService;
use App\Support\SupportingDocumentTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KKProfilingWizardController extends Controller
{
    public function __construct(
        protected KkRegistrationDraftService $draftService,
        protected BarangayZoneService $barangayZoneService,
        protected DuplicateKabataanRegistrationService $duplicateChecker,
    ) {}

    public function saveStep1(Request $request, string $barangay)
    {
        $barangayRecord = $this->resolveBarangay($barangay);
        $validated = $this->validateStep1($request, (int) $barangayRecord->id);
        $payload = $this->normalizeStep1Payload($request, $validated);

        $wizard = $this->draftService->createOrUpdateStep1(
            $barangayRecord,
            $payload,
            $request->input('respondent_number')
        );

        return response()->json([
            'success' => true,
            'token' => $wizard['token'],
            'step' => 2,
            'message' => 'Step 1 saved. Continue to supporting documents.',
        ]);
    }

    public function saveStep2(Request $request, string $barangay)
    {
        $barangayRecord = $this->resolveBarangay($barangay);
        $wizard = $this->requireWizard();

        if (empty($wizard['step1_data'])) {
            throw ValidationException::withMessages([
                'document_type' => ['Please complete Step 1 (KK Profiling Form) before continuing.'],
            ]);
        }

        $skipDocuments = $request->boolean('skip_documents');
        $allowedTypes = implode(',', SupportingDocumentTypes::allowed());
        $fileRule = ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:10240'];

        $request->validate([
            'skip_documents' => ['sometimes', 'boolean'],
            'document_type' => [$skipDocuments ? 'nullable' : 'sometimes', 'nullable', 'in:'.$allowedTypes],
            'school_id_front' => $fileRule,
            'school_id_back' => $fileRule,
            'national_id_front' => $fileRule,
            'national_id_back' => $fileRule,
        ]);

        $documentType = $request->input('document_type');
        $schoolFront = $request->file('school_id_front');
        $schoolBack = $request->file('school_id_back');
        $nationalFront = $request->file('national_id_front');
        $nationalBack = $request->file('national_id_back');

        $hasSchoolUpload = $schoolFront || $schoolBack;
        $hasNationalUpload = $nationalFront || $nationalBack;
        $hasAnyUpload = $hasSchoolUpload || $hasNationalUpload;

        if ($skipDocuments && $hasAnyUpload) {
            throw ValidationException::withMessages([
                'document_type' => ['Remove uploaded files or upload both sides instead of skipping this step.'],
            ]);
        }

        if (! $skipDocuments && $hasAnyUpload) {
            if ($hasSchoolUpload && $hasNationalUpload) {
                throw ValidationException::withMessages([
                    'document_type' => ['You can only upload one ID type at a time.'],
                ]);
            }

            if (! $documentType) {
                throw ValidationException::withMessages([
                    'document_type' => ['Please select School ID or PhilSys / National ID.'],
                ]);
            }

            if ($documentType === SupportingDocumentTypes::SCHOOL_ID && $hasNationalUpload) {
                throw ValidationException::withMessages([
                    'document_type' => ['Selected School ID but National ID files were uploaded.'],
                ]);
            }

            if ($documentType === SupportingDocumentTypes::NATIONAL_ID && $hasSchoolUpload) {
                throw ValidationException::withMessages([
                    'document_type' => ['Selected National ID but School ID files were uploaded.'],
                ]);
            }

            $sides = match ($documentType) {
                SupportingDocumentTypes::SCHOOL_ID => [
                    'front' => $schoolFront,
                    'back' => $schoolBack,
                ],
                SupportingDocumentTypes::NATIONAL_ID => [
                    'front' => $nationalFront,
                    'back' => $nationalBack,
                ],
                default => [],
            };

            $uploadedSides = collect($sides)->filter(fn ($file) => $file instanceof \Illuminate\Http\UploadedFile);

            if ($uploadedSides->count() > 0 && $uploadedSides->count() < 2) {
                throw ValidationException::withMessages([
                    'document_type' => ['Please upload both front and back images of your ID.'],
                ]);
            }

            if ($uploadedSides->count() === 2) {
                $wizard = $this->draftService->saveStep2($wizard, (string) $documentType, $sides);
            }
        }

        $wizard = $this->draftService->advanceToStep3($wizard);

        $email = strtolower(trim($wizard['email'] ?? $wizard['step1_data']['email'] ?? ''));
        $verificationSent = false;
        $emailError = null;

        try {
            $wizard = $this->dispatchWizardSetPasswordEmail($wizard, $barangayRecord);
            $verificationSent = true;
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $emailError = 'Unable to send set password email. Please tap Resend set password link.';
            Log::warning('KK wizard set-password email failed after step 2', [
                'email' => $email,
                'barangay_id' => $barangayRecord->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'token' => $wizard['token'],
            'step' => 3,
            'email' => $email,
            'message' => $hasAnyUpload
                ? 'Supporting documents saved. Continue to email verification.'
                : 'Continue to email verification. You may upload an ID later if needed.',
            'documents_uploaded' => $hasAnyUpload,
            'verification_sent' => $verificationSent,
            'email_error' => $emailError,
        ]);
    }

    public function sendVerification(Request $request, string $barangay)
    {
        $barangayRecord = $this->resolveBarangay($barangay);
        $wizard = $this->draftService->resolveWizard();

        if (! $wizard || empty($wizard['step1_data'])) {
            if ($completed = $this->completedRegistrationResponse($barangayRecord)) {
                return $completed;
            }

            throw ValidationException::withMessages([
                'draft' => ['Your registration session expired. Please start again from Step 1.'],
            ]);
        }

        if ((int) ($wizard['barangay_id'] ?? 0) !== (int) $barangayRecord->id) {
            throw ValidationException::withMessages([
                'draft' => ['Your registration session does not match this barangay. Please restart registration.'],
            ]);
        }

        if (empty($wizard['email_verified_at'])) {
            $this->draftService->clearCompletedRegistration();
        }

        $email = strtolower(trim($wizard['email'] ?? $wizard['step1_data']['email'] ?? ''));

        if ($email === '') {
            throw ValidationException::withMessages([
                'email' => ['Email address is missing from your registration session.'],
            ]);
        }

        try {
            $wizard = $this->dispatchWizardSetPasswordEmail($wizard, $barangayRecord);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            Log::warning('KK wizard set-password email failed on send-verification', [
                'email' => $email,
                'barangay_id' => $barangayRecord->id,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['Unable to send set password email right now. Please tap Resend set password link to try again.'],
            ]);
        }

        return response()->json([
            'success' => true,
            'email' => $email,
            'verification_sent' => true,
            'message' => 'Set password link sent. Please check your inbox.',
        ]);
    }

    public function resendVerification(Request $request, string $barangay)
    {
        return $this->sendVerification($request, $barangay);
    }

    public function openSetPasswordFromEmail(Request $request, string $token, string $hash)
    {
        $wizard = $this->draftService->loadByToken($token);

        if (! $wizard) {
            $completed = $this->draftService->resolveCompletedByWizardToken($token);
            $emailFromCache = strtolower(trim($completed['email'] ?? ''));

            if ($completed && $emailFromCache !== '' && hash_equals($hash, sha1($emailFromCache))) {
                $message = ($completed['auto_approved'] ?? false)
                    ? 'Your registration is verified. You can log in now.'
                    : 'Your registration has been submitted. Please wait for SK officials to verify your account before logging in.';

                return redirect()->route('login')->with('success', $message);
            }

            return redirect()->route('login')->with('success', 'Your registration has already been submitted. Please wait for SK officials to verify your account before logging in.');
        }

        $email = strtolower(trim($wizard['email'] ?? $wizard['step1_data']['email'] ?? ''));

        if ($email === '' || ! hash_equals($hash, sha1($email))) {
            return redirect()->route('kkprofiling.signup')->withErrors([
                'verification' => 'The verification link is invalid.',
            ]);
        }

        if ($this->draftService->isExpiredWizard($wizard)) {
            return redirect()->route('kkprofiling.signup')->withErrors([
                'verification' => 'This registration link has expired. Please start again.',
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

        $barangayRecord = Barangay::find((int) $wizard['barangay_id']);

        if (! $barangayRecord) {
            return redirect()->route('kkprofiling.signup')->withErrors([
                'verification' => 'Barangay not found for this registration.',
            ]);
        }

        return view('kkprofiling::set_password', [
            'wizardToken' => $token,
            'barangay' => $barangayRecord->name,
            'slug' => $this->barangaySlugFromId((int) $wizard['barangay_id']),
            'email' => $email,
            'emailVerified' => true,
            'barangayLogoUrl' => KKProfilingController::getBarangayLogoUrl($barangayRecord->id),
        ]);
    }

    public function finalizeByToken(Request $request, string $token)
    {
        set_time_limit((int) config('kkprofiling.finalize_time_limit', 180));

        $wizard = $this->draftService->loadByToken($token);

        if (! $wizard) {
            throw ValidationException::withMessages([
                'draft' => ['This registration link has expired or was already completed.'],
            ]);
        }

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
            $wizard = $this->draftService->markEmailVerified($wizard);
        }

        $registration = $this->draftService->commitWizard($wizard, $request->password);

        return $this->finalizeRegistrationResponse($registration);
    }

    public function checkRegistrationComplete(Request $request, string $barangay)
    {
        $barangayRecord = $this->resolveBarangay($barangay);

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($request->input('email', '')));

        $completed = KabataanRegistration::query()
            ->where('barangay_id', $barangayRecord->id)
            ->where('email', $email)
            ->whereIn('status', ['password_set', 'active'])
            ->first();

        if ($completed) {
            $registration = KabataanRegistration::query()
                ->where('barangay_id', $barangayRecord->id)
                ->where('email', $email)
                ->whereIn('status', ['password_set', 'active'])
                ->latest('id')
                ->first();

            $this->draftService->markRegistrationComplete($email, (int) $barangayRecord->id, $registration);
        }

        return response()->json([
            'completed' => $completed !== null,
            'auto_approved' => $completed
                ? RegistrationEvaluationService::isAutoApprovedStatus($completed->evaluation_status)
                : false,
            'evaluation_status' => $completed?->evaluation_status,
        ]);
    }

    public function finalize(Request $request, string $barangay)
    {
        set_time_limit((int) config('kkprofiling.finalize_time_limit', 180));

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

        $registration = $this->draftService->commitWizard($wizard, $request->password);

        $response = $this->finalizeRegistrationResponse($registration);
        $payload = $response->getData(true);
        $payload['redirect'] = '/youth/login';

        return response()->json($payload);
    }

    public function status(string $barangay)
    {
        $barangayRecord = $this->resolveBarangay($barangay);

        if ($completed = $this->draftService->resolveCompletedRegistration((int) $barangayRecord->id)) {
            $registration = KabataanRegistration::query()
                ->where('barangay_id', $barangayRecord->id)
                ->where('email', strtolower(trim($completed['email'] ?? '')))
                ->whereIn('status', ['password_set', 'active'])
                ->latest('id')
                ->first();

            $autoApproved = $registration
                ? RegistrationEvaluationService::isAutoApprovedStatus($registration->evaluation_status)
                : (bool) ($completed['auto_approved'] ?? false);

            if ($registration) {
                $this->draftService->markRegistrationComplete(
                    (string) $completed['email'],
                    (int) $barangayRecord->id,
                    $registration,
                );
            }

            return response()->json([
                'draft' => null,
                'registration_completed' => true,
                'email' => $completed['email'],
                'auto_approved' => $autoApproved,
                'evaluation_status' => $registration?->evaluation_status ?? ($completed['evaluation_status'] ?? null),
            ]);
        }

        $wizard = $this->draftService->resolveWizard();

        if ($wizard && (int) ($wizard['barangay_id'] ?? 0) !== (int) $barangayRecord->id) {
            $wizard = null;
        }

        if ($wizard) {
            $email = strtolower(trim($wizard['email'] ?? $wizard['step1_data']['email'] ?? ''));

            if ($email !== '' && $this->draftService->isEmailRegistrationComplete($email, (int) $barangayRecord->id)) {
                $registration = KabataanRegistration::query()
                    ->where('barangay_id', $barangayRecord->id)
                    ->where('email', $email)
                    ->whereIn('status', ['password_set', 'active'])
                    ->latest('id')
                    ->first();

                $this->draftService->markRegistrationComplete($email, (int) $barangayRecord->id, $registration);
                $completed = $this->draftService->resolveCompletedRegistration((int) $barangayRecord->id);

                return response()->json([
                    'draft' => null,
                    'registration_completed' => true,
                    'email' => $email,
                    'auto_approved' => (bool) ($completed['auto_approved'] ?? false),
                    'evaluation_status' => $completed['evaluation_status'] ?? null,
                ]);
            }
        }

        return response()->json([
            'draft' => $this->draftService->wizardStatusPayload($wizard),
        ]);
    }

    public function setStep(Request $request, string $barangay)
    {
        $barangayRecord = $this->resolveBarangay($barangay);
        $wizard = $this->requireWizard();

        if ((int) ($wizard['barangay_id'] ?? 0) !== (int) $barangayRecord->id) {
            throw ValidationException::withMessages([
                'draft' => ['Your registration session does not match this barangay.'],
            ]);
        }

        $validated = $request->validate([
            'step' => ['required', 'integer', 'min:1', 'max:3'],
        ]);

        $targetStep = (int) $validated['step'];

        if ($targetStep === 3 && empty($wizard['step1_data'])) {
            throw ValidationException::withMessages([
                'step' => ['Please complete Step 1 before continuing.'],
            ]);
        }

        if ($targetStep === 3 && empty($wizard['verification_sent_at'])) {
            throw ValidationException::withMessages([
                'step' => ['Please complete Step 2 before opening email verification.'],
            ]);
        }

        $wizard = $this->draftService->setWizardStep($wizard, $targetStep);

        return response()->json([
            'success' => true,
            'step' => $targetStep,
            'draft' => $this->draftService->wizardStatusPayload($wizard),
        ]);
    }

    public function documentPreview(string $barangay, string $type, ?string $side = 'front')
    {
        $barangayRecord = $this->resolveBarangay($barangay);
        $wizard = $this->requireWizard();

        if ((int) ($wizard['barangay_id'] ?? 0) !== (int) $barangayRecord->id) {
            abort(404);
        }

        if (! in_array($side, SupportingDocumentTypes::sides(), true)) {
            abort(404);
        }

        $document = $wizard['step2_data']['documents'][$type] ?? null;

        if (! is_array($document)) {
            abort(404);
        }

        $sides = is_array($document['sides'] ?? null) ? $document['sides'] : [];
        $meta = is_array($sides[$side] ?? null)
            ? $sides[$side]
            : ($side === 'front' && ! empty($document['path']) ? $document : null);

        if (! is_array($meta) || empty($meta['path'])) {
            abort(404);
        }

        $disk = Storage::disk(KkRegistrationDraftService::TEMP_DISK);

        if (! $disk->exists($meta['path'])) {
            abort(404);
        }

        return response()->file($disk->path($meta['path']), [
            'Content-Type' => $meta['mime'] ?? 'image/jpeg',
        ]);
    }

    private function completedRegistrationResponse(Barangay $barangayRecord): ?\Illuminate\Http\JsonResponse
    {
        $completed = $this->draftService->resolveCompletedRegistration((int) $barangayRecord->id);

        if (! $completed) {
            return null;
        }

        $autoApproved = (bool) ($completed['auto_approved'] ?? false);
        $registration = KabataanRegistration::query()
            ->where('barangay_id', $barangayRecord->id)
            ->where('email', strtolower(trim($completed['email'] ?? '')))
            ->whereIn('status', ['password_set', 'active'])
            ->latest('id')
            ->first();

        if ($registration) {
            $autoApproved = RegistrationEvaluationService::isAutoApprovedStatus($registration->evaluation_status);
        }

        return response()->json([
            'success' => true,
            'registration_completed' => true,
            'email' => $completed['email'],
            'auto_approved' => $autoApproved,
            'evaluation_status' => $registration?->evaluation_status ?? ($completed['evaluation_status'] ?? null),
            'message' => $autoApproved
                ? 'Registration verified! Your details match a previous KK profiling record. You can log in now.'
                : 'Your registration has been submitted. Please wait for SK officials to verify your account.',
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

    private function finalizeRegistrationResponse(KabataanRegistration $registration): \Illuminate\Http\JsonResponse
    {
        $registration = $registration->fresh();
        $autoApproved = RegistrationEvaluationService::isAutoApprovedStatus($registration->evaluation_status);

        if ($autoApproved && $registration->user_id) {
            User::query()
                ->where('id', $registration->user_id)
                ->where('status', User::STATUS_PENDING_APPROVAL)
                ->update(['status' => User::STATUS_ACTIVE]);
        }

        return response()->json([
            'success' => true,
            'auto_approved' => $autoApproved,
            'message' => $autoApproved
                ? 'Registration verified! Your details match a previous KK profiling record. You can log in now.'
                : 'Registration completed! Please wait for verification/approval by SK Officials before logging in.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function dispatchWizardSetPasswordEmail(array $wizard, Barangay $barangayRecord): array
    {
        $email = strtolower(trim($wizard['email'] ?? $wizard['step1_data']['email'] ?? ''));

        if ($email === '') {
            throw ValidationException::withMessages([
                'email' => ['Email address is missing from your registration session.'],
            ]);
        }

        $this->draftService->assertEmailAvailable($email, (int) $barangayRecord->id);

        $setPasswordUrl = route('kkprofiling.wizard.set-password', [
            'token' => $wizard['token'],
            'hash' => sha1($email),
        ]);

        Log::info('Sending KK wizard set-password email', [
            'email' => $email,
            'barangay_id' => $barangayRecord->id,
            'token' => $wizard['token'] ?? null,
        ]);

        Notification::route('mail', $email)
            ->notify(new KabataanSetPasswordEmail($setPasswordUrl));

        return $this->draftService->markVerificationSent($wizard);
    }

    private function validateStep1(Request $request, int $barangayId): array
    {
        $validated = $request->validate([
            'last_name' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z.\-]{3,50}$/'],
            'first_name' => ['required', 'string', 'min:3', 'max:50', 'regex:/^(?!\s)[A-Za-z.\-\s]+$/'],
            'middle_name' => ['nullable', 'string', 'max:50', 'regex:/^$|^[A-Za-z.\-]{3,50}$/'],
            'suffix' => ['required', 'string', 'in:None,Jr.,Sr.,I,II,III,IV,V,Others'],
            'custom_suffix' => ['nullable', 'required_if:suffix,Others', 'string', 'max:5', 'regex:/^(?!\s+$)[A-Za-z.\s]+$/'],
            'purok_zone' => $this->barangayZoneService->purokZoneRules($barangayId),
            'sex' => 'required|in:Male,Female',
            'age' => 'required|integer|min:15|max:30',
            'birthday' => 'required|date|before_or_equal:today',
            'email' => ['required', 'email', 'max:254', 'regex:/^[A-Za-z0-9._%+-]{6,30}@gmail\.com$/i'],
            'contact_number' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'civil_status' => 'required|string',
            'youth_classification' => 'required|string',
            'youth_age_group' => 'required|string',
            'work_status' => 'required|string',
            'education' => 'required|string',
            'sk_voter' => 'required|string',
            'national_voter' => 'required|string',
            'sk_voted' => 'required|string',
            'kk_assembly' => 'required|string|in:Yes,No',
            'kk_times' => 'required_if:kk_assembly,Yes|nullable|string',
            'kk_reason' => 'required_if:kk_assembly,No|nullable|string',
            'facebook_profile_url' => [
                'nullable',
                Rule::requiredIf(fn () => in_array((string) $request->input('group_chat'), ['Yes', 'No'], true)),
                'string',
                'min:3',
                'max:50',
                new FacebookProfileUrl,
            ],
            'group_chat' => [
                'nullable',
                Rule::requiredIf(fn () => trim((string) $request->input('facebook_profile_url', '')) !== ''),
                'string',
                Rule::in(['Yes', 'No']),
            ],
            'signature' => 'required|string',
            'data_agreement' => 'accepted',
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

            if ($validRoman && (strlen($compact) < 1 || strlen($compact) > 5)) {
                throw ValidationException::withMessages([
                    'custom_suffix' => ['Suffix must not exceed 5 characters.'],
                ]);
            }

            if (! $validRoman && strlen(str_replace(' ', '', $customSuffix)) > 5) {
                throw ValidationException::withMessages([
                    'custom_suffix' => ['Suffix must not exceed 5 characters.'],
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
        $validated['civil_status'] = $request->input('civil_status', []);
        $validated['youth_classification'] = $request->input('youth_classification', []);
        $validated['youth_age_group'] = $request->input('youth_age_group', []);
        $validated['work_status'] = $request->input('work_status', []);
        $validated['education'] = $request->input('education', []);
        $validated['sk_voter'] = $request->input('sk_voter');
        $validated['national_voter'] = $request->input('national_voter');
        $validated['sk_voted'] = $request->input('sk_voted');
        $validated['kk_assembly'] = $request->input('kk_assembly');
        $validated['kk_times'] = $request->input('kk_assembly') === 'Yes' ? $request->input('kk_times') : null;
        $validated['kk_reason'] = $request->input('kk_assembly') === 'No' ? $request->input('kk_reason') : null;
        $validated['facebook_profile_url'] = trim((string) $request->input('facebook_profile_url', '')) ?: null;
        $validated['group_chat'] = $request->input('group_chat');
        $validated['signature_name'] = $request->input('signature_name');

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
            'alipit' => 'Alipit',
            'bagumbayan' => 'Bagumbayan',
            'poblacion-i' => 'Poblacion I',
            'poblacion-ii' => 'Poblacion II',
            'poblacion-iii' => 'Poblacion III',
            'poblacion-iv' => 'Poblacion IV',
            'poblacion-v' => 'Poblacion V',
            'bubukal' => 'Bubukal',
            'calios' => 'Calios',
            'duhat' => 'Duhat',
            'gatid' => 'Gatid',
            'jasaan' => 'Jasaan',
            'labuin' => 'Labuin',
            'malinao' => 'Malinao',
            'oogong' => 'Oogong',
            'pagsawitan' => 'Pagsawitan',
            'palasan' => 'Palasan',
            'patimbao' => 'Patimbao',
            'san-jose' => 'San Jose',
            'san-juan' => 'San Juan',
            'san-pablo-norte' => 'San Pablo Norte',
            'san-pablo-sur' => 'San Pablo Sur',
            'santisima-cruz' => 'Santisima Cruz',
            'santo-angel-central' => 'Santo Angel Central',
            'santo-angel-norte' => 'Santo Angel Norte',
            'santo-angel-sur' => 'Santo Angel Sur',
        ];

        return $barangayMap[$slug] ?? null;
    }

    private function getBarangaySlug(string $name): string
    {
        $slugMap = [
            'Alipit' => 'alipit',
            'Bagumbayan' => 'bagumbayan',
            'Poblacion I' => 'poblacion-i',
            'Poblacion II' => 'poblacion-ii',
            'Poblacion III' => 'poblacion-iii',
            'Poblacion IV' => 'poblacion-iv',
            'Poblacion V' => 'poblacion-v',
            'Bubukal' => 'bubukal',
            'Calios' => 'calios',
            'Duhat' => 'duhat',
            'Gatid' => 'gatid',
            'Jasaan' => 'jasaan',
            'Labuin' => 'labuin',
            'Malinao' => 'malinao',
            'Oogong' => 'oogong',
            'Pagsawitan' => 'pagsawitan',
            'Palasan' => 'palasan',
            'Patimbao' => 'patimbao',
            'San Jose' => 'san-jose',
            'San Juan' => 'san-juan',
            'San Pablo Norte' => 'san-pablo-norte',
            'San Pablo Sur' => 'san-pablo-sur',
            'Santisima Cruz' => 'santisima-cruz',
            'Santo Angel Central' => 'santo-angel-central',
            'Santo Angel Norte' => 'santo-angel-norte',
            'Santo Angel Sur' => 'santo-angel-sur',
        ];

        return $slugMap[$name] ?? strtolower(str_replace(' ', '-', $name));
    }
}
