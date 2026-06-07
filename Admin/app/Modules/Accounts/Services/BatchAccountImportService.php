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

    public function __construct(private readonly int $tenantId)
    {
        $this->barangayLookup = [];

        Barangay::query()
            ->where('tenant_id', $tenantId)
            ->get()
            ->each(function (Barangay $barangay): void {
                $this->registerBarangayAlias($barangay->name, $barangay->id);
            });
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
    public function normalizeAccountRow(array $row, string $role): array
    {
        $firstName = $this->stringValue($row, ['first_name', 'first name']);
        $lastName = $this->stringValue($row, ['last_name', 'last name']);
        $email = $this->stringValue($row, ['email', 'email address']);
        $barangayName = $this->stringValue($row, ['barangay', 'barangay_name', 'barangay name']);
        $barangayId = $this->resolveBarangayId(
            $this->intValue($row, ['barangay_id', 'barangay id']) ?: null,
            $barangayName
        );

        $status = $this->normalizeStatus($this->stringValue($row, ['status']) ?: 'ACTIVE');
        $position = $this->normalizePosition($this->stringValue($row, ['position']), $role);

        $termStart = $this->parseDate($this->rawValue($row, [
            'term_start', 'term start date', 'term start', 'term_start_date', 'start date', 'term begin', 'term begin date',
        ]));
        $termEnd = $this->parseDate($this->rawValue($row, [
            'term_end', 'term end date', 'term end', 'term_end_date', 'end date', 'term expiry', 'term expiry date',
        ]));
        $dateOfBirth = $this->parseDate($this->rawValue($row, [
            'date_of_birth', 'birthdate', 'date of birth', 'birth date', 'dob',
        ]));

        $suffix = $this->normalizeSuffix($this->stringValue($row, ['suffix']));
        $sex = $this->normalizeSex($this->stringValue($row, ['sex']));

        $data = [
            'first_name' => $firstName,
            'middle_name' => $this->nullableString($this->stringValue($row, ['middle_name', 'middle name'])),
            'last_name' => $lastName,
            'suffix' => $suffix,
            'sex' => $sex,
            'date_of_birth' => $dateOfBirth,
            'age' => $this->intValue($row, ['age']) ?: ($dateOfBirth ? Carbon::parse($dateOfBirth)->age : null),
            'contact_number' => $this->stringValue($row, ['contact_number', 'contact number']),
            'email' => strtolower($email),
            'role' => $role,
            'status' => $status,
            'barangay_id' => $barangayId,
            'position' => $position,
            'term_start' => $termStart,
            'term_end' => $termEnd,
            'term_status' => $status === User::STATUS_INACTIVE ? 'INACTIVE' : 'ACTIVE',
        ];

        $this->assertRowIsValid($data, $barangayName);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertRowIsValid(array $data, string $barangayName): void
    {
        $errors = [];

        if ($data['first_name'] === '') {
            $errors[] = 'First name is required.';
        }

        if ($data['last_name'] === '') {
            $errors[] = 'Last name is required.';
        }

        if ($data['email'] === '' || ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }

        if ($data['sex'] === null) {
            $errors[] = 'Sex must be Male or Female.';
        }

        if ($data['date_of_birth'] === null) {
            $errors[] = 'Birthdate is required.';
        }

        if ($data['contact_number'] === '') {
            $errors[] = 'Contact number is required.';
        }

        if ($data['position'] === null) {
            $errors[] = 'Position is invalid or missing.';
        }

        if ($data['barangay_id'] === null) {
            $errors[] = $barangayName !== ''
                ? 'Barangay "'.$barangayName.'" was not found.'
                : 'Barangay is required.';
        }

        if ($data['term_start'] === null || $data['term_end'] === null) {
            $errors[] = 'Term start and term end dates are required.';
        } elseif ($data['term_start'] >= $data['term_end']) {
            $errors[] = 'Term end date must be after term start date.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'row' => implode(' ', $errors),
            ]);
        }
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
            'chairman' => 'Chairman',
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

    private function normalizeStatus(string $value): string
    {
        $normalized = Str::upper(trim($value));

        return match ($normalized) {
            'INACTIVE', 'DISABLED' => User::STATUS_INACTIVE,
            default => User::STATUS_ACTIVE,
        };
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
            default => in_array($normalized, ['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'], true) ? $normalized : null,
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
