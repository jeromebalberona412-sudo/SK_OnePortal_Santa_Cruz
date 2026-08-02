<?php

namespace App\Modules\Program_Accomplishment\Services;

use App\Models\ProgramAccomplishmentImage;
use App\Models\ProgramAccomplishmentReport;
use App\Models\ScheduleProgram;
use App\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProgramAccomplishmentService
{
    public function __construct(
        private readonly CloudinaryService $cloudinaryService
    ) {}

    /**
     * Get completed programs for a barangay
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getCompletedPrograms(int $barangayId): Collection
    {
        return ScheduleProgram::query()
            ->where('barangay_id', $barangayId)
            ->where('status', 'Completed')
            ->with(['barangay', 'creator'])
            ->orderByDesc('end_date')
            ->get()
            ->map(fn (ScheduleProgram $program) => $this->formatProgram($program));
    }

    /**
     * Get accomplishment reports for a barangay
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getAccomplishmentReports(int $barangayId): Collection
    {
        return ProgramAccomplishmentReport::query()
            ->where('barangay_id', $barangayId)
            ->with(['program', 'barangay', 'creator', 'images'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ProgramAccomplishmentReport $report) => $this->formatReport($report));
    }

    /**
     * Get all images for accomplishment reports
     *
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
     * Get a specific accomplishment report by ID
     *
     * @return array<string, mixed>
     */
    public function getReportById(int $reportId): array
    {
        $report = ProgramAccomplishmentReport::query()
            ->with(['program', 'barangay', 'creator', 'images' => fn ($query) => $query->ordered()])
            ->findOrFail($reportId);

        return $this->formatReport($report);
    }

    /**
     * Get a published accomplishment report by ID (for public view)
     *
     * @return array<string, mixed>
     */
    public function getPublishedById(int $reportId): array
    {
        $report = ProgramAccomplishmentReport::query()
            ->published()
            ->with(['program', 'barangay', 'creator', 'images' => fn ($query) => $query->ordered()])
            ->findOrFail($reportId);

        return $this->formatReport($report);
    }

    /**
     * Create a new accomplishment report
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createReport(int $barangayId, int $userId, array $data): array
    {
        $program = ScheduleProgram::query()
            ->where('id', $data['program_id'])
            ->where('barangay_id', $barangayId)
            ->where('status', 'Completed')
            ->firstOrFail();

        // Check if report already exists for this program
        $existingReport = ProgramAccomplishmentReport::query()
            ->where('program_id', $data['program_id'])
            ->first();

        if ($existingReport) {
            throw ValidationException::withMessages([
                'program_id' => ['An accomplishment report already exists for this program.'],
            ]);
        }

        // Validate budget
        $actualExpense = (float) ($data['actual_expense'] ?? 0);
        $budgetAllocated = (float) ($program->participation_quantity ?? 0); // Using participation_quantity as budget

        if ($actualExpense > $budgetAllocated) {
            throw ValidationException::withMessages([
                'actual_expense' => ['Actual expense cannot exceed budget allocated.'],
            ]);
        }

        return DB::transaction(function () use ($program, $userId, $data, $actualExpense) {
            $report = ProgramAccomplishmentReport::create([
                'tenant_id' => $program->tenant_id ?? null,
                'barangay_id' => $program->barangay_id,
                'program_id' => $program->id,
                'created_by' => $userId,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'objectives' => $data['objectives'] ?? null,
                'implementation_summary' => $data['implementation_summary'],
                'lessons_learned' => $data['lessons_learned'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
                'participants_count' => (int) ($data['participants_count'] ?? 0),
                'actual_expense' => $actualExpense,
                'remarks' => $data['remarks'] ?? null,
                'status' => 'Unpublished',
            ]);

            // Upload images if provided
            if (isset($data['images']) && is_array($data['images'])) {
                $this->uploadImages($report, $data['images']);
            }

            return $this->formatReport($report->fresh(['program', 'barangay', 'creator', 'images']));
        });
    }

    /**
     * Update an existing accomplishment report
     *
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

        if (!$report->isEditable()) {
            throw ValidationException::withMessages([
                'report' => ['This report cannot be edited.'],
            ]);
        }

        // Validate budget
        $actualExpense = (float) ($data['actual_expense'] ?? $report->actual_expense);
        $budgetAllocated = (float) ($report->program?->participation_quantity ?? 0);

        if ($actualExpense > $budgetAllocated) {
            throw ValidationException::withMessages([
                'actual_expense' => ['Actual expense cannot exceed budget allocated.'],
            ]);
        }

        return DB::transaction(function () use ($report, $data, $actualExpense) {
            $report->update([
                'title' => $data['title'] ?? $report->title,
                'description' => $data['description'] ?? $report->description,
                'objectives' => $data['objectives'] ?? $report->objectives,
                'implementation_summary' => $data['implementation_summary'] ?? $report->implementation_summary,
                'lessons_learned' => $data['lessons_learned'] ?? $report->lessons_learned,
                'recommendations' => $data['recommendations'] ?? $report->recommendations,
                'participants_count' => (int) ($data['participants_count'] ?? $report->participants_count),
                'actual_expense' => $actualExpense,
                'remarks' => $data['remarks'] ?? $report->remarks,
            ]);

            // Handle new images
            if (isset($data['new_images']) && is_array($data['new_images'])) {
                $this->uploadImages($report, $data['new_images']);
            }

            // Handle image deletions
            if (isset($data['delete_images']) && is_array($data['delete_images'])) {
                $this->deleteImages($report, $data['delete_images']);
            }

            // Handle image updates (captions, sort order)
            if (isset($data['update_images']) && is_array($data['update_images'])) {
                $this->updateImageMetadata($data['update_images']);
            }

            return $this->formatReport($report->fresh(['program', 'barangay', 'creator', 'images']));
        });
    }

    /**
     * Delete an accomplishment report
     */
    public function deleteReport(int $reportId, int $barangayId): void
    {
        $report = ProgramAccomplishmentReport::query()
            ->where('id', $reportId)
            ->where('barangay_id', $barangayId)
            ->firstOrFail();

        if (!$report->isEditable()) {
            throw ValidationException::withMessages([
                'report' => ['This report cannot be deleted.'],
            ]);
        }

        DB::transaction(function () use ($report) {
            // Delete all associated images from Cloudinary
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

            // Delete the report (images will be cascade deleted)
            $report->delete();
        });
    }

    /**
     * Upload images for a report
     *
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
            if (!$image instanceof UploadedFile) {
                continue;
            }

            // Validate image
            $this->validateImage($image);

            try {
                $publicId = "program_accomplishment_{$report->id}_".time()."_{$index}";
                $result = $this->cloudinaryService->upload($image, $publicId);

                ProgramAccomplishmentImage::create([
                    'accomplishment_report_id' => $report->id,
                    'cloudinary_public_id' => $result['public_id'],
                    'image_url' => $result['url'],
                    'secure_url' => $result['url'],
                    'display_name' => $image->getClientOriginalName(),
                    'caption' => null,
                    'sort_order' => $currentCount + $index + 1,
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
     * Delete specific images
     *
     * @param  array<int>  $imageIds
     */
    private function deleteImages(ProgramAccomplishmentReport $report, array $imageIds): void
    {
        $images = $report->images()
            ->whereIn('id', $imageIds)
            ->get();

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
     * Update image metadata (captions, sort order)
     *
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
     * Validate an uploaded image
     */
    private function validateImage(UploadedFile $image): void
    {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($image->getMimeType(), $allowedTypes, true)) {
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

    /**
     * Format program for API response
     *
     * @return array<string, mixed>
     */
    private function formatProgram(ScheduleProgram $program): array
    {
        return [
            'id' => $program->id,
            'program_name' => $program->program_name,
            'program_type' => $program->program_type,
            'committee' => $program->committee,
            'start_date' => $program->start_date?->format('Y-m-d'),
            'end_date' => $program->end_date?->format('Y-m-d'),
            'status' => $program->status,
            'barangay_id' => $program->barangay_id,
            'barangay' => $program->barangay?->name,
            'participation_quantity' => $program->participation_quantity, // Using as budget
            'created_by' => $program->created_by,
            'creator' => $program->creator?->name,
            'created_at' => $program->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Format report for API response
     *
     * @return array<string, mixed>
     */
    private function formatReport(ProgramAccomplishmentReport $report): array
    {
        return [
            'id' => $report->id,
            'program_id' => $report->program_id,
            'title' => $report->title,
            'description' => $report->description,
            'objectives' => $report->objectives,
            'implementation_summary' => $report->implementation_summary,
            'lessons_learned' => $report->lessons_learned,
            'recommendations' => $report->recommendations,
            'participants_count' => $report->participants_count,
            'actual_expense' => (float) $report->actual_expense,
            'remarks' => $report->remarks,
            'status' => $report->status,
            'remaining_budget' => $report->remaining_budget,
            'budget_utilization_percent' => $report->budget_utilization_percent,
            'program' => $report->program ? $this->formatProgram($report->program) : null,
            'barangay' => $report->barangay?->name,
            'creator' => $report->creator?->name,
            'images' => $report->images->map(fn ($image) => $this->formatImage($image))->values(),
            'created_at' => $report->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $report->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Format image for API response
     *
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
}