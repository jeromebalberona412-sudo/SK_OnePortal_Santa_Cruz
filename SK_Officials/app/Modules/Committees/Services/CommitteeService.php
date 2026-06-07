<?php

namespace App\Modules\Committees\Services;

use App\Models\Committee;
use App\Models\OfficialProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CommitteeService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listForBarangay(User $user): Collection
    {
        return $this->barangayCommitteesQuery($user)
            ->with(['head.officialProfile'])
            ->orderBy('committee_name')
            ->get()
            ->map(fn (Committee $committee) => $this->formatCommittee($committee));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listSkOfficials(User $user): Collection
    {
        return User::query()
            ->with('officialProfile')
            ->where('barangay_id', $user->barangay_id)
            ->where('role', User::ROLE_SK_OFFICIAL)
            ->where('status', User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get()
            ->map(fn (User $official) => [
                'id' => $official->id,
                'full_name' => $this->buildOfficialFullName($official),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(User $user, array $data): array
    {
        $this->assertHeadBelongsToBarangay($user, (int) $data['committee_head_id']);
        $this->assertUniqueHead((int) $data['committee_head_id']);
        $this->assertUniqueCommitteeName($user, (string) $data['committee_name']);

        $committee = Committee::create([
            'committee_name' => trim((string) $data['committee_name']),
            'committee_head_id' => (int) $data['committee_head_id'],
            'description' => isset($data['description']) ? trim((string) $data['description']) : null,
        ]);

        return $this->formatCommittee($committee->load(['head.officialProfile']));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $user, Committee $committee, array $data): array
    {
        $this->assertCommitteeInBarangay($user, $committee);
        $this->assertHeadBelongsToBarangay($user, (int) $data['committee_head_id']);
        $this->assertUniqueHead((int) $data['committee_head_id'], $committee->id);
        $this->assertUniqueCommitteeName($user, (string) $data['committee_name'], $committee->id);

        $committee->update([
            'committee_name' => trim((string) $data['committee_name']),
            'committee_head_id' => (int) $data['committee_head_id'],
            'description' => isset($data['description']) ? trim((string) $data['description']) : null,
        ]);

        return $this->formatCommittee($committee->fresh(['head.officialProfile']));
    }

    public function findCommitteeForBarangay(User $user, int $committeeId): Committee
    {
        $committee = $this->barangayCommitteesQuery($user)
            ->with(['head.officialProfile'])
            ->where('committees.id', $committeeId)
            ->first();

        if ($committee === null) {
            throw ValidationException::withMessages([
                'committee' => ['Committee not found.'],
            ]);
        }

        return $committee;
    }

    public function getCommitteeNameForUser(User $user): ?string
    {
        $committee = Committee::query()
            ->where('committee_head_id', $user->id)
            ->value('committee_name');

        return $committee !== null && trim($committee) !== '' ? trim($committee) : null;
    }

    public function buildOfficialFullName(User $user): string
    {
        $profile = $user->officialProfile;

        if ($profile instanceof OfficialProfile) {
            $middleInitial = $this->deriveMiddleInitial($profile->middle_name);

            return trim(implode(' ', array_filter([
                $profile->first_name ? mb_strtoupper($profile->first_name, 'UTF-8') : null,
                $middleInitial,
                $profile->last_name ? mb_strtoupper($profile->last_name, 'UTF-8') : null,
                $profile->suffix,
            ])));
        }

        return trim((string) $user->name);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatCommittee(Committee $committee): array
    {
        $createdAt = $committee->created_at;

        return [
            'id' => $committee->id,
            'name' => $committee->committee_name,
            'head_id' => $committee->committee_head_id,
            'head' => $committee->head ? $this->buildOfficialFullName($committee->head) : '—',
            'description' => $committee->description ?? '',
            'assigned_date' => $createdAt?->format('M j, Y') ?? '—',
            'assigned_time' => $createdAt?->format('g:i A') ?? '—',
            'status' => 'Active',
        ];
    }

    private function barangayCommitteesQuery(User $user): Builder
    {
        return Committee::query()
            ->whereHas('head', function (Builder $query) use ($user) {
                $query->where('barangay_id', $user->barangay_id)
                    ->where('role', User::ROLE_SK_OFFICIAL);
            });
    }

    private function assertCommitteeInBarangay(User $user, Committee $committee): void
    {
        $exists = $this->barangayCommitteesQuery($user)
            ->where('committees.id', $committee->id)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'committee' => ['Committee not found.'],
            ]);
        }
    }

    private function assertHeadBelongsToBarangay(User $user, int $headId): void
    {
        $valid = User::query()
            ->where('id', $headId)
            ->where('barangay_id', $user->barangay_id)
            ->where('role', User::ROLE_SK_OFFICIAL)
            ->where('status', User::STATUS_ACTIVE)
            ->exists();

        if (! $valid) {
            throw ValidationException::withMessages([
                'committee_head_id' => ['Selected committee head is not a valid SK official in your barangay.'],
            ]);
        }
    }

    private function assertUniqueHead(int $headId, ?int $ignoreCommitteeId = null): void
    {
        $query = Committee::query()->where('committee_head_id', $headId);

        if ($ignoreCommitteeId !== null) {
            $query->where('id', '!=', $ignoreCommitteeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'committee_head_id' => ['This SK official is already assigned as a committee head.'],
            ]);
        }
    }

    private function assertUniqueCommitteeName(User $user, string $committeeName, ?int $ignoreCommitteeId = null): void
    {
        $query = $this->barangayCommitteesQuery($user)
            ->whereRaw('LOWER(committee_name) = ?', [mb_strtolower(trim($committeeName), 'UTF-8')]);

        if ($ignoreCommitteeId !== null) {
            $query->where('committees.id', '!=', $ignoreCommitteeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'committee_name' => ['This committee is already assigned in your barangay.'],
            ]);
        }
    }

    private function deriveMiddleInitial(?string $middleName): ?string
    {
        $trimmed = trim((string) $middleName);

        if ($trimmed === '') {
            return null;
        }

        return mb_strtoupper(mb_substr($trimmed, 0, 1, 'UTF-8'), 'UTF-8').'.';
    }
}
