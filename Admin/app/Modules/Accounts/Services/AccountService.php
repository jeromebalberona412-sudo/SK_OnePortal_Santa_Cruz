<?php

namespace App\Modules\Accounts\Services;

use App\Modules\Accounts\Models\Barangay;
use App\Modules\Accounts\Models\OfficialProfile;
use App\Modules\Accounts\Models\OfficialTerm;
use App\Modules\AuditLog\Contracts\AuditLogInterface;
use App\Modules\Shared\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountService
{
    public function __construct(private readonly AuditLogInterface $auditLog)
    {
    }

    public function createAccount(array $data, User $admin): User
    {
        $normalizedData = $this->withNormalizedMiddleName($data);

        return DB::transaction(function () use ($normalizedData, $admin) {
            $user = $this->createUser($normalizedData, $admin);
            $this->assignRole($user, $normalizedData['role']);
            $this->assignBarangay($user, (int) $normalizedData['barangay_id']);

            $profile = $this->createOfficialProfile($user, $normalizedData);
            $this->createTermRecord($profile, [
                'term_start' => $normalizedData['term_start'],
                'term_end' => $normalizedData['term_end'],
                'status' => $normalizedData['term_status'],
            ]);

            $this->logAuditAction(
                $admin,
                'account_created',
                'users',
                (string) $user->id,
                ['role' => $normalizedData['role'], 'email' => $user->email]
            );

            return $user;
        });
    }

    public function updateAccount(User $account, array $data, User $admin): User
    {
        $this->assertSameTenant($account->tenant_id, $admin->tenant_id, 'Target account is outside your tenant scope.');

        $normalizedData = $this->withNormalizedMiddleName($data);

        return DB::transaction(function () use ($account, $normalizedData, $admin) {
            $account->forceFill([
                'name' => $this->buildFullName($normalizedData),
                'email' => $normalizedData['email'],
                'status' => $normalizedData['status'],
            ])->save();

            $this->assignBarangay($account, (int) $normalizedData['barangay_id']);

            $profile = $account->officialProfile;
            if (! $profile) {
                $profile = $this->createOfficialProfile($account, $normalizedData);
            } else {
                $profile->update([
                    'first_name' => $normalizedData['first_name'],
                    'last_name' => $normalizedData['last_name'],
                    'middle_name' => $normalizedData['middle_name'] ?? null,
                    'suffix' => $normalizedData['suffix'] ?? null,
                    'date_of_birth' => $normalizedData['date_of_birth'] ?? null,
                    'age' => $this->deriveAge($normalizedData['date_of_birth'] ?? null),
                    'contact_number' => $normalizedData['contact_number'] ?? null,
                    'position' => $normalizedData['position'],
                    'municipality' => 'Santa Cruz',
                    'province' => 'Laguna',
                    'region' => 'IV-A CALABARZON',
                ]);
            }

            $latestTerm = $profile->terms()->latest('term_end')->first();
            $hasNewTermRange = ! $latestTerm
                || $latestTerm->term_start->toDateString() !== $normalizedData['term_start']
                || $latestTerm->term_end->toDateString() !== $normalizedData['term_end'];

            if ($hasNewTermRange) {
                if ($normalizedData['term_status'] === OfficialTerm::STATUS_ACTIVE) {
                    $profile->terms()
                        ->where('status', OfficialTerm::STATUS_ACTIVE)
                        ->update(['status' => OfficialTerm::STATUS_INACTIVE]);
                }

                $this->createTermRecord($profile, [
                    'term_start' => $normalizedData['term_start'],
                    'term_end' => $normalizedData['term_end'],
                    'status' => $normalizedData['term_status'],
                ]);
            } elseif ($latestTerm->status !== $normalizedData['term_status']) {
                if ($normalizedData['term_status'] === OfficialTerm::STATUS_ACTIVE) {
                    $profile->terms()
                        ->where('id', '!=', $latestTerm->id)
                        ->where('status', OfficialTerm::STATUS_ACTIVE)
                        ->update(['status' => OfficialTerm::STATUS_INACTIVE]);
                }

                $latestTerm->update([
                    'status' => $normalizedData['term_status'],
                ]);
            }

            $this->logAuditAction(
                $admin,
                'account_updated',
                'users',
                (string) $account->id,
                ['email' => $account->email]
            );

            return $account->fresh(['officialProfile.terms', 'barangay']);
        });
    }

    public function createUser(array $data, User $admin): User
    {
        $fullName = $this->buildFullName($data);

        return User::create([
            'tenant_id' => $admin->tenant_id,
            'name' => $fullName,
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_USER,
            'status' => $data['status'] ?? User::STATUS_PENDING_APPROVAL,
            'must_change_password' => true,
        ]);
    }

    public function assignRole(User $user, string $role): void
    {
        $user->forceFill(['role' => $role])->save();
    }

    public function createOfficialProfile(User $user, array $data): OfficialProfile
    {
        return OfficialProfile::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'suffix' => $data['suffix'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'age' => $this->deriveAge($data['date_of_birth'] ?? null),
            'contact_number' => $data['contact_number'] ?? null,
            'position' => $data['position'],
            'municipality' => 'Santa Cruz',
            'province' => 'Laguna',
            'region' => 'IV-A CALABARZON',
        ]);
    }

    public function createTermRecord(OfficialProfile $profile, array $data): OfficialTerm
    {
        return OfficialTerm::create([
            'official_profile_id' => $profile->id,
            'term_start' => $data['term_start'],
            'term_end' => $data['term_end'],
            'status' => $data['status'],
        ]);
    }

    public function assignBarangay(User $user, int $barangayId): void
    {
        $barangay = Barangay::query()
            ->where('id', $barangayId)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (! $barangay) {
            throw ValidationException::withMessages([
                'barangay_id' => 'Selected barangay does not belong to your tenant.',
            ]);
        }

        $user->forceFill([
            'barangay_id' => $barangayId,
        ])->save();
    }

    public function deactivate(User $target, User $admin): void
    {
        $this->assertSameTenant($target->tenant_id, $admin->tenant_id, 'Target account is outside your tenant scope.');

        if ($target->is($admin)) {
            throw ValidationException::withMessages([
                'account' => 'You cannot deactivate your own admin account.',
            ]);
        }

        $target->forceFill([
            'status' => User::STATUS_INACTIVE,
        ])->save();
        $target->delete();

        $this->logAuditAction(
            $admin,
            'account_deactivated',
            'users',
            (string) $target->id,
            ['email' => $target->email]
        );
    }

    public function resetPassword(User $target, string $newPassword, User $admin): void
    {
        $this->assertSameTenant($target->tenant_id, $admin->tenant_id, 'Target account is outside your tenant scope.');

        if ($target->is($admin)) {
            throw ValidationException::withMessages([
                'account' => 'You cannot reset your own password from this action.',
            ]);
        }

        $target->forceFill([
            'password' => $newPassword,
            'must_change_password' => true,
        ])->save();

        $this->logAuditAction(
            $admin,
            'password_reset',
            'users',
            (string) $target->id,
            ['email' => $target->email]
        );
    }

    public function extendTerm(OfficialProfile $profile, array $data, User $admin): OfficialTerm
    {
        $this->assertSameTenant($profile->tenant_id, $admin->tenant_id, 'Target profile is outside your tenant scope.');

        return DB::transaction(function () use ($profile, $data, $admin) {
            $profile->terms()
                ->where('status', OfficialTerm::STATUS_ACTIVE)
                ->update(['status' => OfficialTerm::STATUS_INACTIVE]);

            $term = $this->createTermRecord($profile, [
                'term_start' => $data['term_start'],
                'term_end' => $data['term_end'],
                'status' => $data['status'],
            ]);

            $this->logAuditAction(
                $admin,
                'term_extended',
                'official_profiles',
                (string) $profile->id,
                [
                    'user_id' => $profile->user_id,
                    'term_start' => $term->term_start->toDateString(),
                    'term_end' => $term->term_end->toDateString(),
                    'status' => $term->status,
                ]
            );

            return $term;
        });
    }

    public function logAuditAction(User $user, string $action, string $entityType, ?string $entityId = null, array $metadata = []): void
    {
        $this->auditLog->log('accounts.'.$action, $user, array_merge([
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ], $metadata));
    }

    protected function assertSameTenant(?int $targetTenantId, ?int $actorTenantId, string $message): void
    {
        if (! $targetTenantId || ! $actorTenantId || $targetTenantId !== $actorTenantId) {
            throw ValidationException::withMessages([
                'tenant' => $message,
            ]);
        }
    }

    protected function buildFullName(array $data): string
    {
        $middleInitial = $this->deriveMiddleInitial(
            $this->normalizeMiddleName($data['middle_name'] ?? null)
        );

        return trim(implode(' ', array_filter([
            $data['first_name'] ?? null,
            $middleInitial,
            $data['last_name'] ?? null,
            $data['suffix'] ?? null,
        ])));
    }

    protected function withNormalizedMiddleName(array $data): array
    {
        $middleName = $this->normalizeMiddleName($data['middle_name'] ?? ($data['middle_initial'] ?? null));
        $data['middle_name'] = $middleName;
        unset($data['middle_initial']);

        return $data;
    }

    protected function normalizeMiddleName(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $middleName = trim($value);

        return $middleName === '' ? null : $middleName;
    }

    protected function deriveMiddleInitial(?string $middleName): ?string
    {
        if ($middleName === null) {
            return null;
        }

        if (! preg_match('/[A-Za-z]/', $middleName, $matches)) {
            return null;
        }

        return strtoupper($matches[0]).'.';
    }

    protected function deriveAge(?string $dateOfBirth): ?int
    {
        if (! $dateOfBirth) {
            return null;
        }

        return Carbon::parse($dateOfBirth)->age;
    }
}
