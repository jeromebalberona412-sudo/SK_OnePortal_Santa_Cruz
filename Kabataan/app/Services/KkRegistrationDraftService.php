<?php

namespace App\Services;

use App\Models\Barangay;
use App\Models\KabataanRegistration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Session + temp-file wizard storage. No database writes until Step 4 finalize.
 */
class KkRegistrationDraftService
{
    public const SESSION_KEY = 'kk_wizard';

    public const PENDING_DISK = 'local';

    public const PENDING_ROOT = 'kk_wizard_pending';

    public const TEMP_DISK = 'local';

    public const TEMP_ROOT = 'kk_wizard_pending';

    public const DOCUMENTS_DISK = 'public';

    public const DOCUMENTS_DIRECTORY = 'kabataan_documents';

    public function __construct(
        protected KabataanPhotoService $photoService
    ) {}

    public function resolveWizard(): ?array
    {
        $wizard = session(self::SESSION_KEY);

        if (! is_array($wizard) || empty($wizard['token'])) {
            return null;
        }

        if ($this->isExpired($wizard)) {
            $this->clearSessionDraft();

            return null;
        }

        return $wizard;
    }

    public function loadByToken(string $token): ?array
    {
        $path = $this->pendingFilePath($token);

        if (! Storage::disk(self::PENDING_DISK)->exists($path)) {
            return null;
        }

        $wizard = json_decode(Storage::disk(self::PENDING_DISK)->get($path), true);

        if (! is_array($wizard) || empty($wizard['token'])) {
            return null;
        }

        if ($this->isExpired($wizard)) {
            $this->deletePendingFiles($token);

            return null;
        }

        return $wizard;
    }

    public function clearSessionDraft(): void
    {
        $wizard = session(self::SESSION_KEY);
        $token = is_array($wizard) ? ($wizard['token'] ?? null) : null;

        if ($token) {
            $this->deletePendingFiles($token);
        }

        session()->forget([
            self::SESSION_KEY,
            'kk_wizard_step',
            'kk_wizard_email_verified',
            'kk_wizard_draft_id',
        ]);
    }

    public function syncWizardSession(array $wizard): void
    {
        $this->persist($wizard);
    }

    public function createOrUpdateStep1(Barangay $barangay, array $validated, ?string $respondentNumber = null): array
    {
        $wizard = $this->resolveWizard();

        if (! $wizard || (int) ($wizard['barangay_id'] ?? 0) !== (int) $barangay->id) {
            if ($wizard && ! empty($wizard['token'])) {
                $this->deletePendingFiles($wizard['token']);
            }

            $wizard = $this->blankWizard($barangay->id);
        }

        $wizard['respondent_number'] = $respondentNumber ?: ($wizard['respondent_number'] ?? null);
        $wizard['step1_data'] = $validated;
        $wizard['email'] = strtolower(trim($validated['email'] ?? ''));
        $wizard['current_step'] = max((int) ($wizard['current_step'] ?? 1), 2);
        $wizard['expires_at'] = now()->addDays(7)->toIso8601String();

        return $this->persist($wizard);
    }

    public function saveStep2(array $wizard, string $verifiedSelfie): array
    {
        if (empty($wizard['step1_data'])) {
            throw ValidationException::withMessages([
                'step' => ['Please complete Step 1 before facial verification.'],
            ]);
        }

        $binary = $this->photoService->resolveBinaryForDraft($verifiedSelfie);
        $this->photoService->assertValidImageBinaryForDraft($binary);

        $dir = $this->wizardDirectory($wizard['token']);
        $relativePath = $dir . '/verified_selfie.jpg';

        Storage::disk(self::TEMP_DISK)->put($relativePath, $binary);

        $wizard['step2_data'] = [
            'selfie_path'  => $relativePath,
            'completed_at' => now()->toIso8601String(),
        ];
        $wizard['current_step'] = max((int) ($wizard['current_step'] ?? 1), 3);
        $wizard['expires_at'] = now()->addDays(7)->toIso8601String();

        return $this->persist($wizard);
    }

    /**
     * @param  array<string, UploadedFile|null>  $files
     */
    public function saveStep3(array $wizard, array $files): array
    {
        if (empty($wizard['step2_data'])) {
            throw ValidationException::withMessages([
                'step' => ['Facial verification is required before uploading documents.'],
            ]);
        }

        $stored = $wizard['step3_data']['documents'] ?? [];
        $dir = $this->wizardDirectory($wizard['token']) . '/documents';

        foreach ($files as $key => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $filename = Str::slug($key, '_') . '_' . now()->format('YmdHis') . '_' . Str::lower(Str::random(6))
                . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs($dir, $filename, self::TEMP_DISK);

            $stored = [
                $key => [
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime'          => $file->getMimeType(),
                    'size'          => $file->getSize(),
                ],
            ];
        }

        $wizard['step3_data'] = ['documents' => $stored];
        $wizard['current_step'] = max((int) ($wizard['current_step'] ?? 1), 4);
        $wizard['expires_at'] = now()->addDays(7)->toIso8601String();

        return $this->persist($wizard);
    }

    public function skipStep3(array $wizard): array
    {
        if (empty($wizard['step2_data'])) {
            throw ValidationException::withMessages([
                'step' => ['Facial verification is required before continuing.'],
            ]);
        }

        $wizard['current_step'] = max((int) ($wizard['current_step'] ?? 1), 4);
        $wizard['expires_at'] = now()->addDays(7)->toIso8601String();

        return $this->persist($wizard);
    }

    public function markVerificationSent(array $wizard): array
    {
        $wizard['verification_sent_at'] = now()->toIso8601String();

        return $this->persist($wizard);
    }

    public function markEmailVerified(array $wizard): array
    {
        $wizard['email_verified_at'] = now()->toIso8601String();
        $wizard['current_step'] = max((int) ($wizard['current_step'] ?? 1), 4);

        return $this->persist($wizard);
    }

    public function assertEmailAvailable(string $email, int $barangayId): void
    {
        $email = strtolower(trim($email));

        $existingUser = User::where('email', $email)
            ->whereIn('status', ['ACTIVE', 'PENDING_APPROVAL', 'INACTIVE'])
            ->exists();

        $existingRegistration = KabataanRegistration::where('email', $email)
            ->where('barangay_id', $barangayId)
            ->whereIn('status', ['email_verified', 'password_set', 'active', 'pending_verification'])
            ->exists();

        if ($existingUser || $existingRegistration) {
            throw ValidationException::withMessages([
                'email' => ['This email is already registered. Please use a different Gmail address.'],
            ]);
        }
    }

    public function commitWizard(array $wizard, string $password): KabataanRegistration
    {
        if (empty($wizard['step1_data']) || empty($wizard['step2_data'])) {
            throw ValidationException::withMessages([
                'step' => ['Registration data is incomplete. Please restart the wizard.'],
            ]);
        }

        if (empty($wizard['email_verified_at'])) {
            throw ValidationException::withMessages([
                'email' => ['Please verify your email before completing registration.'],
            ]);
        }

        $barangay = Barangay::find($wizard['barangay_id'] ?? 0);

        if (! $barangay) {
            throw ValidationException::withMessages([
                'barangay' => ['Barangay not found. Please restart registration.'],
            ]);
        }

        $step1 = $wizard['step1_data'];
        $email = strtolower(trim($step1['email'] ?? $wizard['email'] ?? ''));

        $this->assertEmailAvailable($email, $barangay->id);

        return DB::transaction(function () use ($wizard, $barangay, $step1, $email, $password) {
            $this->photoService->ensureDirectoryExists();

            $selfiePath = $wizard['step2_data']['selfie_path'] ?? null;
            $selfieBinary = $selfiePath
                ? Storage::disk(self::TEMP_DISK)->get($selfiePath)
                : null;

            if (! $selfieBinary) {
                throw ValidationException::withMessages([
                    'verified_selfie' => ['Verified selfie is missing. Please redo facial verification.'],
                ]);
            }

            $photo = $this->photoService->storeVerifiedSelfieFromBinary($selfieBinary, $email);

            $formData = $this->buildFormData($step1, $wizard);
            $formData['supporting_documents'] = $this->promoteDocuments($wizard);

            $registration = KabataanRegistration::create([
                'tenant_id'                        => $barangay->tenant_id,
                'barangay_id'                      => $barangay->id,
                'last_name'                        => $step1['last_name'],
                'first_name'                       => $step1['first_name'],
                'middle_name'                      => $step1['middle_name'] ?? null,
                'suffix'                           => $this->resolvedSuffix($step1),
                'email'                            => $email,
                'contact_number'                   => $step1['contact_number'] ?? null,
                'profile_photo_path'               => $photo['path'],
                'facial_verification_completed_at' => now(),
                'form_data'                        => $formData,
                'status'                           => 'password_set',
                'email_verified_at'                => $wizard['email_verified_at'] ?? now(),
                'submitted_at'                     => now(),
            ]);

            try {
                (new RespondentNumberService())->assignToRegistration($registration->fresh());
            } catch (\Throwable $e) {
                report($e);
            }

            $user = User::create([
                'name'                      => $registration->full_name,
                'email'                     => $email,
                'password'                  => bcrypt($password),
                'email_verified_at'         => now(),
                'tenant_id'                 => $registration->tenant_id,
                'barangay_id'               => $registration->barangay_id,
                'role'                      => 'kabataan',
                'status'                    => 'PENDING_APPROVAL',
                'profile_image_url'         => $this->photoService->publicUrl($registration->profile_photo_path),
                'profile_image_uploaded_at' => $registration->facial_verification_completed_at ?? now(),
            ]);

            $registration->markPasswordSet();
            $registration->markActive($user->id);

            $evaluator = new RegistrationEvaluationService();
            $autoApproved = $evaluator->evaluate($registration->fresh());

            try {
                (new KkSurveyResponseService())->syncFromRegistration(
                    $registration->fresh(),
                    $autoApproved ? 'approved' : 'pending'
                );
            } catch (\Throwable $e) {
                report($e);
            }

            $this->deletePendingFiles($wizard['token']);
            $this->clearSessionDraft();

            return $registration->fresh();
        });
    }

    public function wizardStatusPayload(?array $wizard): ?array
    {
        if (! $wizard) {
            return null;
        }

        return [
            'token'               => $wizard['token'],
            'current_step'        => (int) ($wizard['current_step'] ?? 1),
            'email'               => $wizard['email'] ?? null,
            'email_verified'      => ! empty($wizard['email_verified_at']),
            'verification_sent'   => ! empty($wizard['verification_sent_at']),
            'has_step1'           => ! empty($wizard['step1_data']),
            'has_step2'           => ! empty($wizard['step2_data']),
            'has_documents'       => ! empty($wizard['step3_data']['documents']),
        ];
    }

    private function blankWizard(int $barangayId): array
    {
        return [
            'token'                 => (string) Str::uuid(),
            'barangay_id'           => $barangayId,
            'respondent_number'     => null,
            'step1_data'            => null,
            'step2_data'            => null,
            'step3_data'            => null,
            'email'                 => null,
            'email_verified_at'     => null,
            'verification_sent_at'  => null,
            'current_step'          => 1,
            'expires_at'            => now()->addDays(7)->toIso8601String(),
        ];
    }

    private function persist(array $wizard): array
    {
        session([self::SESSION_KEY => $wizard]);

        if (! empty($wizard['token'])) {
            Storage::disk(self::PENDING_DISK)->put(
                $this->pendingFilePath($wizard['token']),
                json_encode($wizard)
            );
        }

        session([
            'kk_wizard_step'           => max(1, min(4, (int) ($wizard['current_step'] ?? 1))),
            'kk_wizard_email_verified' => ! empty($wizard['email_verified_at']),
        ]);

        return $wizard;
    }

    private function isExpired(array $wizard): bool
    {
        if (empty($wizard['expires_at'])) {
            return false;
        }

        try {
            return now()->greaterThan(\Carbon\Carbon::parse($wizard['expires_at']));
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function deletePendingFiles(string $token): void
    {
        $dir = self::TEMP_ROOT . '/' . $token;

        if (Storage::disk(self::TEMP_DISK)->exists($dir)) {
            Storage::disk(self::TEMP_DISK)->deleteDirectory($dir);
        }

        $file = $this->pendingFilePath($token);

        if (Storage::disk(self::PENDING_DISK)->exists($file)) {
            Storage::disk(self::PENDING_DISK)->delete($file);
        }
    }

    private function pendingFilePath(string $token): string
    {
        return self::PENDING_ROOT . '/' . $token . '.json';
    }

    private function wizardDirectory(string $token): string
    {
        return self::TEMP_ROOT . '/' . $token;
    }

    private function buildFormData(array $step1, array $wizard): array
    {
        $data = $step1;

        if (! empty($wizard['respondent_number'])) {
            $data['respondent_number'] = $wizard['respondent_number'];
        }

        if (($data['suffix'] ?? null) === 'Others' && ! empty($data['custom_suffix'])) {
            $data['suffix'] = trim($data['custom_suffix']);
        }

        unset($data['custom_suffix'], $data['verified_selfie'], $data['facial_verification_completed'], $data['data_agreement']);

        return $data;
    }

    private function resolvedSuffix(array $step1): ?string
    {
        $suffix = $step1['suffix'] ?? null;

        if ($suffix === 'Others') {
            return trim($step1['custom_suffix'] ?? '') ?: null;
        }

        return $suffix;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function promoteDocuments(array $wizard): array
    {
        $documents = $wizard['step3_data']['documents'] ?? [];

        if ($documents === []) {
            return [];
        }

        if (! Storage::disk(self::DOCUMENTS_DISK)->exists(self::DOCUMENTS_DIRECTORY)) {
            Storage::disk(self::DOCUMENTS_DISK)->makeDirectory(self::DOCUMENTS_DIRECTORY);
        }

        $promoted = [];
        $token = $wizard['token'] ?? Str::random(8);

        foreach ($documents as $key => $meta) {
            $tempPath = $meta['path'] ?? null;

            if (! $tempPath || ! Storage::disk(self::TEMP_DISK)->exists($tempPath)) {
                continue;
            }

            $basename = basename($tempPath);
            $dest = self::DOCUMENTS_DIRECTORY . '/' . $token . '_' . $basename;

            Storage::disk(self::DOCUMENTS_DISK)->put(
                $dest,
                Storage::disk(self::TEMP_DISK)->get($tempPath)
            );

            $promoted[] = [
                'type'          => $key,
                'path'          => $dest,
                'url'           => Storage::disk(self::DOCUMENTS_DISK)->url($dest),
                'original_name' => $meta['original_name'] ?? $basename,
            ];
        }

        return $promoted;
    }
}
