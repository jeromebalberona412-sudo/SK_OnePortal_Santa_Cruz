<?php

namespace App\Services;

use App\Models\ProgramAccomplishmentReport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PublicProgramAccomplishmentService
{
    /**
     * @return Collection<int, ProgramAccomplishmentReport>
     */
    public function publishedForBarangay(int $barangayId): Collection
    {
        if (! Schema::hasTable('programs_accomplishment_reports')) {
            return collect();
        }

        $relations = ['program'];

        if (Schema::hasTable('programs_accomplishment')) {
            $relations[] = 'images';
        }

        if (Schema::hasTable('programs_accomplishment_documents')) {
            $relations[] = 'documents';
        }

        return ProgramAccomplishmentReport::query()
            ->publiclyVisible()
            ->where('barangay_id', $barangayId)
            ->with($relations)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return list<int>
     */
    public function barangayIdsWithPublishedReports(): array
    {
        if (! Schema::hasTable('programs_accomplishment_reports')) {
            return [];
        }

        return ProgramAccomplishmentReport::query()
            ->publiclyVisible()
            ->distinct()
            ->pluck('barangay_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
