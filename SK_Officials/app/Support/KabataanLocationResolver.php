<?php

namespace App\Support;

use App\Models\KabataanRegistration;

final class KabataanLocationResolver
{
    /**
     * @param  array<string, mixed>  $formData
     */
    public static function resolveRegion(array $formData, ?object $barangay): string
    {
        $fromForm = self::formValue($formData, 'region');

        if ($fromForm !== null && $fromForm !== '' && $fromForm !== '—') {
            return $fromForm;
        }

        return (string) ($barangay->region ?? 'Region IV-A (CALABARZON)');
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    public static function resolveProvince(array $formData, ?object $barangay): string
    {
        $fromForm = self::formValue($formData, 'province');

        if ($fromForm !== null && $fromForm !== '' && $fromForm !== '—') {
            return $fromForm;
        }

        return (string) ($barangay->province ?? 'Laguna');
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    public static function resolveCity(array $formData, ?object $barangay): string
    {
        $fromForm = self::formValue($formData, 'city')
            ?? self::formValue($formData, 'municipality');

        if ($fromForm !== null && $fromForm !== '' && $fromForm !== '—') {
            return $fromForm;
        }

        return (string) ($barangay->municipality ?? 'Santa Cruz');
    }

    public static function forRegistration(KabataanRegistration $registration): array
    {
        $formData = $registration->form_data ?? [];
        $barangay = $registration->barangay;

        return [
            'region' => self::resolveRegion($formData, $barangay),
            'province' => self::resolveProvince($formData, $barangay),
            'city' => self::resolveCity($formData, $barangay),
        ];
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    private static function formValue(array $formData, string $key): ?string
    {
        $value = $formData[$key] ?? null;

        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
