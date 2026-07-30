<?php

namespace App\Modules\Program_Accomplishment\Services;

use App\Models\ProgramAccomplishmentReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgramAccomplishmentService
{
    public function __construct(
        private readonly BudgetComputationService $budgetService,
        private readonly ApprovalService $approvalService,
        private readonly PublishingService $publishingService,
    ) {}

    public function listForBarangay(int $barangayId, ?string $status = null, ?string $search = null, ?int $year = null): array
    {
        $query = ProgramAccomplishmentReport::forBarangay($barangayId)
            ->with('program')
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('accomplishment_status', $status);
        }

        if ($year) {
            $query->whereYear('date_completed', $year);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('venue', 'ilike', "%{$search}%");
            });
        }

        $reports = $query->paginate(15);

        return [
            'data' => $reports->items(),
            'total' => $reports->total(),
            'per_page' => $reports->perPage(),
            'current_page' => $reports->currentPage(),
            'last_page' => $reports->lastPage(),
        ];
    }

    public function findById(int $id): ?ProgramAccomplishmentReport
    {
        return ProgramAccomplishmentReport::with('program')
            ->findOrFail($id);
    }

    public function create(array $data): ProgramAccomplishmentReport
    {
        $errors = $this->budgetService->validate(
            (float) ($data['budget_allocated'] ?? 0),
            (float) ($data['actual_expense'] ?? 0)
        );

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode(' ', $errors));
        }

        return DB::transaction(function () use ($data) {
            $report = ProgramAccomplishmentReport::create([
                'tenant_id' => $data['tenant_id'] ?? null,
                'barangay_id' => $data['barangay_id'],
                'program_id' => $data['program_id'],
                'created_by' => $data['created_by'] ?? Auth::id(),
                'title' => $data['title'] ?? $data['program_name'] ?? 'Untitled',
                'description' => $data['description'] ?? null,
                'objectives' => $data['objectives'] ?? null,
                'implementation_summary' => $data['implementation_summary'] ?? null,
                'lessons_learned' => $data['lessons_learned'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
                'venue' => $data['venue'] ?? null,
                'person_responsible' => $data['person_responsible'] ?? null,
                'date_started' => $data['date_started'] ?? null,
                'date_completed' => $data['date_completed'] ?? null,
                'participants_count' => (int) ($data['participants_count'] ?? 0),
                'budget_allocated' => (float) ($data['budget_allocated'] ?? 0),
                'actual_expense' => (float) ($data['actual_expense'] ?? 0),
                'accomplishment_status' => 'Draft',
                'remarks' => $data['remarks'] ?? null,
                'image_name' => $data['image_name'] ?? null,
                'image_path' => $data['image_path'] ?? null,
                'image_type' => $data['image_type'] ?? null,
                'image_size' => $data['image_size'] ?? null,
                'image_caption' => $data['image_caption'] ?? null,
                'file_name' => $data['file_name'] ?? null,
                'file_path' => $data['file_path'] ?? null,
                'file_type' => $data['file_type'] ?? null,
                'file_size' => $data['file_size'] ?? null,
            ]);

            return $report->fresh();
        });
    }

    public function update(int $id, array $data): ProgramAccomplishmentReport
    {
        $report = $this->findById($id);

        if (!$report->isEditable()) {
            throw new \RuntimeException('Cannot edit a report that is not in Draft status.');
        }

        $allocated = (float) ($data['budget_allocated'] ?? $report->budget_allocated);
        $expense = (float) ($data['actual_expense'] ?? $report->actual_expense);

        $errors = $this->budgetService->validate($allocated, $expense);
        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode(' ', $errors));
        }

        return DB::transaction(function () use ($report, $data) {
            $report->update([
                'title' => $data['title'] ?? $report->title,
                'description' => $data['description'] ?? $report->description,
                'objectives' => $data['objectives'] ?? $report->objectives,
                'implementation_summary' => $data['implementation_summary'] ?? $report->implementation_summary,
                'lessons_learned' => $data['lessons_learned'] ?? $report->lessons_learned,
                'recommendations' => $data['recommendations'] ?? $report->recommendations,
                'venue' => $data['venue'] ?? $report->venue,
                'person_responsible' => $data['person_responsible'] ?? $report->person_responsible,
                'date_started' => $data['date_started'] ?? $report->date_started,
                'date_completed' => $data['date_completed'] ?? $report->date_completed,
                'participants_count' => (int) ($data['participants_count'] ?? $report->participants_count),
                'budget_allocated' => $allocated,
                'actual_expense' => $expense,
                'remarks' => $data['remarks'] ?? $report->remarks,
                'image_name' => $data['image_name'] ?? $report->image_name,
                'image_path' => $data['image_path'] ?? $report->image_path,
                'image_type' => $data['image_type'] ?? $report->image_type,
                'image_size' => $data['image_size'] ?? $report->image_size,
                'image_caption' => $data['image_caption'] ?? $report->image_caption,
                'file_name' => $data['file_name'] ?? $report->file_name,
                'file_path' => $data['file_path'] ?? $report->file_path,
                'file_type' => $data['file_type'] ?? $report->file_type,
                'file_size' => $data['file_size'] ?? $report->file_size,
            ]);

            return $report->fresh();
        });
    }

    public function delete(int $id): void
    {
        $report = $this->findById($id);

        if (!$report->isEditable()) {
            throw new \RuntimeException('Cannot delete a report that is not in Draft status.');
        }

        $report->delete();
    }

    public function submit(int $id): ProgramAccomplishmentReport
    {
        $report = $this->findById($id);

        if (!$report->isSubmittable()) {
            throw new \RuntimeException('Only draft reports can be submitted.');
        }

        $report->update([
            'accomplishment_status' => 'Submitted',
            'submitted_at' => Carbon::now(),
        ]);

        return $report->fresh();
    }

    public function approve(int $id): ProgramAccomplishmentReport
    {
        $report = $this->findById($id);
        return $this->approvalService->approve($report);
    }

    public function reject(int $id, ?string $reason = null): ProgramAccomplishmentReport
    {
        $report = $this->findById($id);
        return $this->approvalService->reject($report, $reason);
    }

    public function publish(int $id): ProgramAccomplishmentReport
    {
        $report = $this->findById($id);
        return $this->publishingService->publish($report);
    }

    public function unpublish(int $id): ProgramAccomplishmentReport
    {
        $report = $this->findById($id);
        return $this->publishingService->unpublish($report);
    }

    public function getPublished(int $barangayId, int $limit = 10): array
    {
        return ProgramAccomplishmentReport::forBarangay($barangayId)
            ->published()
            ->with('program')
            ->orderBy('published_at', 'desc')
            ->take($limit)
            ->get()
            ->toArray();
    }

    public function getPublishedById(int $id): ?ProgramAccomplishmentReport
    {
        return ProgramAccomplishmentReport::published()
            ->with(['program'])
            ->findOrFail($id);
    }
}
