<?php

namespace App\Services;

use App\Models\KabataanRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KkProfilingAccountInviteService
{
    public const TOKEN_TTL_HOURS = 24;

    public const FORM_TOKEN_KEY = 'account_invite_token_hash';

    public const FORM_EXPIRES_KEY = 'account_invite_expires_at';

    public const FORM_USED_KEY = 'account_invite_used_at';

    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    public function findValidRegistration(int $registrationId, string $plainToken): KabataanRegistration
    {
        $registration = KabataanRegistration::query()->find($registrationId);

        if (! $registration) {
            throw ValidationException::withMessages([
                'token' => ['This activation link is invalid.'],
            ]);
        }

        $formData = is_array($registration->form_data) ? $registration->form_data : [];
        $storedHash = (string) ($formData[self::FORM_TOKEN_KEY] ?? '');
        $expiresAt = (string) ($formData[self::FORM_EXPIRES_KEY] ?? '');
        $usedAt = $formData[self::FORM_USED_KEY] ?? null;

        if ($storedHash === '' || ! hash_equals($storedHash, self::hashToken($plainToken))) {
            throw ValidationException::withMessages([
                'token' => ['This activation link is invalid or has already been used.'],
            ]);
        }

        if ($usedAt) {
            throw ValidationException::withMessages([
                'token' => ['This activation link has already been used. Please contact your SK Officials if you still need access.'],
            ]);
        }

        if ($expiresAt === '' || now()->greaterThan($expiresAt)) {
            throw ValidationException::withMessages([
                'token' => ['This activation link has expired. Please contact your SK Officials to send a new one.'],
            ]);
        }

        if ($registration->user_id) {
            throw ValidationException::withMessages([
                'token' => ['This KK Profiling record already has an account. You can sign in instead.'],
            ]);
        }

        $email = strtolower(trim((string) $registration->email));

        if ($email === '') {
            throw ValidationException::withMessages([
                'token' => ['This KK Profiling record does not have an email address yet.'],
            ]);
        }

        return $registration;
    }

    public function activate(KabataanRegistration $registration, string $plainToken, string $password): User
    {
        $this->findValidRegistration((int) $registration->id, $plainToken);

        $email = strtolower(trim((string) $registration->email));

        return DB::transaction(function () use ($registration, $email, $password) {
            $registration = KabataanRegistration::query()
                ->where('id', $registration->id)
                ->lockForUpdate()
                ->firstOrFail();

            $formData = is_array($registration->form_data) ? $registration->form_data : [];

            if (! empty($formData[self::FORM_USED_KEY]) || $registration->user_id) {
                throw ValidationException::withMessages([
                    'token' => ['This activation link has already been used.'],
                ]);
            }

            $existingUser = User::query()->where('email', $email)->first();

            if ($existingUser) {
                throw ValidationException::withMessages([
                    'email' => ['This email is already registered to another account.'],
                ]);
            }

            $alreadyApproved = in_array($registration->evaluation_status, ['active', 'Auto Approved', 'ID Verified'], true);

            $user = User::create([
                'name' => $registration->full_name,
                'email' => $email,
                'password' => bcrypt($password),
                'email_verified_at' => now(),
                'tenant_id' => $registration->tenant_id,
                'barangay_id' => $registration->barangay_id,
                'role' => User::ROLE_KABATAAN,
                'status' => $alreadyApproved ? User::STATUS_ACTIVE : User::STATUS_PENDING_APPROVAL,
            ]);

            $formData[self::FORM_USED_KEY] = now()->toIso8601String();
            unset($formData[self::FORM_TOKEN_KEY], $formData[self::FORM_EXPIRES_KEY]);

            $registration->update([
                'user_id' => $user->id,
                'email' => $email,
                'email_verified_at' => now(),
                'password_set_at' => now(),
                'status' => $alreadyApproved ? 'active' : 'password_set',
                'form_data' => $formData,
            ]);

            try {
                (new KkSurveyResponseService)->syncFromRegistration($registration->fresh(), $alreadyApproved ? 'approved' : 'pending');
            } catch (\Throwable $e) {
                report($e);
            }

            return $user;
        });
    }
}
