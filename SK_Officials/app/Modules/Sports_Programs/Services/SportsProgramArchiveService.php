<?php

namespace App\Modules\Sports_Programs\Services;

use App\Models\ProgramApplication;
use App\Models\RejectedSports;
use App\Models\ScheduleProgram;
use App\Models\User;
use App\Modules\Program_Management\Services\ScheduleProgramService;
use App\Services\SkOfficialActivityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class SportsProgramArchiveService
{
    public const RETENTION_DAYS = 30;

    public function __construct(
        private readonly SkOfficialActivityService $activityService,
    ) {
    }

    public function archive(User $user, ScheduleProgram $program, ?string $reason = null): ScheduleProgram
    {
        $this->assertSportsProgram($program);
        $this->assertBarangayAccess($user, $program);

        $now = now();

        DB::table('schedule_programs')
            ->where('id', $program->id)
            ->update([
                'is_archived' => DB::raw('true'),
                'archived_at' => $now,
                'archived_by' => $user->id,
                'deleted_reason' => $reason,
                'restored_at' => null,
                'restored_by' => null,
                'status' => ScheduleProgram::STATUS_CLOSED,
                'updated_at' => $now,
            ]);

        $this->logActivity($user, 'sports_program.archived', 'Archived sports program: '.$program->program_name, $program->id);

        return $program->fresh();
    }

    public function restore(User $user, ScheduleProgram $program): ScheduleProgram
    {
        $this->assertSportsProgram($program);
        $this->assertBarangayAccess($user, $program);

        if (! $program->is_archived) {
            throw ValidationException::withMessages([
                'program' => ['This program is not archived.'],
            ]);
        }

        $now = now();

        DB::table('schedule_programs')
            ->where('id', $program->id)
            ->update([
                'is_archived' => DB::raw('false'),
                'archived_at' => null,
                'archived_by' => null,
                'deleted_reason' => null,
                'restored_at' => $now,
                'restored_by' => $user->id,
                'updated_at' => $now,
            ]);

        $this->logActivity($user, 'sports_program.restored', 'Restored sports program: '.$program->program_name, $program->id);

        return $program->fresh();
    }

    public function permanentlyDelete(User $user, ScheduleProgram $program): void
    {
        $this->assertSportsProgram($program);
        $this->assertBarangayAccess($user, $program);

        if (! $program->is_archived) {
            throw ValidationException::withMessages([
                'program' => ['Only archived sports programs can be permanently deleted.'],
            ]);
        }

        if ($this->hasHistoricalRecords($program)) {
            throw ValidationException::withMessages([
                'program' => ['This sports program contains historical records and cannot be permanently deleted.'],
            ]);
        }

        DB::transaction(function () use ($user, $program) {
            $programId = $program->id;
            $programName = $program->program_name;

            $this->deleteApplicationFiles($programId);
            $applicationIds = ProgramApplication::withTrashed()
                ->where('program_id', $programId)
                ->pluck('id');
            if ($applicationIds->isNotEmpty()) {
                RejectedSports::query()
                    ->whereIn('program_application_id', $applicationIds)
                    ->delete();
            }
            ProgramApplication::withTrashed()->where('program_id', $programId)->forceDelete();
            $program->delete();

            $this->logActivity($user, 'sports_program.permanent_delete', 'Permanently deleted sports program: '.$programName, $programId);
        });
    }

    public function purgeExpired(): int
    {
        if (! Schema::hasColumn('schedule_programs', 'is_archived')) {
            return 0;
        }

        $cutoff = now()->subDays(self::RETENTION_DAYS);
        $purged = 0;

        ScheduleProgram::query()
            ->where('program_letter', ScheduleProgramService::LETTER_SPORTS)
            ->archived()
            ->whereNotNull('archived_at')
            ->where('archived_at', '<=', $cutoff)
            ->orderBy('id')
            ->chunkById(25, function ($programs) use (&$purged) {
                foreach ($programs as $program) {
                    if ($this->hasHistoricalRecords($program)) {
                        continue;
                    }

                    try {
                        DB::transaction(function () use ($program) {
                            $applicationIds = ProgramApplication::withTrashed()
                                ->where('program_id', $program->id)
                                ->pluck('id');
                            $this->deleteApplicationFiles($program->id);
                            if ($applicationIds->isNotEmpty()) {
                                RejectedSports::query()
                                    ->whereIn('program_application_id', $applicationIds)
                                    ->delete();
                            }
                            ProgramApplication::withTrashed()->where('program_id', $program->id)->forceDelete();
                            $program->delete();
                        });
                        $purged++;
                    } catch (Throwable) {
                        continue;
                    }
                }
            });

        return $purged;
    }

    public function hasHistoricalRecords(ScheduleProgram $program): bool
    {
        return ProgramApplication::withTrashed()
            ->where('program_id', $program->id)
            ->exists();
    }

    public function daysRemaining(ScheduleProgram $program): ?int
    {
        if (! $program->is_archived || $program->archived_at === null) {
            return null;
        }

        $expiresAt = $program->archived_at->copy()->addDays(self::RETENTION_DAYS);

        return max(0, (int) now()->diffInDays($expiresAt, false));
    }

    protected function deleteApplicationFiles(int $programId): void
    {
        $applications = ProgramApplication::withTrashed()
            ->where('program_id', $programId)
            ->get(['required_documents']);

        foreach ($applications as $application) {
            $documents = $application->required_documents ?? [];
            if (! is_array($documents)) {
                continue;
            }

            $items = array_is_list($documents) ? $documents : array_values($documents);
            foreach ($items as $meta) {
                if (! is_array($meta) || empty($meta['path'])) {
                    continue;
                }

                $path = (string) $meta['path'];
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }

                $kabataanRoot = realpath(base_path('../Kabataan/storage/app/public'));
                if ($kabataanRoot) {
                    $candidate = rtrim($kabataanRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
                    if (is_file($candidate)) {
                        @unlink($candidate);
                    }
                }
            }
        }
    }

    protected function assertSportsProgram(ScheduleProgram $program): void
    {
        if (strtoupper((string) $program->program_letter) !== ScheduleProgramService::LETTER_SPORTS) {
            throw ValidationException::withMessages([
                'program' => ['This action is only available for sports programs.'],
            ]);
        }
    }

    protected function assertBarangayAccess(User $user, ScheduleProgram $program): void
    {
        if ($user->barangay_id === null || (int) $user->barangay_id !== (int) $program->barangay_id) {
            throw ValidationException::withMessages([
                'program' => ['Unauthorized access to this sports program.'],
            ]);
        }
    }

    protected function logActivity(User $user, string $action, string $description, int $programId): void
    {
        $this->activityService->log($user, $action, $description, [
            'schedule_program_id' => $programId,
            'ip_address' => request()->ip(),
        ]);
    }
}
