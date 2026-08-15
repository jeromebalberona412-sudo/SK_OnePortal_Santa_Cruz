<?php

namespace App\Modules\Program_Accomplishment\Services;

use App\Models\Abyip;
use App\Models\ProgramAccomplishmentDocument;
use App\Models\ProgramAccomplishmentImage;
use App\Models\ProgramAccomplishmentReport;
use App\Models\ScheduleProgram;
use App\Models\User;
use App\Modules\Programs\Services\AbyipProgramCatalogService;
use App\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProgramAccomplishmentService
{
    public const CLOUDINARY_FOLDER = 'Program_Accomplishment';

    /** @var array<int, array<string, array<string, mixed>>> */
    private array $abyipMetaCache = [];

    public function __construct(
        private readonly CloudinaryService $cloudinaryService,
        private readonly AbyipProgramCatalogService $catalogService,
    ) {}

    /**
     * Completed Programs from the Programs catalog (ABYIP duration status).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getCompletedPrograms(int $barangayId, User $user): Collection
    {
        $catalog = $this->catalogService->listForProgramsPage($user);
        $withMeta = $this->attachCatalogAccomplishmentMeta($catalog['programs'] ?? [], $barangayId);
        $abyipMeta = $this->abyipMetaByLetter($barangayId);

        return collect($withMeta)
            ->filter(fn (array $program) => ($program['status'] ?? '') === AbyipProgramCatalogService::STATUS_COMPLETED)
            ->map(function (array $program) use ($barangayId, $abyipMeta) {
                $letter = strtoupper(trim((string) ($program['letter'] ?? '')));
                $meta = $abyipMeta[$letter] ?? [];
                $approvedBudget = (float) ($program['total'] ?? 0);
                if ($approvedBudget <= 0) {
                    $approvedBudget = $this->catalogService->resolveProgramTotalForBarangay(
                        $barangayId,
                        $letter,
                        (string) ($program['title'] ?? ''),
                        isset($program['id']) ? (int) $program['id'] : null,
                    );
                }

                return [
                    'id' => $program['schedule_program_id'] ?? null,
                    'abyip_program_id' => $program['id'],
                    'program_name' => $program['title'] ?? '',
                    'program_type' => $program['committee'] ?? ($program['title'] ?? ''),
                    'committee' => $program['committee'] ?? '',
                    'category' => $meta['category'] ?? ($program['committee'] ?? null),
                    'description' => $program['description'] ?? ($meta['description'] ?? null),
                    'expected_result' => $meta['expected_result'] ?? null,
                    'performance_indicator' => $meta['performance_indicator'] ?? null,
                    'person_responsible' => $meta['person_responsible'] ?? ($program['sk_head_display'] ?? null),
                    'start_date' => $program['startDate'] ?? $program['start_date'] ?? null,
                    'end_date' => $program['endDate'] ?? $program['end_date'] ?? null,
                    'status' => 'Completed',
                    'barangay_id' => $barangayId,
                    'participation_quantity' => $approvedBudget,
                    'approved_budget' => $approvedBudget,
                    'total' => $approvedBudget,
                    'creator' => $program['sk_head_display'] ?? '',
                    'accomplishment_report_id' => $program['accomplishment_report_id'] ?? null,
                    'can_create_accomplishment' => (bool) ($program['can_create_accomplishment'] ?? false),
                ];
            })
            ->values();
    }

    /**
     * @param  list<array<string, mixed>>  $catalogPrograms
     * @return list<array<string, mixed>>
     */
    public function attachCatalogAccomplishmentMeta(array $catalogPrograms, int $barangayId): array
    {
        $schedules = ScheduleProgram::query()
            ->where('barangay_id', $barangayId)
            ->get();

        $reports = ProgramAccomplishmentReport::query()
            ->where('barangay_id', $barangayId)
            ->get()
            ->keyBy('program_id');

        return array_map(function (array $program) use ($schedules, $reports) {
            $letter = strtoupper(trim((string) ($program['letter'] ?? '')));
            $title = mb_strtolower(trim((string) ($program['title'] ?? '')), 'UTF-8');

            $match = $schedules->first(function (ScheduleProgram $schedule) use ($letter, $title) {
                $scheduleLetter = strtoupper(trim((string) $schedule->program_letter));
                $scheduleName = mb_strtolower(trim((string) $schedule->program_name), 'UTF-8');

                return ($letter !== '' && $scheduleLetter === $letter)
                    || ($title !== '' && $scheduleName === $title);
            });

            $report = $match ? $reports->get($match->id) : null;
            $completed = ($program['status'] ?? '') === AbyipProgramCatalogService::STATUS_COMPLETED;

            $program['schedule_program_id'] = $match?->id;
            $program['accomplishment_report_id'] = $report?->id;
            $program['accomplishment_status'] = $report?->status;
            $program['can_create_accomplishment'] = $completed && $report === null;

            return $program;
        }, $catalogPrograms);
    }

    /**
     * @return array<string, mixed>
     */
    public function prepareFromCatalog(int $barangayId, int $userId, int $abyipProgramId): array
    {
        if (! $this->catalogService->programBelongsToBarangay($barangayId, $abyipProgramId)) {
            throw ValidationException::withMessages([
                'program_id' => ['Program not found for your barangay.'],
            ]);
        }

        $abyip = Abyip::query()->findOrFail($abyipProgramId);
        $fiscalYear = (int) ($abyip->fiscal_year ?: now()->year);
        $duration = $this->catalogService->resolveProgramDuration($barangayId, $abyipProgramId, $fiscalYear);
        $status = $this->catalogService->resolveProgramStatus($duration['start_date'], $duration['end_date']);

        if ($status !== AbyipProgramCatalogService::STATUS_COMPLETED) {
            throw ValidationException::withMessages([
                'program_id' => ['A Program Accomplishment can only be created for a completed program.'],
            ]);
        }

        $letter = strtoupper(trim((string) ($abyip->program_letter ?? $abyip->code ?? '')));
        $name = trim((string) $abyip->program_name);

        $schedule = ScheduleProgram::query()
            ->where('barangay_id', $barangayId)
            ->where(function ($query) use ($letter, $name) {
                if ($letter !== '') {
                    $query->where('program_letter', $letter);
                }
                $query->orWhereRaw('LOWER(program_name) = ?', [mb_strtolower($name, 'UTF-8')]);
            })
            ->first();

        if ($schedule === null) {
            $schedule = ScheduleProgram::query()->create([
                'barangay_id' => $barangayId,
                'created_by' => $userId,
                'program_type' => $name,
                'committee' => $name,
                'program_name' => $name,
                'program_letter' => $letter !== '' ? $letter : null,
                'participation_quantity' => (int) round($this->catalogService->resolveProgramTotal($abyip)),
                'start_date' => $duration['start_date'],
                'end_date' => $duration['end_date'],
                'status' => 'Completed',
            ]);
        } elseif (strcasecmp((string) $schedule->status, 'Completed') !== 0) {
            $schedule->update(['status' => 'Completed']);
        }

        $existing = ProgramAccomplishmentReport::query()
            ->where('program_id', $schedule->id)
            ->first();

        return [
            'schedule_program_id' => $schedule->id,
            'accomplishment_report_id' => $existing?->id,
            'can_create' => $existing === null,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getAccomplishmentReports(int $barangayId): Collection
    {
        return ProgramAccomplishmentReport::query()
            ->where('barangay_id', $barangayId)
            ->with(['program', 'barangay', 'creator', 'images', 'documents'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ProgramAccomplishmentReport $report) => $this->formatReport($report));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getAllImages(int $barangayId): Collection
    {
        return ProgramAccomplishmentImage::query()
            ->whereHas('accomplishmentReport', fn ($query) => $query->where('barangay_id', $barangayId))
            ->with('accomplishmentReport')
            ->get()
            ->map(fn (ProgramAccomplishmentImage $image) => $this->formatImage($image));
    }

    /**
     * @return array<string, mixed>
     */
    public function getReportById(int $reportId): array
    {
        $report = ProgramAccomplishmentReport::query()
            ->with([
                'program',
                'barangay',
                'creator',
                'images' => fn ($query) => $query->ordered(),
                'documents',
            ])
            ->findOrFail($reportId);

        return $this->formatReport($report);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPublishedById(int $reportId): array
    {
        $report = ProgramAccomplishmentReport::query()
            ->published()
            ->with([
                'program',
                'barangay',
                'creator',
                'images' => fn ($query) => $query->ordered(),
                'documents',
            ])
            ->findOrFail($reportId);

        $formatted = $this->formatReport($report);
        $formatted['documents'] = collect($formatted['documents'])
            ->filter(fn (array $doc) => ($doc['visibility'] ?? '') === ProgramAccomplishmentDocument::VISIBILITY_PUBLIC)
            ->values()
            ->all();

        return $formatted;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createReport(int $barangayId, int $userId, array $data): array
    {
        $program = $this->completedProgramForBarangay((int) $data['program_id'], $barangayId);

        $existingReport = ProgramAccomplishmentReport::query()
            ->where('program_id', $program->id)
            ->first();

        if ($existingReport) {
            throw ValidationException::withMessages([
                'program_id' => ['An accomplishment report already exists for this program.'],
            ]);
        }

        $approvedBudget = $this->resolveApprovedBudget($program, $data);
        $actualExpense = (float) ($data['actual_expense'] ?? 0);
        $this->assertExpenseWithinBudget($actualExpense, $approvedBudget);

        $status = ProgramAccomplishmentReport::STATUS_PUBLISHED;

        return DB::transaction(function () use ($program, $userId, $data, $actualExpense, $approvedBudget, $status) {
            $report = ProgramAccomplishmentReport::create([
                'tenant_id' => $program->tenant_id ?? null,
                'barangay_id' => $program->barangay_id,
                'program_id' => $program->id,
                'created_by' => $userId,
                'title' => $data['title'] ?? $program->program_name,
                'description' => $data['description'] ?? null,
                'objectives' => $data['objectives'] ?? null,
                'implementation_summary' => $data['implementation_summary'] ?? null,
                'actual_result' => $data['actual_result'] ?? null,
                'lessons_learned' => $data['lessons_learned'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
                'participants_count' => (int) ($data['participants_count'] ?? 0),
                'target_beneficiaries' => isset($data['target_beneficiaries']) ? (int) $data['target_beneficiaries'] : null,
                'actual_expense' => $actualExpense,
                'approved_budget' => $approvedBudget,
                'actual_implementation_date' => $data['actual_implementation_date'] ?? null,
                'actual_completion_date' => $data['actual_completion_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => $status,
                'submitted_at' => now(),
                'published_at' => now(),
            ]);

            if (isset($data['images']) && is_array($data['images'])) {
                $this->uploadImages($report, $data['images']);
            }

            if (isset($data['documents']) && is_array($data['documents'])) {
                $this->storeDocuments($report, $data['documents'], $data['document_types'] ?? []);
            }

            return $this->formatReport($report->fresh(['program', 'barangay', 'creator', 'images', 'documents']));
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateReport(int $reportId, int $barangayId, array $data): array
    {
        $report = ProgramAccomplishmentReport::query()
            ->where('id', $reportId)
            ->where('barangay_id', $barangayId)
            ->with('program')
            ->firstOrFail();

        if (! $report->isEditable()) {
            throw ValidationException::withMessages([
                'report' => ['This report cannot be edited.'],
            ]);
        }

        $approvedBudget = $this->resolveApprovedBudget($report->program, $data, $report);
        $actualExpense = (float) ($data['actual_expense'] ?? $report->actual_expense);
        $this->assertExpenseWithinBudget($actualExpense, $approvedBudget);

        $status = ProgramAccomplishmentReport::STATUS_PUBLISHED;
        $submittedAt = $report->submitted_at ?? now();

        return DB::transaction(function () use ($report, $data, $actualExpense, $approvedBudget, $status, $submittedAt) {
            $report->update([
                'title' => $data['title'] ?? $report->title,
                'description' => $data['description'] ?? $report->description,
                'objectives' => $data['objectives'] ?? $report->objectives,
                'implementation_summary' => $data['implementation_summary'] ?? $report->implementation_summary,
                'actual_result' => $data['actual_result'] ?? $report->actual_result,
                'lessons_learned' => $data['lessons_learned'] ?? $report->lessons_learned,
                'recommendations' => $data['recommendations'] ?? $report->recommendations,
                'participants_count' => (int) ($data['participants_count'] ?? $report->participants_count),
                'target_beneficiaries' => array_key_exists('target_beneficiaries', $data)
                    ? (int) $data['target_beneficiaries']
                    : $report->target_beneficiaries,
                'actual_expense' => $actualExpense,
                'approved_budget' => $approvedBudget,
                'actual_implementation_date' => $data['actual_implementation_date'] ?? $report->actual_implementation_date,
                'actual_completion_date' => $data['actual_completion_date'] ?? $report->actual_completion_date,
                'remarks' => $data['remarks'] ?? $report->remarks,
                'status' => $status,
                'submitted_at' => $submittedAt,
                'published_at' => $report->published_at ?? now(),
                'rejection_reason' => null,
            ]);

            if (isset($data['new_images']) && is_array($data['new_images'])) {
                $this->uploadImages($report, $data['new_images']);
            }

            if (isset($data['delete_images']) && is_array($data['delete_images'])) {
                $this->deleteImages($report, $data['delete_images']);
            }

            if (isset($data['update_images']) && is_array($data['update_images'])) {
                $this->updateImageMetadata($data['update_images']);
            }

            if (isset($data['documents']) && is_array($data['documents'])) {
                $this->storeDocuments($report, $data['documents'], $data['document_types'] ?? []);
            }

            if (isset($data['delete_documents']) && is_array($data['delete_documents'])) {
                $this->deleteDocuments($report, $data['delete_documents']);
            }

            return $this->formatReport($report->fresh(['program', 'barangay', 'creator', 'images', 'documents']));
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function publishReport(int $reportId, int $barangayId): array
    {
        $report = ProgramAccomplishmentReport::query()
            ->where('id', $reportId)
            ->where('barangay_id', $barangayId)
            ->firstOrFail();

        if (! $report->canPublish()) {
            throw ValidationException::withMessages([
                'report' => ['Only submitted reports can be published.'],
            ]);
        }

        $report->update([
            'status' => ProgramAccomplishmentReport::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        return $this->formatReport($report->fresh(['program', 'barangay', 'creator', 'images', 'documents']));
    }

    public function deleteReport(int $reportId, int $barangayId): void
    {
        $report = ProgramAccomplishmentReport::query()
            ->where('id', $reportId)
            ->where('barangay_id', $barangayId)
            ->firstOrFail();

        if (! $report->isEditable() || $report->isPublished()) {
            throw ValidationException::withMessages([
                'report' => ['This report cannot be deleted.'],
            ]);
        }

        DB::transaction(function () use ($report) {
            foreach ($report->images as $image) {
                try {
                    if ($image->cloudinary_public_id) {
                        $this->cloudinaryService->delete($image->cloudinary_public_id);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to delete Cloudinary image', [
                        'public_id' => $image->cloudinary_public_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            foreach ($report->documents as $document) {
                $disk = $document->isPublic() ? 'public' : 'local';
                Storage::disk($disk)->delete($document->stored_path);
            }

            $report->delete();
        });
    }

    /**
     * @param  array<UploadedFile>  $images
     */
    private function uploadImages(ProgramAccomplishmentReport $report, array $images): void
    {
        $currentCount = $report->images()->count();
        $maxImages = 50;

        if ($currentCount + count($images) > $maxImages) {
            throw ValidationException::withMessages([
                'images' => ["Maximum {$maxImages} images allowed per report."],
            ]);
        }

        foreach ($images as $index => $image) {
            if (! $image instanceof UploadedFile) {
                continue;
            }

            $this->validateImage($image);

            try {
                $result = $this->cloudinaryService->uploadToFolder(
                    $image,
                    self::CLOUDINARY_FOLDER,
                    $image->getClientOriginalName(),
                );

                ProgramAccomplishmentImage::create([
                    'accomplishment_report_id' => $report->id,
                    'cloudinary_public_id' => $result['public_id'],
                    'image_url' => $result['url'],
                    'secure_url' => $result['secure_url'] ?? $result['url'],
                    'display_name' => $result['display_name'] ?? $image->getClientOriginalName(),
                    'caption' => null,
                    'sort_order' => $currentCount + $index + 1,
                    'status' => ProgramAccomplishmentImage::STATUS_PUBLISHED,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to upload image to Cloudinary', [
                    'report_id' => $report->id,
                    'file_name' => $image->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'images' => ['Failed to upload image: '.$image->getClientOriginalName()],
                ]);
            }
        }
    }

    /**
     * @param  array<int>  $imageIds
     */
    private function deleteImages(ProgramAccomplishmentReport $report, array $imageIds): void
    {
        $images = $report->images()->whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            try {
                if ($image->cloudinary_public_id) {
                    $this->cloudinaryService->delete($image->cloudinary_public_id);
                }
                $image->delete();
            } catch (\Exception $e) {
                Log::error('Failed to delete image', [
                    'image_id' => $image->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $imageData
     */
    private function updateImageMetadata(array $imageData): void
    {
        foreach ($imageData as $data) {
            if (isset($data['id'])) {
                ProgramAccomplishmentImage::query()
                    ->where('id', $data['id'])
                    ->update([
                        'caption' => $data['caption'] ?? null,
                        'sort_order' => $data['sort_order'] ?? 1,
                    ]);
            }
        }
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @param  array<int, string>  $types
     */
    private function storeDocuments(ProgramAccomplishmentReport $report, array $files, array $types): void
    {
        foreach ($files as $index => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $this->validateDocument($file);

            $path = $file->store('program_accomplishment_docs/'.$report->id, 'public');

            ProgramAccomplishmentDocument::create([
                'accomplishment_report_id' => $report->id,
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'document_type' => $types[$index] ?? 'other',
                'visibility' => ProgramAccomplishmentDocument::VISIBILITY_PUBLIC,
            ]);
        }
    }

    /**
     * @param  array<int>  $documentIds
     */
    private function deleteDocuments(ProgramAccomplishmentReport $report, array $documentIds): void
    {
        $documents = $report->documents()->whereIn('id', $documentIds)->get();

        foreach ($documents as $document) {
            $disk = $document->isPublic() ? 'public' : 'local';
            Storage::disk($disk)->delete($document->stored_path);
            $document->delete();
        }
    }

    private function validateImage(UploadedFile $image): void
    {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $maxSize = 10 * 1024 * 1024;

        if (! in_array($image->getMimeType(), $allowedTypes, true)) {
            throw ValidationException::withMessages([
                'images' => ['Invalid file type: '.$image->getClientOriginalName().'. Only JPG, JPEG, PNG, and WEBP are allowed.'],
            ]);
        }

        if ($image->getSize() > $maxSize) {
            throw ValidationException::withMessages([
                'images' => ['File too large: '.$image->getClientOriginalName().'. Maximum size is 10MB.'],
            ]);
        }
    }

    private function validateDocument(UploadedFile $file): void
    {
        $allowed = [
            'application/pdf',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $maxSize = 10 * 1024 * 1024;

        if (! in_array($file->getMimeType(), $allowed, true)) {
            throw ValidationException::withMessages([
                'documents' => ['Invalid document type: '.$file->getClientOriginalName()],
            ]);
        }

        if ($file->getSize() > $maxSize) {
            throw ValidationException::withMessages([
                'documents' => ['File too large: '.$file->getClientOriginalName().'. Maximum size is 10MB.'],
            ]);
        }
    }

    private function completedProgramForBarangay(int $programId, int $barangayId): ScheduleProgram
    {
        $program = ScheduleProgram::query()
            ->where('id', $programId)
            ->where('barangay_id', $barangayId)
            ->first();

        if ($program === null) {
            throw ValidationException::withMessages([
                'program_id' => ['Program not found for your barangay.'],
            ]);
        }

        $isCompleted = strcasecmp((string) $program->status, 'Completed') === 0
            || (
                $program->end_date
                && $program->end_date->lt(now()->startOfDay())
                && ! in_array(strtolower((string) $program->status), ['cancelled', 'canceled', 'rejected', 'draft'], true)
            );

        if (! $isCompleted) {
            throw ValidationException::withMessages([
                'program_id' => ['A Program Accomplishment can only be created when the program is Completed.'],
            ]);
        }

        return $program;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveApprovedBudget(ScheduleProgram $program, array $data, ?ProgramAccomplishmentReport $report = null): float
    {
        $fromAbyip = $this->catalogService->resolveProgramTotalForBarangay(
            (int) $program->barangay_id,
            (string) $program->program_letter,
            (string) $program->program_name,
        );

        if ($fromAbyip > 0) {
            return $fromAbyip;
        }

        if ($report?->approved_budget !== null && (float) $report->approved_budget > 0) {
            return (float) $report->approved_budget;
        }

        $abyipMeta = $this->abyipMetaByLetter((int) $program->barangay_id);
        $letter = strtoupper(trim((string) $program->program_letter));
        $fromMeta = (float) ($abyipMeta[$letter]['total'] ?? 0);

        if ($fromMeta > 0) {
            return $fromMeta;
        }

        return (float) ($program->participation_quantity ?? 0);
    }

    private function assertExpenseWithinBudget(float $actualExpense, float $approvedBudget): void
    {
        if ($actualExpense < 0) {
            throw ValidationException::withMessages([
                'actual_expense' => ['Actual expenditure cannot be negative.'],
            ]);
        }

        if ($approvedBudget > 0 && $actualExpense > $approvedBudget) {
            throw ValidationException::withMessages([
                'actual_expense' => ['Actual expenditure cannot exceed the approved budget.'],
            ]);
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function abyipMetaByLetter(int $barangayId): array
    {
        if (isset($this->abyipMetaCache[$barangayId])) {
            return $this->abyipMetaCache[$barangayId];
        }

        $document = $this->catalogService->getLatestApprovedAbyip($barangayId);
        if ($document === null) {
            return $this->abyipMetaCache[$barangayId] = [];
        }

        $meta = [];
        Abyip::query()
            ->where('document_id', $document->id)
            ->where(function ($query) {
                $query->where('row_type', Abyip::ROW_YOUTH_PROGRAM)
                    ->orWhereIn('code', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']);
            })
            ->with('children')
            ->get()
            ->each(function (Abyip $row) use (&$meta) {
                $letter = strtoupper(trim((string) ($row->program_letter ?? $row->code ?? '')));
                if ($letter === '') {
                    return;
                }
                $meta[$letter] = [
                    'description' => $row->description,
                    'expected_result' => $row->expected_result,
                    'performance_indicator' => $row->performance_indicator,
                    'person_responsible' => $row->person_responsible,
                    'category' => $row->category ?: $row->program_name,
                    'total' => $this->catalogService->resolveProgramTotal($row),
                    'objective' => $row->expected_result,
                ];
            });

        return $this->abyipMetaCache[$barangayId] = $meta;
    }

    /**
     * @param  array<string, array<string, mixed>>  $abyipMeta
     * @return array<string, mixed>
     */
    private function formatProgram(ScheduleProgram $program, array $abyipMeta = []): array
    {
        $letter = strtoupper(trim((string) $program->program_letter));
        $meta = $abyipMeta[$letter] ?? [];
        $approvedBudget = (float) ($meta['total'] ?? 0);
        if ($approvedBudget <= 0) {
            $approvedBudget = $this->catalogService->resolveProgramTotalForBarangay(
                (int) $program->barangay_id,
                $letter,
                (string) $program->program_name,
            );
        }
        if ($approvedBudget <= 0) {
            $approvedBudget = (float) ($program->participation_quantity ?? 0);
        }

        return [
            'id' => $program->id,
            'program_name' => $program->program_name,
            'program_type' => $program->program_type,
            'committee' => $program->committee,
            'category' => $meta['category'] ?? $program->program_type,
            'description' => $meta['description'] ?? null,
            'expected_result' => $meta['expected_result'] ?? null,
            'performance_indicator' => $meta['performance_indicator'] ?? null,
            'person_responsible' => $meta['person_responsible'] ?? $program->creator?->name,
            'start_date' => $program->start_date?->format('Y-m-d'),
            'end_date' => $program->end_date?->format('Y-m-d'),
            'status' => $program->status,
            'barangay_id' => $program->barangay_id,
            'barangay' => $program->barangay?->name,
            'participation_quantity' => $approvedBudget,
            'approved_budget' => $approvedBudget,
            'total' => $approvedBudget,
            'created_by' => $program->created_by,
            'creator' => $program->creator?->name,
            'created_at' => $program->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $program->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatReport(ProgramAccomplishmentReport $report): array
    {
        $abyipMeta = $report->program
            ? $this->abyipMetaByLetter((int) $report->barangay_id)
            : [];

        $programPayload = $report->program ? $this->formatProgram($report->program, $abyipMeta) : null;
        $approvedBudget = (float) ($programPayload['approved_budget'] ?? 0);
        if ($approvedBudget <= 0) {
            $approvedBudget = $report->plannedBudget();
        }
        $actualExpense = (float) $report->actual_expense;

        return [
            'id' => $report->id,
            'barangay_id' => $report->barangay_id,
            'program_id' => $report->program_id,
            'title' => $report->title,
            'description' => $report->description,
            'objectives' => $report->objectives,
            'implementation_summary' => $report->implementation_summary,
            'actual_result' => $report->actual_result,
            'lessons_learned' => $report->lessons_learned,
            'recommendations' => $report->recommendations,
            'participants_count' => $report->participants_count,
            'target_beneficiaries' => $report->target_beneficiaries,
            'actual_expense' => $actualExpense,
            'approved_budget' => $approvedBudget,
            'actual_implementation_date' => $report->actual_implementation_date?->format('Y-m-d'),
            'actual_completion_date' => $report->actual_completion_date?->format('Y-m-d'),
            'remarks' => $report->remarks,
            'status' => $report->status,
            'rejection_reason' => $report->rejection_reason,
            'remaining_budget' => max(0, $approvedBudget - $actualExpense),
            'budget_utilization_percent' => $approvedBudget > 0 ? ($actualExpense / $approvedBudget) * 100 : 0,
            'program' => $programPayload,
            'creator' => $report->creator?->name,
            'images' => $report->images->map(fn ($image) => $this->formatImage($image))->values(),
            'documents' => $report->documents->map(fn ($doc) => $this->formatDocument($doc))->values(),
            'created_at' => $report->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $report->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatImage(ProgramAccomplishmentImage $image): array
    {
        return [
            'id' => $image->id,
            'accomplishment_report_id' => $image->accomplishment_report_id,
            'cloudinary_public_id' => $image->cloudinary_public_id,
            'image_url' => $image->image_url,
            'secure_url' => $image->secure_url,
            'display_name' => $image->display_name,
            'caption' => $image->caption,
            'sort_order' => $image->sort_order,
            'created_at' => $image->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatDocument(ProgramAccomplishmentDocument $document): array
    {
        return [
            'id' => $document->id,
            'original_name' => $document->original_name,
            'document_type' => $document->document_type,
            'visibility' => $document->visibility,
            'file_size' => $document->file_size,
            'mime_type' => $document->mime_type,
            'public_url' => $document->stored_path ? asset('storage/'.$document->stored_path) : null,
        ];
    }
}
