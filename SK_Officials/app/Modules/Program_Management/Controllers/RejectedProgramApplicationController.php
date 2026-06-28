<?php

namespace App\Modules\Program_Management\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProgramApplication;
use App\Models\User;
use App\Modules\Program_Management\Services\ProgramApplicationReviewService;
use App\Modules\Program_Management\Services\ProgramApplicationStatusMailService;
use App\Services\RejectedProgramApplicationService;
use App\Services\SkOfficialActivityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

abstract class RejectedProgramApplicationController extends Controller
{
    protected string $letter = 'A';

    protected string $viewName = '';

    protected string $restoreRoute = '';

    protected string $dataRoute = '';

    public function __construct(
        private readonly RejectedProgramApplicationService $rejectedService,
        private readonly ProgramApplicationReviewService $reviewService,
        private readonly SkOfficialActivityService $activityService,
        private readonly ProgramApplicationStatusMailService $statusMailService,
    ) {
    }

    public function index()
    {
        $user = Auth::user();
        $barangayName = null;
        $barangayLogoUrl = null;

        if ($user?->barangay_id) {
            $barangayName = DB::table('barangays')
                ->where('id', $user->barangay_id)
                ->value('name');

            $barangayLogoUrl = app(\App\Services\BarangayLogoUrlService::class)->resolve($user->barangay_id);
        }

        return view($this->viewName, [
            'barangayName' => $barangayName,
            'barangayLogoUrl' => $barangayLogoUrl,
            'dataUrl' => route($this->dataRoute),
            'restoreUrlPrefix' => url(str_replace('/data', '', route($this->dataRoute, [], false))),
        ]);
    }

    public function data(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->barangay_id) {
            return response()->json(['data' => [], 'stats' => $this->emptyStats()]);
        }

        $this->syncMissingRejectedRecords($user);

        $modelClass = $this->rejectedService->modelForLetter($this->letter);

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $modelClass::query()
            ->with(['application' => fn ($q) => $q->withTrashed()->with('scheduleProgram')])
            ->forBarangay($user->barangay_id)
            ->active()
            ->orderByDesc('rejected_at')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('application', function ($qb) use ($search) {
                $qb->withTrashed()
                    ->where(function ($inner) use ($search) {
                        $inner->where('last_name', 'ilike', "%{$search}%")
                            ->orWhere('first_name', 'ilike', "%{$search}%")
                            ->orWhere('middle_name', 'ilike', "%{$search}%")
                            ->orWhere('email', 'ilike', "%{$search}%")
                            ->orWhere('school_name', 'ilike', "%{$search}%")
                            ->orWhereHas('scheduleProgram', function ($schedule) use ($search) {
                                $schedule->where('program_name', 'ilike', "%{$search}%")
                                    ->orWhere('program_type', 'ilike', "%{$search}%");
                            });
                    });
            });
        }

        $filter = $request->get('filter', 'all');
        if ($filter === 'today') {
            $query->whereDate('rejected_at', now()->toDateString());
        } elseif ($filter === 'week') {
            $query->where('rejected_at', '>=', now()->startOfWeek());
        } elseif ($filter === 'month') {
            $query->where('rejected_at', '>=', now()->startOfMonth());
        }

        $records = $query->get();
        $data = $records
            ->map(fn (Model $row) => $this->formatRecord($row))
            ->filter()
            ->values();

        $all = $modelClass::forBarangay($user->barangay_id)->active()->get();

        return response()->json([
            'data' => $data,
            'stats' => [
                'total' => $all->count(),
                'today' => $all->filter(fn ($r) => $r->rejected_at?->isToday())->count(),
                'month' => $all->filter(fn ($r) => $r->rejected_at?->isCurrentMonth())->count(),
            ],
        ]);
    }

    public function restore(string $id)
    {
        $user = Auth::user();

        if (! $user || ! $user->barangay_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        try {
            $applicationId = $this->parseApplicationId($id);
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first(),
            ], 422);
        }

        $modelClass = $this->rejectedService->modelForLetter($this->letter);

        $application = ProgramApplication::withTrashed()
            ->with(['scheduleProgram'])
            ->whereKey($applicationId)
            ->whereHas('scheduleProgram', function ($query) use ($user) {
                $query->where('barangay_id', $user->barangay_id)
                    ->where('program_letter', $this->letter);
            })
            ->first();

        if ($application === null) {
            return response()->json(['success' => false, 'message' => 'Application not found.'], 404);
        }

        if ($application->status !== ProgramApplication::STATUS_REJECTED) {
            return response()->json(['success' => false, 'message' => 'Only rejected applications can be restored.'], 422);
        }

        $rejectedRow = $modelClass::forBarangay($user->barangay_id)
            ->active()
            ->where('program_application_id', $applicationId)
            ->first();

        if ($rejectedRow === null) {
            $this->rejectedService->recordRejection($application, $user, $this->letter);
        }

        if ($application->trashed()) {
            $application->restore();
        }

        $application->update([
            'status' => ProgramApplication::STATUS_PENDING,
            'rejection_reason' => null,
            'rejection_reasons' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        $this->rejectedService->markRestored($application, $this->letter);

        $label = $this->letter === 'I' ? 'sports' : 'scholarship';
        $fullName = trim(implode(' ', array_filter([
            $application->first_name,
            $application->middle_name,
            $application->last_name,
            $application->suffix,
        ])));

        $this->activityService->log(
            $user,
            "{$label}.restore",
            "Restored rejected {$label} application: {$fullName}",
            ['application_id' => $applicationId]
        );

        $fresh = $application->fresh(['scheduleProgram', 'kabataan']);

        if ($this->statusMailService->isProgramLetterNotifiable($this->letter)) {
            $this->statusMailService->notify($fresh, 'restored');
        }

        return response()->json([
            'success' => true,
            'message' => 'Application restored to pending requests.',
            'full_name' => $fullName !== '' ? $fullName : 'Applicant',
        ]);
    }

    protected function syncMissingRejectedRecords(User $user): void
    {
        $modelClass = $this->rejectedService->modelForLetter($this->letter);

        $trackedIds = $modelClass::forBarangay($user->barangay_id)
            ->active()
            ->pluck('program_application_id')
            ->all();

        $missingApplications = ProgramApplication::query()
            ->with(['scheduleProgram'])
            ->where('status', ProgramApplication::STATUS_REJECTED)
            ->whereHas('scheduleProgram', function ($query) use ($user) {
                $query->where('barangay_id', $user->barangay_id)
                    ->where('program_letter', $this->letter);
            })
            ->when($trackedIds !== [], fn ($query) => $query->whereNotIn('id', $trackedIds))
            ->get();

        foreach ($missingApplications as $application) {
            $reviewer = $application->reviewed_by
                ? User::query()->find($application->reviewed_by)
                : $user;

            if ($reviewer !== null) {
                $this->rejectedService->recordRejection($application, $reviewer, $this->letter);
            }
        }
    }

    protected function parseApplicationId(string $id): int
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false || (int) $id <= 0) {
            throw ValidationException::withMessages([
                'application_id' => ['Invalid application id.'],
            ]);
        }

        return (int) $id;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function formatRecord(Model $row): ?array
    {
        $application = $row->application;
        if ($application === null) {
            return null;
        }

        $appData = $this->reviewService->formatApplicationModel($application, true);

        $rejectedAt = $row->rejected_at;

        return array_merge($appData, [
            'rejection_reason' => $row->rejection_reason ?: ($appData['rejection_reason'] ?? '—'),
            'rejection_reasons' => $row->rejection_reasons ?? ($appData['rejection_reasons'] ?? []),
            'rejected_date' => $rejectedAt?->format('M j, Y') ?? '—',
            'rejected_time' => $rejectedAt?->format('g:i A') ?? '—',
            'rejected_at' => $rejectedAt?->toIso8601String(),
            'date_submitted' => $application->created_at?->format('M j, Y') ?? ($appData['date_submitted'] ?? '—'),
        ]);
    }

    /**
     * @return array{total: int, today: int, month: int}
     */
    protected function emptyStats(): array
    {
        return ['total' => 0, 'today' => 0, 'month' => 0];
    }
}
