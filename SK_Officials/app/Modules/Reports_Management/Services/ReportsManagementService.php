<?php

namespace App\Modules\Reports_Management\Services;

use App\Models\ReportManagement;
use App\Models\User;
use App\Modules\Programs\Services\AbyipProgramCatalogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsManagementService
{
    private const DISK = 'public';

    public function __construct(private readonly AbyipProgramCatalogService $catalogService) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function programsCatalog(?User $user = null): array
    {
        if ($user?->barangay_id) {
            $programs = $this->catalogService->listApprovedProgramsForBarangay((int) $user->barangay_id);
            if ($programs !== []) {
                return $programs;
            }
        }

        return [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForBarangay(int $barangayId, ?string $search = null, ?string $programCode = null): array
    {
        $query = ReportManagement::query()
            ->with('barangay:id,name')
            ->forBarangay($barangayId)
            ->orderByDesc('created_at');

        if ($programCode) {
            $query->where('program_code', $programCode);
        }

        if ($search) {
            $term = '%'.$search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('program_name', 'ilike', $term)
                    ->orWhere('activity_name', 'ilike', $term)
                    ->orWhere('file_name', 'ilike', $term);
            });
        }

        return $query->get()->map(fn (ReportManagement $report) => $this->formatReport($report))->all();
    }

    public function upload(User $user, string $programCode, string $activityName, UploadedFile $file): ReportManagement
    {
        if (! $user->barangay_id) {
            throw new \RuntimeException('Barangay is required to upload reports.');
        }

        if (! $this->catalogService->hasApprovedAbyip((int) $user->barangay_id)) {
            throw new \RuntimeException('An approved ABYIP is required before uploading reports.');
        }

        $program = $this->findProgram($programCode, $user);
        if ($program === null) {
            throw new \InvalidArgumentException('Invalid program selected.');
        }

        if (! in_array($activityName, $program['activities'], true)) {
            throw new \InvalidArgumentException('Invalid activity selected.');
        }

        $barangay = DB::table('barangays')->where('id', $user->barangay_id)->first();
        if ($barangay === null) {
            throw new \RuntimeException('Barangay not found.');
        }

        $storedName = uniqid('report_', true).'.pdf';
        $directory = 'reports/barangay_'.$user->barangay_id;
        $path = $file->storeAs($directory, $storedName, self::DISK);

        return ReportManagement::create([
            'tenant_id' => (int) $barangay->tenant_id,
            'barangay_id' => (int) $user->barangay_id,
            'user_id' => (int) $user->id,
            'program_code' => $program['code'],
            'program_name' => $program['name'],
            'activity_name' => $activityName,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_mime' => 'application/pdf',
            'file_size' => $file->getSize(),
            'status' => 'pending',
        ]);
    }

    public function deleteForBarangay(int $barangayId, int $reportId): void
    {
        $report = ReportManagement::forBarangay($barangayId)->findOrFail($reportId);

        if (Storage::disk(self::DISK)->exists($report->file_path)) {
            Storage::disk(self::DISK)->delete($report->file_path);
        }

        $report->delete();
    }

    public function streamForBarangay(int $barangayId, int $reportId, bool $download = false): StreamedResponse
    {
        $report = ReportManagement::forBarangay($barangayId)->findOrFail($reportId);

        $disposition = $download ? 'attachment' : 'inline';

        return response()->file(
            $this->resolveAbsolutePath($report->file_path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => $disposition.'; filename="'.addslashes($report->file_name).'"',
            ]
        );
    }

    private function resolveAbsolutePath(string $path): string
    {
        if (Storage::disk(self::DISK)->exists($path)) {
            return Storage::disk(self::DISK)->path($path);
        }

        $roots = array_filter([
            env('REPORT_MANAGEMENT_STORAGE_ROOT'),
            realpath(base_path('../SK_Officials/storage/app/public')),
            realpath(base_path('../SK_Federations/storage/app/public')),
        ]);

        foreach ($roots as $root) {
            $candidate = rtrim((string) $root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        abort(404, 'Report file not found.');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findProgram(string $code, User $user): ?array
    {
        foreach ($this->programsCatalog($user) as $program) {
            if ($program['code'] === $code) {
                return $program;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatReport(ReportManagement $report): array
    {
        return [
            'id' => $report->id,
            'programCode' => $report->program_code,
            'programName' => $report->program_name,
            'activity' => $report->activity_name,
            'fileName' => $report->file_name,
            'fileType' => 'pdf',
            'status' => $report->status,
            'uploadedAt' => $report->created_at?->toIso8601String(),
            'barangay' => $report->barangay?->name,
            'downloadUrl' => route('api.reports-management.download', ['id' => $report->id]),
        ];
    }
}
