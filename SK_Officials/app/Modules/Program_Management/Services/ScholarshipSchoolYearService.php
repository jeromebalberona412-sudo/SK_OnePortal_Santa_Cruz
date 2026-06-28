<?php

namespace App\Modules\Program_Management\Services;

use App\Models\ScholarshipSchoolYear;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ScholarshipSchoolYearService
{
    /**
     * @return list<string>
     */
    public function listLabelsForBarangay(User $user): array
    {
        if ($user->barangay_id === null) {
            return [];
        }

        return ScholarshipSchoolYear::query()
            ->where('barangay_id', $user->barangay_id)
            ->orderByDesc('label')
            ->pluck('label')
            ->values()
            ->all();
    }

    public function canManageSchoolYears(User $user): bool
    {
        return $user->hasRole(
            User::ROLE_SK_OFFICIAL,
            User::ROLE_SK_FED,
            User::ROLE_ADMIN,
        );
    }

    /**
     * @return array{label: string}
     */
    public function store(User $user, array $data): array
    {
        if (! $this->canManageSchoolYears($user)) {
            throw ValidationException::withMessages([
                'school_year' => ['You are not allowed to add school years.'],
            ]);
        }

        if ($user->barangay_id === null) {
            throw ValidationException::withMessages([
                'school_year' => ['Barangay is required to add a school year.'],
            ]);
        }

        $label = $this->sanitizeLabel($data['label'] ?? null);

        $exists = ScholarshipSchoolYear::query()
            ->where('barangay_id', $user->barangay_id)
            ->where('label', $label)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'label' => ['This school year is already in the list.'],
            ]);
        }

        $record = ScholarshipSchoolYear::create([
            'tenant_id' => $user->tenant_id,
            'barangay_id' => $user->barangay_id,
            'label' => $label,
            'created_by' => $user->id,
        ]);

        return [
            'label' => $record->label,
        ];
    }

    public function sanitizeLabel(mixed $value): string
    {
        $label = trim(str_replace(['–', '—', ' '], '-', (string) $value));

        if ($label === '' || ! preg_match('/^\d{4}-\d{4}$/', $label)) {
            throw ValidationException::withMessages([
                'label' => ['Enter a valid school year (e.g. 2026-2027).'],
            ]);
        }

        [$startYear, $endYear] = array_map('intval', explode('-', $label));
        if ($endYear !== $startYear + 1) {
            throw ValidationException::withMessages([
                'label' => ['The second year must be one year after the first (e.g. 2026-2027).'],
            ]);
        }

        return $label;
    }

    /**
     * @return Collection<int, string>
     */
    public function labelsFromPrograms(Collection $programs): Collection
    {
        return $programs
            ->map(function ($program) {
                $details = is_array($program['scholarship_details'] ?? null)
                    ? $program['scholarship_details']
                    : [];

                return trim((string) ($details['school_year'] ?? ''));
            })
            ->filter(fn (string $label) => $label !== '')
            ->unique()
            ->values();
    }
}
