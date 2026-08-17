<?php

namespace App\Services;

use App\Models\KabataanRegistration;
use App\Models\User;
use App\Modules\KKProfilingRequests\Notifications\KabataanAccountInviteNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class KkProfilingOfficialUpdateService
{
    public const EMAIL_REGEX = '/^[A-Za-z0-9._%+-]{6,30}@gmail\.com$/i';

    public function __construct(
        private readonly SkOfficialActivityService $activityService,
        private readonly KkSurveyResponseService $surveyService,
        private readonly BarangayZoneService $zoneService,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array{registration: KabataanRegistration, invite_sent: bool, invite_error: ?string}
     */
    public function update(User $official, KabataanRegistration $registration, array $input): array
    {
        if ((int) $official->barangay_id !== (int) $registration->barangay_id) {
            throw ValidationException::withMessages([
                'barangay' => ['You can only edit KK Profiling records in your barangay.'],
            ]);
        }

        $previousEmail = strtolower(trim((string) $registration->email));
        $newEmail = strtolower(trim((string) ($input['email'] ?? '')));
        $newEmail = $newEmail === '' ? null : $newEmail;

        if ($registration->user_id && $newEmail === null) {
            throw ValidationException::withMessages([
                'email' => ['This kabataan already has an account. Email cannot be removed.'],
            ]);
        }

        $this->assertEmailAvailable($newEmail, $registration);

        $formData = is_array($registration->form_data) ? $registration->form_data : [];
        $formData = $this->mergeFormData($formData, $input, $registration);

        $shouldInvite = $newEmail !== null;
        $plainToken = null;

        $updated = DB::transaction(function () use ($registration, $input, $formData, $newEmail, $previousEmail, $shouldInvite, &$plainToken) {
            if ($shouldInvite) {
                $plainToken = bin2hex(random_bytes(32));
                $formData['account_invite_token_hash'] = hash('sha256', $plainToken);
                $formData['account_invite_expires_at'] = now()->addHours(24)->toIso8601String();
                unset($formData['account_invite_used_at'], $formData['account_invite_sent_at']);
            }

            $formData['email'] = $newEmail;

            $registration->update([
                'last_name' => $input['last_name'],
                'first_name' => $input['first_name'],
                'middle_name' => $input['middle_name'] ?? $registration->middle_name,
                'suffix' => $input['suffix'] ?? $registration->suffix,
                'email' => $newEmail,
                'contact_number' => $input['contact_number'] ?? $registration->contact_number,
                'form_data' => $formData,
            ]);

            if ($registration->user_id && $newEmail !== null && $newEmail !== $previousEmail) {
                User::query()
                    ->where('id', $registration->user_id)
                    ->update([
                        'email' => $newEmail,
                        'email_verified_at' => now(),
                    ]);
            }

            $fresh = $registration->fresh();
            $this->surveyService->syncFromRegistration(
                $fresh,
                $fresh->evaluation_status === 'active' ? 'approved' : 'pending'
            );

            return $fresh;
        });

        $inviteSent = false;
        $inviteError = null;

        if (is_string($plainToken) && $plainToken !== '') {
            $inviteError = $this->deliverMail(
                $updated,
                $this->activationUrl($updated, $plainToken),
            );
            if ($inviteError === null) {
                $inviteSent = true;
                $sentData = is_array($updated->form_data) ? $updated->form_data : [];
                $sentData['account_invite_sent_at'] = now()->toIso8601String();
                $updated->update(['form_data' => $sentData]);
            }
        }

        $this->activityService->log(
            $official,
            'kk.edit',
            'Updated KK profiling record: '.$updated->full_name,
            [
                'registration_id' => $updated->id,
                'email_added' => $shouldInvite,
                'invite_sent' => $inviteSent,
            ]
        );

        return [
            'registration' => $updated,
            'invite_sent' => $inviteSent,
            'invite_error' => $inviteError,
        ];
    }

    private function kabataanBaseUrl(): string
    {
        $base = rtrim((string) config('services.kabataan_app_url'), '/');
        if ($base === '') {
            throw new \RuntimeException('Kabataan app URL is not configured.');
        }

        return $base;
    }

    private function activationUrl(KabataanRegistration $registration, string $plainToken): string
    {
        return $this->kabataanBaseUrl().'/kkprofiling/account-invite/'.$registration->id.'/'.$plainToken;
    }

    private function deliverMail(KabataanRegistration $registration, string $actionUrl): ?string
    {
        $email = strtolower(trim((string) $registration->email));
        if ($email === '') {
            return 'The profile was saved, but there is no email address to send to.';
        }

        try {
            Notification::route('mail', $email)
                ->notify(new KabataanAccountInviteNotification(
                    $registration->full_name,
                    $actionUrl,
                ));

            return null;
        } catch (\Throwable $e) {
            report($e);
            Log::error('KK Profiling account email failed', [
                'registration_id' => $registration->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return 'The profile was saved, but the email could not be sent. Please try saving again.';
        }
    }

    private function assertEmailAvailable(?string $email, KabataanRegistration $registration): void
    {
        if ($email === null) {
            return;
        }

        if (! preg_match(self::EMAIL_REGEX, $email)) {
            throw ValidationException::withMessages([
                'email' => ['Use a valid Gmail address (6-30 character username).'],
            ]);
        }

        $takenByUser = User::query()
            ->where('email', $email)
            ->whereIn('status', ['ACTIVE', 'PENDING_APPROVAL', 'INACTIVE'])
            ->when($registration->user_id, fn ($q) => $q->where('id', '!=', $registration->user_id))
            ->exists();

        $takenByProfile = KabataanRegistration::query()
            ->where('email', $email)
            ->where('id', '!=', $registration->id)
            ->whereNotIn('status', ['rejected'])
            ->exists();

        if ($takenByUser || $takenByProfile) {
            throw ValidationException::withMessages([
                'email' => ['This email is already assigned to another account or KK Profiling record.'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $existing
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function mergeFormData(array $existing, array $input, KabataanRegistration $registration): array
    {
        $age = isset($input['age']) ? (int) $input['age'] : (int) ($existing['age'] ?? 0);
        if (! empty($input['birthday'])) {
            $input['birthday'] = $this->normalizeBirthday((string) $input['birthday']);
        }
        $merged = array_merge($existing, array_filter([
            'age' => $input['age'] ?? null,
            'sex' => $input['sex'] ?? null,
            'birthday' => $input['birthday'] ?? null,
            'purok_zone' => $input['purok_zone'] ?? null,
            'civil_status' => $input['civil_status'] ?? null,
            'youth_classification' => $input['youth_classification'] ?? null,
            'youth_age_group' => $this->youthAgeGroupFromAge($age) ?: ($input['youth_age_group'] ?? null),
            'work_status' => $input['work_status'] ?? null,
            'education' => $input['education'] ?? $input['educational_background'] ?? null,
            'sk_voter' => $input['sk_voter'] ?? null,
            'national_voter' => $input['national_voter'] ?? null,
            'sk_voted' => $input['sk_voted'] ?? null,
            'kk_assembly' => $input['kk_assembly'] ?? null,
            'kk_times' => ($input['kk_assembly'] ?? null) === 'Yes' ? ($input['kk_times'] ?? null) : null,
            'kk_reason' => ($input['kk_assembly'] ?? null) === 'No' ? ($input['kk_reason'] ?? null) : null,
            'facebook_profile_url' => $input['facebook_profile_url'] ?? $input['facebook'] ?? null,
            'email' => strtolower(trim((string) ($input['email'] ?? ''))) ?: null,
            'last_name' => $input['last_name'] ?? null,
            'first_name' => $input['first_name'] ?? null,
            'middle_name' => $input['middle_name'] ?? null,
            'suffix' => $input['suffix'] ?? null,
            'custom_suffix' => $input['custom_suffix'] ?? null,
            'contact_number' => $input['contact_number'] ?? null,
        ], fn ($value) => $value !== null && $value !== ''));

        $groupChat = trim((string) ($input['group_chat'] ?? ''));
        $merged['group_chat'] = in_array($groupChat, ['Yes', 'No'], true) ? $groupChat : null;

        $zones = $this->zoneService->activeZoneNames((int) $registration->barangay_id);
        if ($zones !== [] && ! empty($merged['purok_zone']) && ! in_array($merged['purok_zone'], $zones, true)) {
            throw ValidationException::withMessages([
                'purok_zone' => ['Select a valid purok, sitio, or zone for this barangay.'],
            ]);
        }

        return $merged;
    }

    private function normalizeBirthday(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return $value;
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            return $value;
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
            return sprintf('%04d-%02d-%02d', (int) $matches[3], (int) $matches[1], (int) $matches[2]);
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return $value;
        }
    }

    private function youthAgeGroupFromAge(int $age): string
    {
        if ($age >= 15 && $age <= 17) {
            return 'Child Youth (15-17 yrs old)';
        }
        if ($age >= 18 && $age <= 24) {
            return 'Core Youth (18-24 yrs old)';
        }
        if ($age >= 25 && $age <= 30) {
            return 'Young Adult (15-30 yrs old)';
        }

        return '';
    }
}
