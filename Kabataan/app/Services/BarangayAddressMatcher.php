<?php

namespace App\Services;

use App\Models\Barangay;

class BarangayAddressMatcher
{
    /**
     * @param  array{address?: ?string, barangay?: ?string, city?: ?string, province?: ?string}  $parsed
     * @return array{matched: bool, matched_name: ?string, score: float, reason: string}
     */
    public function matchesRegistrationBarangay(array $parsed, ?Barangay $barangay): array
    {
        return $this->matchesRegistrationAddress($parsed, $barangay);
    }

    /**
     * @param  array{address?: ?string, barangay?: ?string, city?: ?string, province?: ?string}  $parsed
     * @param  array<string, mixed>  $registrationFields
     * @return array{matched: bool, matched_name: ?string, score: float, reason: string}
     */
    public function matchesRegistrationAddress(
        array $parsed,
        ?Barangay $barangay,
        array $registrationFields = [],
        string $rawText = '',
    ): array {
        if ($barangay === null) {
            return [
                'matched' => false,
                'matched_name' => null,
                'score' => 0.0,
                'reason' => 'Registration barangay not found.',
            ];
        }

        $haystack = $this->normalize(implode(' ', array_filter([
            $rawText,
            $parsed['address'] ?? null,
            $parsed['barangay'] ?? null,
            $parsed['city'] ?? null,
            $parsed['province'] ?? null,
        ])));

        if ($haystack === '') {
            return [
                'matched' => false,
                'matched_name' => null,
                'score' => 0.0,
                'reason' => 'Insufficient address data for matching.',
            ];
        }

        $targetName = $this->normalize($barangay->name);

        if ($targetName !== '' && str_contains($haystack, $targetName)) {
            return [
                'matched' => true,
                'matched_name' => $barangay->name,
                'score' => 100.0,
                'reason' => 'Detected barangay name on uploaded ID.',
            ];
        }

        $purokZone = trim((string) ($registrationFields['purok_zone'] ?? ''));
        if ($purokZone !== '') {
            $purokMatch = $this->matchesPurokZone($haystack, $purokZone);
            if ($purokMatch['matched']) {
                return [
                    'matched' => true,
                    'matched_name' => $barangay->name,
                    'score' => $purokMatch['score'],
                    'reason' => $purokMatch['reason'],
                ];
            }
        }

        if ($this->containsSantaCruzLaguna($haystack) && $targetName !== '') {
            $tokens = preg_split('/\s+/', $targetName) ?: [];
            foreach ($tokens as $token) {
                if (strlen($token) >= 4 && str_contains($haystack, $token)) {
                    return [
                        'matched' => true,
                        'matched_name' => $barangay->name,
                        'score' => 92.0,
                        'reason' => 'Detected barangay within Santa Cruz, Laguna address on ID.',
                    ];
                }
            }
        }

        $detectedBarangay = $this->normalize((string) ($parsed['barangay'] ?? ''));

        if ($detectedBarangay !== '' && $targetName !== '') {
            similar_text($detectedBarangay, $targetName, $percent);

            if ($percent >= 75.0) {
                return [
                    'matched' => true,
                    'matched_name' => $barangay->name,
                    'score' => round($percent, 2),
                    'reason' => 'Detected barangay closely matches registration barangay.',
                ];
            }
        }

        if ($targetName !== '') {
            similar_text($haystack, $targetName, $fullPercent);

            if ($fullPercent >= 75.0) {
                return [
                    'matched' => true,
                    'matched_name' => $barangay->name,
                    'score' => round($fullPercent, 2),
                    'reason' => 'Address text matches registration barangay.',
                ];
            }
        }

        $municipal = $this->matchesSchoolIdMunicipalFallback(
            $haystack,
            $barangay,
            $registrationFields,
            (bool) ($registrationFields['_both_sides_uploaded'] ?? false),
        );

        if ($municipal['matched']) {
            return [
                'matched' => true,
                'matched_name' => $barangay->name,
                'score' => $municipal['score'],
                'reason' => $municipal['reason'],
            ];
        }

        return [
            'matched' => false,
            'matched_name' => null,
            'score' => 0.0,
            'reason' => 'Uploaded ID address does not match registration barangay or purok/sitio.',
        ];
    }

    /**
     * Fallback when handwriting on ID back is unreadable but a Santa Cruz school ID was uploaded.
     *
     * @param  array<string, mixed>  $registrationFields
     * @return array{matched: bool, score: float, reason: string}
     */
    public function matchesSchoolIdMunicipalFallback(
        string $haystack,
        ?Barangay $barangay,
        array $registrationFields,
        bool $bothSidesUploaded,
    ): array {
        if (! $bothSidesUploaded || $barangay === null) {
            return ['matched' => false, 'score' => 0.0, 'reason' => 'Both ID sides are required.'];
        }

        if (! config('ocr.trust_school_id_municipal_match', true)) {
            return ['matched' => false, 'score' => 0.0, 'reason' => 'School ID municipal fallback disabled.'];
        }

        $normalized = $this->normalize($haystack);

        if ($normalized === '') {
            return ['matched' => false, 'score' => 0.0, 'reason' => 'No OCR text available.'];
        }

        $purokZone = trim((string) ($registrationFields['purok_zone'] ?? ''));
        if ($purokZone === '') {
            return ['matched' => false, 'score' => 0.0, 'reason' => 'Registration purok/sitio is required.'];
        }

        $isSchoolId = str_contains($normalized, 'HIGH SCHOOL')
            || str_contains($normalized, 'SCHOOL')
            || str_contains($normalized, 'NATIONAL HIGH');

        $hasMunicipalHint = str_contains($normalized, 'SANTA CRUZ')
            || preg_match('/\bSANTA\b/', $normalized) === 1
            || str_contains($normalized, 'LAGUNA')
            || preg_match('/\bLAG\b/', $normalized) === 1;

        if (! $isSchoolId || ! $hasMunicipalHint) {
            return ['matched' => false, 'score' => 0.0, 'reason' => 'School ID municipal markers not found.'];
        }

        return [
            'matched' => true,
            'score' => 90.0,
            'reason' => 'School ID issued in Santa Cruz, Laguna with complete front/back upload matches your registration.',
        ];
    }

    /**
     * @return array{matched: bool, score: float, reason: string}
     */
    private function matchesPurokZone(string $haystack, string $purokZone): array
    {
        $normalizedZone = $this->normalize($purokZone);

        if ($normalizedZone !== '' && str_contains($haystack, $normalizedZone)) {
            return [
                'matched' => true,
                'score' => 98.0,
                'reason' => 'Detected purok/sitio/zone on uploaded ID matches registration.',
            ];
        }

        if (preg_match('/\b(SITIO|PUROK|ZONE)\s*(\d+)\b/i', $purokZone, $zoneMatch)) {
            $type = strtoupper($zoneMatch[1]);
            $number = $zoneMatch[2];
            $pattern = $type.'\s*'.$number;

            if (preg_match('/\b'.$pattern.'\b/i', $haystack)) {
                return [
                    'matched' => true,
                    'score' => 95.0,
                    'reason' => 'Detected '.$type.' '.$number.' on uploaded ID matches registration.',
                ];
            }
        }

        $zoneTokens = array_values(array_filter(
            preg_split('/\s+/', $normalizedZone) ?: [],
            fn (string $token) => strlen($token) >= 3,
        ));

        $matchedTokens = 0;
        foreach ($zoneTokens as $token) {
            if (str_contains($haystack, $token)) {
                $matchedTokens++;
            }
        }

        if ($matchedTokens >= 2 || ($matchedTokens === 1 && count($zoneTokens) === 1)) {
            return [
                'matched' => true,
                'score' => 88.0,
                'reason' => 'Detected sitio/purok details on uploaded ID match registration.',
            ];
        }

        return [
            'matched' => false,
            'score' => 0.0,
            'reason' => 'Purok/sitio not found on ID.',
        ];
    }

    private function containsSantaCruzLaguna(string $haystack): bool
    {
        return str_contains($haystack, 'SANTA CRUZ')
            && (str_contains($haystack, 'LAGUNA') || preg_match('/\bLAG\b/', $haystack) === 1);
    }

    private function normalize(string $value): string
    {
        $value = strtoupper(trim($value));
        $value = preg_replace('/\bSTA\.?\b/u', 'SANTA', $value) ?? $value;
        $value = preg_replace('/\bSTO\.?\b/u', 'SANTO', $value) ?? $value;
        $value = preg_replace('/\bLAG\.?\b/u', 'LAGUNA', $value) ?? $value;
        $value = preg_replace('/[^A-Z0-9\s]/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }
}
