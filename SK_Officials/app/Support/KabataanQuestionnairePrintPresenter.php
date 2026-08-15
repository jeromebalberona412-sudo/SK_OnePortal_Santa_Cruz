<?php

namespace App\Support;

use App\Models\KabataanRegistration;
use App\Services\RespondentNumberService;
use Carbon\CarbonInterface;

class KabataanQuestionnairePrintPresenter
{
    /**
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    public static function present(
        KabataanRegistration $registration,
        array $formData,
        ?CarbonInterface $submittedAt = null,
        ?string $barangayLogoUrl = null,
    ): array {
        $read = static function (string $key, string $fallback = '') use ($formData, $registration): string {
            $value = $formData[$key] ?? null;
            if (is_array($value)) {
                $value = $value[0] ?? null;
            }
            if (! filled($value) && isset($registration->{$key})) {
                $value = $registration->{$key};
            }

            return filled($value) ? (string) $value : $fallback;
        };

        $isChecked = static function (string $field, string $option) use ($read): bool {
            $keys = [$field];
            if ($field === 'education') {
                $keys[] = 'educational_background';
            }

            $optionNorm = strtolower(trim($option));
            $aliases = [
                'there was no kk assembly' => ['there was no kk assembly meeting'],
                'there was no kk assembly meeting' => ['there was no kk assembly'],
                'not interested to attend' => ['not interested to attend'],
                'high school level' => ['high school level'],
                'high school grad' => ['high school grad'],
            ];

            foreach ($keys as $key) {
                $stored = strtolower(trim($read($key)));
                if ($stored === '') {
                    continue;
                }
                if ($stored === $optionNorm) {
                    return true;
                }
                foreach ($aliases[$optionNorm] ?? [] as $alias) {
                    if ($stored === $alias) {
                        return true;
                    }
                }
            }

            return false;
        };

        $suffix = trim((string) ($registration->suffix ?? $read('suffix')));
        $customSuffix = $read('custom_suffix') ?: $read('suffix_other');
        if ($suffix === '' || strcasecmp($suffix, 'none') === 0) {
            $suffix = '';
        } elseif (strcasecmp($suffix, 'other') === 0 || strcasecmp($suffix, 'others') === 0) {
            $suffix = $customSuffix;
        }

        $nameParts = array_filter([
            $registration->first_name ?? $read('first_name'),
            $registration->middle_name ?? $read('middle_name'),
            $registration->last_name ?? $read('last_name'),
            $suffix !== '' ? $suffix : null,
        ], fn ($part) => filled($part));

        $signature = $formData['signature'] ?? null;
        $hasSignatureImage = is_string($signature) && str_starts_with($signature, 'data:image');

        $location = KabataanLocationResolver::forRegistration($registration);

        return [
            'respondentNumber' => RespondentNumberService::displaySequence(
                $registration->respondent_sequence,
                $registration->respondent_number,
            ),
            'date' => $submittedAt?->timezone(config('app.timezone', 'Asia/Manila'))->format('m/d/Y')
                ?? now(config('app.timezone', 'Asia/Manila'))->format('m/d/Y'),
            'barangayLogoUrl' => $barangayLogoUrl ?: asset('images/SK_OnePortal_logo.png'),
            'lastName' => strtoupper((string) ($registration->last_name ?? $read('last_name'))),
            'firstName' => strtoupper((string) ($registration->first_name ?? $read('first_name'))),
            'middleName' => strtoupper((string) ($registration->middle_name ?? $read('middle_name', ''))),
            'suffix' => $suffix,
            'region' => $location['region'] ?? $read('region', 'Region IV-A (CALABARZON)'),
            'province' => $location['province'] ?? $read('province', 'Laguna'),
            'city' => $location['city'] ?? $read('city', 'Santa Cruz'),
            'barangay' => $registration->barangay?->name ?? $read('barangay'),
            'purokZone' => $read('purok_zone'),
            'age' => $read('age'),
            'birthday' => (static function () use ($read) {
                $value = $read('birthday');
                if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
                    return $m[2] . '/' . $m[3] . '/' . $m[1];
                }

                return $value;
            })(),
            'email' => $read('email', (string) $registration->email),
            'contactNumber' => $read('contact_number', (string) ($registration->contact_number ?? '')),
            'facebook' => $read('facebook_profile_url') ?: $read('facebook'),
            'signatureImage' => $hasSignatureImage ? $signature : null,
            'signatureName' => implode(' ', $nameParts),
            'isChecked' => $isChecked,
        ];
    }
}
