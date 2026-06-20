<?php

namespace App\Modules\Accounts\Services;

use App\Modules\Accounts\Models\Barangay;
use App\Modules\Accounts\Models\OfficialProfile;
use App\Modules\Shared\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BatchAccountImportService
{
    /** @var array<string, int> */
    private array $barangayLookup = [];

    private ?int $defaultBarangayId = null;

    public function __construct(private readonly int $tenantId)
    {
        $this->barangayLookup = [];

        Barangay::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->each(function (Barangay $barangay): void {
                if ($this->defaultBarangayId === null) {
                    $this->defaultBarangayId = $barangay->id;
                }

                $this->registerBarangayAlias($barangay->name, $barangay->id);
            });
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{row: int, error: string}>
     */
    public function validateRows(array $rows, string $role): array
    {
        $errors = [];
        $seenEmails = [];
        $chairsInFileByBarangay = [];
        $rosterService = app(FederationRosterService::class);

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                $errors[] = ['row' => $index + 1, 'error' => 'Invalid row format.'];

                continue;
            }

            try {
                $normalized = $this->normalizeAccountRow($row, $role, true);
                $email = $normalized['email'];

                if (isset($seenEmails[$email])) {
                    $errors[] = ['row' => $index + 1, 'error' => 'Duplicate email in upload file.'];

                    continue;
                }

                $seenEmails[$email] = true;

                if (User::query()->where('email', $email)->whereNull('deleted_at')->exists()) {
                    $errors[] = ['row' => $index + 1, 'error' => 'Email is already registered.'];
                }

                if ($role === User::ROLE_SK_OFFICIAL && $rosterService->isChairPosition((string) $normalized['position'])) {
                    $barangayId = (int) ($normalized['barangay_id'] ?? 0);

                    if ($barangayId > 0) {
                        if (isset($chairsInFileByBarangay[$barangayId])) {
                            $errors[] = ['row' => $index + 1, 'error' => 'This barangay already has an SK Chairperson in the uploaded file.'];
                        } else {
                            $chairsInFileByBarangay[$barangayId] = true;
                        }

                        try {
                            $rosterService->assertSingleChairPerBarangay(
                                $this->tenantId,
                                $barangayId,
                                (string) $normalized['position'],
                            );
                        } catch (ValidationException $chairException) {
                            $errors[] = [
                                'row' => $index + 1,
                                'error' => collect($chairException->errors())->flatten()->first() ?? 'This barangay already has an SK Chairperson account.',
                            ];
                        }
                    }
                }
            } catch (ValidationException $exception) {
                $errors[] = [
                    'row' => $index + 1,
                    'error' => collect($exception->errors())->flatten()->first() ?? 'Validation failed.',
                ];
            }
        }

        return $errors;
    }

    private function registerBarangayAlias(string $name, int $id): void
    {
        $this->barangayLookup[$this->normalizeKey($name)] = $id;

        $canonical = $this->normalizeBarangayName($name);
        if ($canonical !== $name) {
            $this->barangayLookup[$this->normalizeKey($canonical)] = $id;
        }

        if (preg_match('/^poblacion\s+([ivx]+)$/i', $canonical, $matches)) {
            $roman = strtoupper($matches[1]);
            $digit = $this->romanToDigit($roman);

            if ($digit !== null) {
                $this->barangayLookup[$this->normalizeKey('Poblacion '.$digit)] = $id;
                $this->barangayLookup[$this->normalizeKey('Poblacion '.$roman)] = $id;
                $this->barangayLookup[$this->normalizeKey('Barangay '.$digit)] = $id;
                $this->barangayLookup[$this->normalizeKey('Barangay '.$roman)] = $id;
                $this->barangayLookup[$this->normalizeKey('Barangay '.$roman.' (Poblacion)')] = $id;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function normalizeAccountRow(array $row, string $role, bool $strictDemographics = true): array
    {
        $firstName = mb_strtoupper($this->stringValue($row, ['first_name', 'first name']), 'UTF-8');
        $lastName = mb_strtoupper($this->stringValue($row, ['last_name', 'last name']), 'UTF-8');

        if ($role === User::ROLE_SK_OFFICIAL) {
            $firstName = preg_replace('/\s+/u', '', $firstName) ?? $firstName;
            $lastName = preg_replace('/\s+/u', '', $lastName) ?? $lastName;
        }
        $email = strtolower($this->stringValue($row, ['email', 'email address']));
        $barangayName = $this->stringValue($row, ['barangay', 'barangay_name', 'barangay name']);
        $municipality = $this->stringValue($row, ['municipality']) ?: 'Santa Cruz';
        $barangayId = $this->resolveBarangayId(
            $this->intValue($row, ['barangay_id', 'barangay id']) ?: null,
            $barangayName !== '' ? $barangayName : $municipality
        );

        if ($barangayId === null && $role === User::ROLE_SK_FED && ! $strictDemographics) {
            $barangayId = $this->defaultBarangayId;
        }

        $status = User::STATUS_ACTIVE;
        $position = $this->normalizePosition($this->stringValue($row, ['position']), $role);

        $termEnd = $this->parseDate($this->rawValue($row, [
            'term_end', 'term end date', 'term end', 'term_end_date', 'end date', 'term expiry', 'term expiry date',
        ]));
        $termStart = $this->parseDate($this->rawValue($row, [
            'term_start', 'term start date', 'term start', 'term_start_date', 'start date', 'term begin', 'term begin date',
        ]));

        if (! $strictDemographics) {
            if ($termStart === null) {
                $termStart = now()->startOfYear()->toDateString();
            }

            if ($termEnd === null) {
                $termEnd = Carbon::parse($termStart)->addYears(3)->toDateString();
            }
        }

        $dateOfBirth = $this->parseDate($this->rawValue($row, [
            'date_of_birth', 'birthdate', 'date of birth', 'birth date', 'dob',
        ]));

        $suffix = $this->normalizeSuffix($this->stringValue($row, ['suffix']));
        $sex = $this->normalizeSex($this->stringValue($row, ['sex']));
        $contactNumber = $this->normalizeContactNumber($this->stringValue($row, ['contact_number', 'contact number']));

        $middleNameRaw = $this->stringValue($row, ['middle_name', 'middle name']);
        $middleName = $middleNameRaw !== '' ? mb_strtoupper($middleNameRaw, 'UTF-8') : null;
        if ($role === User::ROLE_SK_OFFICIAL && $middleName !== null) {
            $middleName = preg_replace('/\s+/u', '', $middleName) ?? $middleName;
            $middleName = $middleName !== '' ? $middleName : null;
        }

        $data = [
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'suffix' => $suffix,
            'sex' => $sex,
            'date_of_birth' => $dateOfBirth,
            'age' => $this->intValue($row, ['age']) ?: ($dateOfBirth ? Carbon::parse($dateOfBirth)->age : null),
            'contact_number' => $contactNumber,
            'email' => $email,
            'role' => $role,
            'status' => $status,
            'barangay_id' => $barangayId,
            'position' => $position,
            'region' => $this->stringValue($row, ['region']) ?: 'IV-A CALABARZON',
            'province' => $this->stringValue($row, ['province']) ?: 'Laguna',
            'municipality' => $municipality,
            'term_start' => $termStart,
            'term_end' => $termEnd,
            'term_status' => 'ACTIVE',
        ];

        $this->assertRowIsValid($data, $barangayName, $role, $strictDemographics);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertRowIsValid(array $data, string $barangayName, string $role, bool $strictDemographics): void
    {
        $errors = [];
        $isOfficial = $role === User::ROLE_SK_OFFICIAL;
        $namePattern = $isOfficial ? '/^[A-Z\-']+$/u' : '/^[A-Z\s\-\']+$/u';
        $middlePattern = $isOfficial ? '/^[A-Z\-']*$/u' : '/^[A-Z\s\-\']*$/u';
        $nameMax = $isOfficial ? 50 : 35;

        if ($data['first_name'] === '') {
            $errors[] = 'First name is required.';
        } elseif (mb_strlen($data['first_name']) < 3) {
            $errors[] = 'First name must be at least 3 characters.';
        } elseif (mb_strlen($data['first_name']) > $nameMax) {
            $errors[] = 'First name must not exceed '.$nameMax.' characters.';
        } elseif (! preg_match($namePattern, $data['first_name'])) {
            $errors[] = $isOfficial
                ? 'First name must use uppercase letters only, with no spaces.'
                : 'First name must use uppercase letters only.';
        }

        if ($data['middle_name'] !== null && mb_strlen((string) $data['middle_name']) > $nameMax) {
            $errors[] = 'Middle name must not exceed '.$nameMax.' characters.';
        } elseif ($data['middle_name'] !== null && ! preg_match($middlePattern, (string) $data['middle_name'])) {
            $errors[] = $isOfficial
                ? 'Middle name must use uppercase letters only, with no spaces.'
                : 'Middle name must use uppercase letters only.';
        }

        if ($data['last_name'] === '') {
            $errors[] = 'Last name is required.';
        } elseif (mb_strlen($data['last_name']) < 3) {
            $errors[] = 'Last name must be at least 3 characters.';
        } elseif (mb_strlen($data['last_name']) > $nameMax) {
            $errors[] = 'Last name must not exceed '.$nameMax.' characters.';
        } elseif (! preg_match($namePattern, $data['last_name'])) {
            $errors[] = $isOfficial
                ? 'Last name must use uppercase letters only, with no spaces.'
                : 'Last name must use uppercase letters only.';
        }

        if ($data['suffix'] !== null && mb_strlen((string) $data['suffix']) > 10) {
            $errors[] = 'Suffix must not exceed 10 characters.';
        }

        if ($data['email'] === '' || ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email';
        } elseif (strlen($data['email']) > 254) {
            $errors[] = 'Email must not exceed 254 characters.';
        }

        if ($data['position'] === null || $data['position'] === '') {
            $errors[] = 'Position is required or not recognized for this account type.';
        }

        if ($data['barangay_id'] === null) {
            $errors[] = $barangayName !== ''
                ? 'Barangay "'.$barangayName.'" was not found.'
                : 'Barangay is required.';
        }

        if ($strictDemographics) {
            if ($data['sex'] === null) {
                $errors[] = 'Sex must be Male or Female.';
            }

            if ($data['date_of_birth'] === null) {
                $errors[] = 'Birthdate is required.';
            } elseif (Carbon::parse($data['date_of_birth'])->isFuture()) {
                $errors[] = 'Birthdate must be before today.';
            }

            if ($data['contact_number'] === '') {
                $errors[] = 'Contact number is required.';
            } elseif (! preg_match('/^09\d{9}$/', $data['contact_number'])) {
                $errors[] = 'Contact number must be 11 digits starting with 09.';
            }

            if ($data['term_start'] === null) {
                $errors[] = 'Term start date is required.';
            } elseif ($data['term_start'] < '2023-01-01') {
                $errors[] = 'Term start date cannot be before 2023.';
            }

            if ($data['term_end'] === null) {
                $errors[] = 'Term end date is required.';
            }
        }

        if ($data['term_start'] !== null && $data['term_end'] !== null) {
            if ($data['term_start'] >= $data['term_end']) {
                $errors[] = 'Term end date must be after term start date.';
            } elseif (Carbon::parse($data['term_end'])->gt(Carbon::parse($data['term_start'])->addYears(5))) {
                $errors[] = 'Term end date must be within 5 years of the term start date.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'row' => implode(' ', $errors),
            ]);
        }
    }

    private function normalizeContactNumber(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if ($digits === '') {
            return '';
        }

        if (! str_starts_with($digits, '09')) {
            $digits = '09'.ltrim($digits, '0');
        }

        return substr($digits, 0, 11);
    }

    private function resolveBarangayId(?int $barangayId, string $barangayName): ?int
    {
        if ($barangayId !== null && $barangayId > 0) {
            $exists = Barangay::query()
                ->where('id', $barangayId)
                ->where('tenant_id', $this->tenantId)
                ->exists();

            return $exists ? $barangayId : null;
        }

        if ($barangayName === '') {
            return null;
        }

        $canonicalName = $this->normalizeBarangayName($barangayName);

        return $this->barangayLookup[$this->normalizeKey($canonicalName)]
            ?? $this->barangayLookup[$this->normalizeKey($barangayName)]
            ?? null;
    }

    private function normalizeBarangayName(string $name): string
    {
        $trimmed = trim($name);

        if (preg_match('/^barangay\s+([0-9ivx]+)\s*\(poblacion\)$/i', $trimmed, $matches)) {
            return 'Poblacion '.$this->normalizeRomanOrDigitToken($matches[1]);
        }

        if (preg_match('/^poblacion\s+([0-9ivx]+)$/i', $trimmed, $matches)) {
            return 'Poblacion '.$this->normalizeRomanOrDigitToken($matches[1]);
        }

        if (preg_match('/^barangay\s+([0-9ivx]+)$/i', $trimmed, $matches)) {
            return 'Poblacion '.$this->normalizeRomanOrDigitToken($matches[1]);
        }

        if (Str::lower($trimmed) === 'santa cruz') {
            return $trimmed;
        }

        return $trimmed;
    }

    private function normalizeRomanOrDigitToken(string $token): string
    {
        $token = trim($token);

        if (ctype_digit($token)) {
            return $this->digitToRoman((int) $token) ?? $token;
        }

        return strtoupper($token);
    }

    private function digitToRoman(int $digit): ?string
    {
        return match ($digit) {
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            default => null,
        };
    }

    private function romanToDigit(string $roman): ?int
    {
        return match (strtoupper($roman)) {
            'I' => 1,
            'II' => 2,
            'III' => 3,
            'IV' => 4,
            'V' => 5,
            default => null,
        };
    }

    private function normalizePosition(string $value, string $role): ?string
    {
        $normalized = Str::lower(trim($value));
        $normalized = str_replace(['sk ', 'sangguniang kabataan '], '', $normalized);

        $aliases = [
            'chairperson' => 'Chairperson',
            'chairman' => 'Chairperson',
            'secretary' => 'Secretary',
            'treasurer' => 'Treasurer',
            'kagawad' => 'Kagawad',
            'councilor' => 'Councilor',
            'auditor' => 'Auditor',
            'pio' => 'PIO',
            'president' => 'President',
            'vice president' => 'Vice President',
            'sergeant at arms' => 'Sergeant at Arms',
        ];

        $position = $aliases[$normalized] ?? null;

        if ($position === null && $value !== '') {
            $allowed = OfficialProfile::positionsForRole($role);
            foreach ($allowed as $allowedPosition) {
                if (Str::lower($allowedPosition) === $normalized) {
                    $position = $allowedPosition;
                    break;
                }
            }
        }

        if ($position === null) {
            return null;
        }

        $allowed = OfficialProfile::positionsForRole($role);

        return in_array($position, $allowed, true) ? $position : null;
    }

    private function normalizeSuffix(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = trim($value);

        if (in_array(Str::lower($normalized), ['none', 'n/a', 'na', '-'], true)) {
            return null;
        }

        return match (Str::lower(rtrim($normalized, '.'))) {
            'jr' => 'Jr.',
            'sr' => 'Sr.',
            'ii' => 'II',
            'iii' => 'III',
            'iv' => 'IV',
            'v' => 'V',
            default => mb_strlen($normalized) <= 10 ? $normalized : null,
        };
    }

    private function normalizeSex(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match (Str::lower(trim($value))) {
            'male', 'm' => 'Male',
            'female', 'f' => 'Female',
            default => in_array($value, ['Male', 'Female'], true) ? $value : null,
        };
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        if (is_numeric($value)) {
            $serial = (float) $value;

            return Carbon::createFromDate(1899, 12, 30)->addDays((int) round($serial))->toDateString();
        }

        $stringValue = trim((string) $value);

        foreach (['Y-m-d', 'm/d/Y', 'n/j/Y', 'd/m/Y', 'j/n/Y', 'm-d-Y', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $stringValue)->toDateString();
            } catch (\Throwable) {
                // Try the next common spreadsheet format.
            }
        }

        try {
            return Carbon::parse($stringValue)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $keys
     */
    private function rawValue(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && trim((string) $row[$key]) !== '') {
                return $row[$key];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $keys
     */
    private function stringValue(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $row)) {
                continue;
            }

            $value = trim((string) $row[$key]);

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $keys
     */
    private function intValue(array $row, array $keys): ?int
    {
        $value = $this->stringValue($row, $keys);

        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function nullableString(string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    private function normalizeKey(string $value): string
    {
        return Str::lower(trim($value));
    }
}
