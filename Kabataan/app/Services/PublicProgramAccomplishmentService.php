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

    public function categoryForReport(ProgramAccomplishmentReport $report): string
    {
        $committee = trim((string) ($report->program?->committee ?? ''));
        if ($committee !== '') {
            return $committee;
        }

        $type = trim((string) ($report->program?->program_type ?? ''));
        if ($type !== '' && strcasecmp($type, 'other') !== 0) {
            return ucwords(str_replace(['-', '_'], ' ', $type));
        }

        $letter = strtoupper(trim((string) ($report->program?->program_letter ?? '')));

        return match ($letter) {
            'A' => 'Education',
            'B' => 'Environment',
            'C' => 'Disaster',
            'D' => 'Livelihood',
            'E' => 'Health',
            'F' => 'Anti-Drug',
            'G' => 'Gender',
            'H' => 'Feeding',
            'I' => 'Sports',
            default => 'Other',
        };
    }

    public function durationLabel($start, $end): string
    {
        if ($start === null || $end === null) {
            return '—';
        }

        $months = max(0, $start->diffInMonths($end));
        if ($months < 1) {
            $days = max(1, $start->diffInDays($end));

            return $days === 1 ? '1 Day' : $days.' Days';
        }

        if ($months < 12) {
            return $months === 1 ? '1 Month' : $months.' Months';
        }

        $years = max(1, (int) round($months / 12));

        return $years === 1 ? '1 Year' : $years.' Years';
    }

    public function publishedByIdForBarangay(int $barangayId, int $reportId): ?ProgramAccomplishmentReport
    {
        if (! Schema::hasTable('programs_accomplishment_reports')) {
            return null;
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
            ->where('id', $reportId)
            ->with($relations)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function toPublicCard(ProgramAccomplishmentReport $report, string $barangayName): array
    {
        $images = $report->relationLoaded('images') ? $report->images : collect();
        $cover = $images->first();
        $start = $report->program?->start_date;
        $end = $report->program?->end_date ?? $report->actual_completion_date;
        $displayDate = $report->actual_completion_date
            ?: $report->published_at
            ?: $report->program?->end_date;
        $year = $displayDate?->year
            ?? $report->program?->end_date?->year
            ?? (int) date('Y');

        $photos = $images->map(fn ($image) => [
            'src' => $image->secure_url ?: $image->image_url,
        ])->filter(fn ($photo) => filled($photo['src']))->values();

        $docs = ($report->relationLoaded('documents') ? $report->documents : collect())
            ->filter(fn ($document) => $document->visibility === 'public' && filled($document->stored_path))
            ->map(function ($document) {
                $name = (string) $document->original_name;
                $ext = strtoupper((string) pathinfo($name, PATHINFO_EXTENSION));

                return [
                    'name' => $name,
                    'url' => asset('storage/'.$document->stored_path),
                    'type' => $ext !== '' ? $ext : 'FILE',
                    'size' => $this->formatFileSize((int) ($document->file_size ?? 0)),
                ];
            })
            ->values();

        return [
            'id' => $report->id,
            'title' => $report->program?->program_name ?? $report->title,
            'description' => $report->description ?: $report->implementation_summary,
            'category' => $this->categoryForReport($report),
            'status' => 'Completed',
            'date_label' => $displayDate?->format('M d, Y') ?? '—',
            'published_label' => $report->published_at?->format('M d, Y'),
            'implementation_label' => $report->actual_implementation_date?->format('M d, Y'),
            'sort_date' => optional($displayDate)->toDateString() ?? '',
            'year' => (string) $year,
            'location' => $barangayName,
            'duration' => $this->durationLabel($start, $end),
            'committee' => $report->program?->committee ?: $this->categoryForReport($report),
            'beneficiaries' => (int) ($report->participants_count ?? 0),
            'target_beneficiaries' => (int) ($report->target_beneficiaries ?? 0),
            'expenditure' => (float) $report->actual_expense,
            'approved_budget' => $report->plannedBudget(),
            'remaining' => $report->remainingBudget(),
            'objectives' => $report->objectives,
            'summary' => $report->implementation_summary,
            'actual_result' => $report->actual_result,
            'cover' => $cover ? ($cover->secure_url ?: $cover->image_url) : null,
            'photos' => $photos,
            'documents' => $docs,
        ];
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        return max(1, (int) round($bytes / 1024)).' KB';
    }
}
