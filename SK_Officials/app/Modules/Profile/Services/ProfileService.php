<?php

namespace App\Modules\Profile\Services;

use App\Models\OfficialTerm;
use App\Models\User;
use App\Modules\Committees\Services\CommitteeService;

class ProfileService
{
    public function __construct(private readonly CommitteeService $committeeService)
    {
    }

    /**
     * @return array<string, string>
     */
    public function getDisplayData(User $user): array
    {
        $user->loadMissing([
            'officialProfile.latestTerm',
            'officialProfile.terms',
            'barangay',
        ]);

        $profile = $user->officialProfile;
        $term = $profile?->terms
            ->firstWhere('status', OfficialTerm::STATUS_ACTIVE)
            ?? $profile?->latestTerm;
        $nameFallback = $this->splitFullName($user->name);

        return [
            'first_name' => $this->displayName($profile?->first_name ?? $nameFallback['first_name']),
            'middle_name' => $this->displayName($profile?->middle_name ?? $nameFallback['middle_name']),
            'last_name' => $this->displayName($profile?->last_name ?? $nameFallback['last_name']),
            'suffix' => $this->displaySuffix($profile?->suffix),
            'sex' => $this->displayValue($profile?->sex),
            'birthdate' => $this->formatDate($profile?->date_of_birth),
            'contact_number' => $this->displayValue($profile?->contact_number),
            'position' => $this->displayPosition($profile?->position),
            'region' => $this->displayValue($profile?->region ?? $user->barangay?->region),
            'province' => $this->displayValue($profile?->province ?? $user->barangay?->province),
            'municipality' => $this->displayValue($profile?->municipality ?? $user->barangay?->municipality),
            'barangay' => $this->displayValue($user->barangay?->name),
            'term_start' => $this->formatDate($term?->term_start),
            'term_end' => $this->formatDate($term?->term_end),
            'committee' => $this->displayValue($this->committeeService->getCommitteeNameForUser($user)),
            'email' => $this->displayValue($user->email),
        ];
    }

    private function displayValue(?string $value): string
    {
        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : '—';
    }

    private function displayName(?string $value): string
    {
        $trimmed = trim((string) $value);

        return $trimmed !== '' ? mb_strtoupper($trimmed, 'UTF-8') : '—';
    }

    private function displaySuffix(?string $suffix): string
    {
        $trimmed = trim((string) $suffix);

        if ($trimmed === '' || strcasecmp($trimmed, 'none') === 0 || strcasecmp($trimmed, 'n/a') === 0) {
            return 'None';
        }

        return $trimmed;
    }

    private function displayPosition(?string $position): string
    {
        $trimmed = trim((string) $position);

        if ($trimmed === '') {
            return '—';
        }

        return match ($trimmed) {
            'Chairperson' => 'SK Chairperson',
            'Secretary' => 'SK Secretary',
            'Treasurer' => 'SK Treasurer',
            'Kagawad' => 'SK Kagawad',
            default => $trimmed,
        };
    }

    private function formatDate(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        try {
            return $value->format('F j, Y');
        } catch (\Throwable) {
            return '—';
        }
    }

    /**
     * @return array{first_name: ?string, middle_name: ?string, last_name: ?string}
     */
    private function splitFullName(?string $fullName): array
    {
        $parts = preg_split('/\s+/', trim((string) $fullName)) ?: [];

        if ($parts === []) {
            return [
                'first_name' => null,
                'middle_name' => null,
                'last_name' => null,
            ];
        }

        if (count($parts) === 1) {
            return [
                'first_name' => $parts[0],
                'middle_name' => null,
                'last_name' => null,
            ];
        }

        if (count($parts) === 2) {
            return [
                'first_name' => $parts[0],
                'middle_name' => null,
                'last_name' => $parts[1],
            ];
        }

        return [
            'first_name' => $parts[0],
            'middle_name' => implode(' ', array_slice($parts, 1, -1)),
            'last_name' => $parts[count($parts) - 1],
        ];
    }
}
