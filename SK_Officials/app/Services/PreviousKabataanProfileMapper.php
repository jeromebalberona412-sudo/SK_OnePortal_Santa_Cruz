<?php

namespace App\Services;

use App\Models\PreviousKabataan;

class PreviousKabataanProfileMapper
{
    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    public static function extractProfileColumns(array $source): array
    {
        $value = static function (array $keys) use ($source): ?string {
            foreach ($keys as $key) {
                if (! array_key_exists($key, $source)) {
                    continue;
                }

                $raw = $source[$key];

                if (is_array($raw)) {
                    $raw = $raw[0] ?? null;
                }

                if ($raw === null) {
                    continue;
                }

                $text = trim((string) $raw);

                if ($text !== '') {
                    return $text;
                }
            }

            return null;
        };

        return [
            'age' => $value(['age']),
            'birthday' => $value(['birthday', 'birthday_(month_day_year)']),
            'sex' => $value(['sex', 'sex_assigned_at_birth', 'sexAssignedAtBirth']),
            'civil_status' => $value(['civil_status', 'civilStatus']),
            'youth_classification' => $value(['youth_classification', 'youthClassification']),
            'youth_age_group' => $value(['youth_age_group', 'youthAgeGroup']),
            'home_address' => $value(['home_address', 'homeAddress', 'purok_zone', 'purokZone']),
            'education' => $value(['education', 'highest_educational_attainment', 'highestEducation', 'educational_background']),
            'work_status' => $value(['work_status', 'workStatus']),
            'registered_voter' => $value(['registered_voter', 'registered_voter?', 'sk_voter', 'skVoter', 'registeredVoter']),
            'voted_last_election' => $value(['voted_last_election', 'voted_last_election?', 'sk_voted', 'votingHistory', 'votedLastElection']),
            'kk_assembly' => $value(['kk_assembly', 'attended_kk_assembly', 'attended_kk_assembly?', 'attended_kk__assembly?', 'kkAssembly']),
            'kk_assembly_count' => $value([
                'kk_assembly_count',
                'vote_frequency',
                'votingFrequency',
                'if_yes,_how_many_times?',
                'if_yes__how_many_times?',
            ]),
            'barangay_name' => $value(['barangay', 'barangay_name', 'barangayName']),
            'region' => $value(['region']),
            'province' => $value(['province']),
            'city' => $value(['city', 'city_municipality', 'cityMunicipality']),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function buildCreateAttributes(array $row, array $context): array
    {
        $name = trim((string) ($row['name'] ?? ''));
        $lastName = trim((string) ($row['last_name'] ?? $row['lastName'] ?? ''));
        $firstName = trim((string) ($row['first_name'] ?? $row['firstName'] ?? ''));

        if ($lastName === '' && $name !== '') {
            $lastName = $name;
        }

        $profile = self::extractProfileColumns($row);

        return array_merge($profile, [
            'kabataan_registration_id' => $context['kabataan_registration_id'] ?? null,
            'tenant_id' => $context['tenant_id'],
            'barangay_id' => $context['barangay_id'],
            'moved_by_user_id' => $context['moved_by_user_id'] ?? null,
            'last_name' => $context['last_name'] ?? ($lastName !== '' ? $lastName : ($name !== '' ? $name : '')),
            'first_name' => $context['first_name'] ?? ($firstName !== '' ? $firstName : ($name !== '' ? $name : '')),
            'middle_name' => $context['middle_name'] ?? $row['middle_name'] ?? $row['middleName'] ?? null,
            'suffix' => $context['suffix'] ?? $row['suffix'] ?? null,
            'email' => $context['email'] ?? $row['email'] ?? null,
            'contact_number' => $context['contact_number'] ?? $row['contact'] ?? $row['contact_number'] ?? null,
            'form_data' => $row,
            'profiling_year' => $row['year'] ?? $context['profiling_year'] ?? now()->year,
            'moved_at' => $context['moved_at'] ?? now(),
            'barangay_name' => $context['barangay_name'] ?? $profile['barangay_name'] ?? null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function toApiArray(PreviousKabataan $record): array
    {
        $fd = $record->form_data ?? [];
        $column = static fn (string $name): string => self::resolveColumn($record, $name, $fd);

        return [
            'id' => $record->id,
            'respondent_no' => 'PK-'.$record->profiling_year.'-'.str_pad((string) $record->id, 3, '0', STR_PAD_LEFT),
            'profiling_year' => $record->profiling_year,
            'last_name' => $record->last_name ?? '',
            'first_name' => $record->first_name ?? '',
            'middle_name' => $record->middle_name ?? '',
            'suffix' => $record->suffix ?? '',
            'age' => $column('age'),
            'birthday' => $column('birthday'),
            'sex' => $column('sex'),
            'email' => $record->email ?? '',
            'contact_number' => $record->contact_number ?? '',
            'barangay' => self::filled($record->barangay_name)
                ? $record->barangay_name
                : ($record->barangay?->name ?? $column('barangay_name')),
            'home_address' => $column('home_address'),
            'purok_zone' => $fd['purokZone'] ?? $fd['purok_zone'] ?? '',
            'sk_voter' => $column('registered_voter'),
            'civil_status' => $column('civil_status'),
            'youth_classification' => $column('youth_classification'),
            'youth_age_group' => $column('youth_age_group'),
            'work_status' => $column('work_status'),
            'education' => $column('education'),
            'sk_voted' => $column('voted_last_election'),
            'vote_frequency' => $column('kk_assembly_count'),
            'kk_assembly' => $column('kk_assembly'),
            'region' => $column('region'),
            'province' => $column('province'),
            'city' => $column('city'),
            'date' => $record->moved_at?->format('m/d/Y') ?? $record->created_at?->format('m/d/Y') ?? '',
        ];
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    private static function resolveColumn(PreviousKabataan $record, string $column, array $formData): string
    {
        $value = $record->{$column};

        if (self::filled($value)) {
            return trim((string) $value);
        }

        return self::extractProfileColumns($formData)[$column] ?? '';
    }

    private static function filled(?string $value): bool
    {
        return $value !== null && trim($value) !== '';
    }
}
