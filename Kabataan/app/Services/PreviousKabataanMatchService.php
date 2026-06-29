<?php

namespace App\Services;

use App\Models\Barangay;
use App\Models\KabataanRegistration;
use App\Models\PreviousKabataan;
use Carbon\Carbon;

class PreviousKabataanMatchService
{
    public function findMatch(KabataanRegistration $registration): ?PreviousKabataan
    {
        $barangay = Barangay::query()->find($registration->barangay_id);

        if (! $barangay) {
            return null;
        }

        $records = PreviousKabataan::query()
            ->forBarangay((int) $registration->barangay_id)
            ->get();

        foreach ($records as $record) {
            if ($this->matches($registration, $record, $barangay)) {
                return $record;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $step1Fields
     */
    public function matchesStep1Fields(array $step1Fields, int $barangayId): ?PreviousKabataan
    {
        $barangay = Barangay::query()->find($barangayId);

        if (! $barangay) {
            return null;
        }

        $registration = new KabataanRegistration([
            'barangay_id' => $barangayId,
            'last_name' => $step1Fields['last_name'] ?? '',
            'first_name' => $step1Fields['first_name'] ?? '',
            'middle_name' => $step1Fields['middle_name'] ?? null,
            'suffix' => $this->resolveSuffixFromStep1($step1Fields),
            'form_data' => $step1Fields,
        ]);

        return $this->findMatch($registration);
    }

    private function matches(
        KabataanRegistration $registration,
        PreviousKabataan $record,
        Barangay $barangay,
    ): bool {
        $formData = is_array($registration->form_data) ? $registration->form_data : [];

        if (! $this->textEquals($registration->last_name, $record->last_name)) {
            return false;
        }

        if (! $this->textEquals($registration->first_name, $record->first_name)) {
            return false;
        }

        if (! $this->middleNameMatches($registration->middle_name, $record->middle_name)) {
            return false;
        }

        if (! $this->suffixMatches($registration->suffix, $record->suffix)) {
            return false;
        }

        $submittedBirthday = $this->normalizeBirthday($this->formScalar($formData['birthday'] ?? null));
        $previousBirthday = $this->normalizeBirthday($record->birthday);

        if ($previousBirthday === '') {
            $previousForm = is_array($record->form_data) ? $record->form_data : [];
            $previousBirthday = $this->normalizeBirthday(
                $this->formScalar($previousForm['birthday'] ?? $previousForm['birthday_(month_day_year)'] ?? null),
            );
        }

        if ($submittedBirthday === '' || $previousBirthday === '' || $submittedBirthday !== $previousBirthday) {
            return false;
        }

        $submittedSex = $this->normalizeSex($this->formScalar($formData['sex'] ?? null));
        $previousSex = $this->normalizeSex($record->sex);

        if ($previousSex === '') {
            $previousForm = is_array($record->form_data) ? $record->form_data : [];
            $previousSex = $this->normalizeSex(
                $this->formScalar($previousForm['sex'] ?? $previousForm['sex_assigned_at_birth'] ?? null),
            );
        }

        if ($submittedSex === '' || $previousSex === '' || $submittedSex !== $previousSex) {
            return false;
        }

        if (! $this->locationFieldMatches($barangay->region, $record->region)) {
            return false;
        }

        if (! $this->locationFieldMatches($barangay->province, $record->province)) {
            return false;
        }

        if (! $this->locationFieldMatches($barangay->municipality, $record->city)) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $step1Fields
     */
    private function resolveSuffixFromStep1(array $step1Fields): ?string
    {
        $suffix = $step1Fields['suffix'] ?? null;

        if ($suffix === 'Others') {
            $suffix = trim((string) ($step1Fields['custom_suffix'] ?? ''));
        }

        return $this->normalizeSuffix($suffix);
    }

    private function normalizeSuffix(?string $suffix): ?string
    {
        $normalized = trim((string) $suffix);

        if ($normalized === '' || strcasecmp($normalized, 'none') === 0) {
            return null;
        }

        return $normalized;
    }

    private function suffixMatches(?string $submitted, ?string $previous): bool
    {
        $left = $this->normalizeSuffix($submitted);
        $right = $this->normalizeSuffix($previous);

        return $left === $right;
    }

    private function middleNameMatches(?string $submitted, ?string $previous): bool
    {
        $left = $this->normalizeNamePart($submitted);
        $right = $this->normalizeNamePart($previous);

        return $left === $right;
    }

    private function textEquals(?string $left, ?string $right): bool
    {
        return $this->normalizeNamePart($left) === $this->normalizeNamePart($right);
    }

    private function normalizeNamePart(?string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim((string) $value));

        return strtoupper($normalized ?? '');
    }

    private function normalizeSex(?string $value): string
    {
        return strtoupper(trim((string) $value));
    }

    private function locationFieldMatches(?string $expected, ?string $actual): bool
    {
        $left = $this->normalizeLocationKey($expected);
        $right = $this->normalizeLocationKey($actual);

        if ($right === '') {
            return true;
        }

        if ($left === '') {
            return false;
        }

        if ($left === $right) {
            return true;
        }

        $compact = static fn (string $value): string => preg_replace('/[^A-Z0-9]/', '', $value) ?? '';

        $leftCompact = $compact($left);
        $rightCompact = $compact($right);

        return $leftCompact === $rightCompact
            || str_contains($leftCompact, $rightCompact)
            || str_contains($rightCompact, $leftCompact);
    }

    private function normalizeLocationKey(?string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim((string) $value));
        $normalized = strtoupper($normalized ?? '');
        $normalized = preg_replace('/^REGION\s+/', '', $normalized);
        $normalized = str_replace(['(', ')'], ' ', $normalized);

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? '');
    }

    private function formScalar(mixed $value): string
    {
        if (is_array($value)) {
            $value = $value[0] ?? '';
        }

        return trim((string) $value);
    }

    private function normalizeBirthday(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return '';
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $matches)) {
            return sprintf('%04d-%02d-%02d', (int) $matches[1], (int) $matches[2], (int) $matches[3]);
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $raw, $matches)) {
            return sprintf('%04d-%02d-%02d', (int) $matches[3], (int) $matches[1], (int) $matches[2]);
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2})$/', $raw, $matches)) {
            $year = (int) $matches[3];
            $year += $year >= 70 ? 1900 : 2000;

            return sprintf('%04d-%02d-%02d', $year, (int) $matches[1], (int) $matches[2]);
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable) {
            return '';
        }
    }
}
