<?php

namespace App\Services;

use App\Models\OfficialTerm;
use App\Models\User;

class ArchiveTermService
{
    /**
     * @return list<array{id: string, label: string, start_year: int, end_year: int, is_active: bool}>
     */
    public function termsForUser(?User $user): array
    {
        if (! $user?->barangay_id) {
            return $this->fallbackTerms();
        }

        return $this->termsForBarangay((int) $user->barangay_id);
    }

    /**
     * @return list<array{id: string, label: string, start_year: int, end_year: int, is_active: bool}>
     */
    public function termsForBarangay(int $barangayId): array
    {
        $rows = OfficialTerm::query()
            ->whereHas('officialProfile.user', function ($query) use ($barangayId) {
                $query->where('barangay_id', $barangayId)
                    ->where('role', User::ROLE_SK_OFFICIAL);
            })
            ->orderByDesc('term_start')
            ->get(['term_start', 'term_end', 'status']);

        $unique = [];

        foreach ($rows as $row) {
            if ($row->term_start === null || $row->term_end === null) {
                continue;
            }

            $id = $row->term_start->format('Y').'-'.$row->term_end->format('Y');

            if (isset($unique[$id])) {
                if ($row->status === OfficialTerm::STATUS_ACTIVE) {
                    $unique[$id]['is_active'] = true;
                }

                continue;
            }

            $unique[$id] = [
                'id' => $id,
                'label' => 'SK Term '.$row->term_start->format('Y').'–'.$row->term_end->format('Y'),
                'start_year' => (int) $row->term_start->format('Y'),
                'end_year' => (int) $row->term_end->format('Y'),
                'is_active' => $row->status === OfficialTerm::STATUS_ACTIVE,
            ];
        }

        $terms = array_values($unique);

        return $terms !== [] ? $terms : $this->fallbackTerms();
    }

    /**
     * @param  list<array{id: string, label: string, start_year: int, end_year: int, is_active: bool}>  $terms
     */
    public function activeTermId(array $terms): string
    {
        foreach ($terms as $term) {
            if ($term['is_active']) {
                return $term['id'];
            }
        }

        return $terms[0]['id'] ?? $this->fallbackTerms()[0]['id'];
    }

    /**
     * @return list<array{id: string, label: string, start_year: int, end_year: int, is_active: bool}>
     */
    private function fallbackTerms(): array
    {
        $currentYear = (int) now()->year;
        $endYear = $currentYear + 2;
        $id = $currentYear.'-'.$endYear;

        return [[
            'id' => $id,
            'label' => 'SK Term '.$currentYear.'–'.$endYear,
            'start_year' => $currentYear,
            'end_year' => $endYear,
            'is_active' => true,
        ]];
    }
}
