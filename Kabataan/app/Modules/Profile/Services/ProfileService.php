<?php

namespace App\Modules\Profile\Services;

use App\Models\Barangay;
use App\Models\KabataanRegistration;
use App\Models\User;
use App\Modules\KKProfiling\Controllers\KKProfilingController;

class ProfileService
{
    /**
     * @return array{
     *     fullName: string,
     *     barangayName: string,
     *     municipality: string,
     *     province: string,
     *     region: string,
     *     barangayLogoUrl: ?string,
     *     registration: ?KabataanRegistration
     * }
     */
    public function getDisplayData(User $user): array
    {
        $registration = KabataanRegistration::with('barangay')
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereNotNull('submitted_at')
                    ->orWhereNotNull('form_data');
            })
            ->latest('id')
            ->first();

        $formData = $registration?->form_data ?? [];
        $barangay = $registration?->barangay;

        if ($barangay === null && $user->barangay_id) {
            $barangay = Barangay::query()->find($user->barangay_id);
        }

        $barangayName = $barangay?->name
            ?? ($formData['barangay'] ?? null)
            ?? 'Santa Cruz';

        $barangayId = $registration?->barangay_id ?? $user->barangay_id;

        return [
            'fullName' => mb_strtoupper($this->buildFullName($registration, $user)),
            'barangayName' => $barangayName,
            'municipality' => $barangay?->municipality ?? 'Santa Cruz',
            'province' => $barangay?->province ?? 'Laguna',
            'region' => $barangay?->region ?? 'Region IV-A (CALABARZON)',
            'barangayLogoUrl' => KKProfilingController::getBarangayLogoUrl($barangayId),
            'registration' => $registration,
        ];
    }

    protected function buildFullName(?KabataanRegistration $registration, User $user): string
    {
        if ($registration !== null) {
            $formData = $registration->form_data ?? [];

            $last = trim((string) ($formData['last_name'] ?? $registration->last_name ?? ''));
            $first = trim((string) ($formData['first_name'] ?? $registration->first_name ?? ''));
            $middle = trim((string) ($formData['middle_name'] ?? $registration->middle_name ?? ''));
            $suffix = trim((string) ($formData['suffix'] ?? $registration->suffix ?? ''));
            if (strcasecmp($suffix, 'None') === 0) {
                $suffix = '';
            }

            $parts = array_filter([$last, $first, $middle, $suffix], fn ($part) => $part !== '');

            if ($parts !== []) {
                if ($last !== '' && $first !== '') {
                    $name = $last.', '.$first;
                    if ($middle !== '') {
                        $name .= ' '.$middle;
                    }
                    if ($suffix !== '') {
                        $name .= ' '.$suffix;
                    }

                    return $name;
                }

                return implode(' ', $parts);
            }
        }

        return trim((string) ($user->name ?? ''));
    }
}
