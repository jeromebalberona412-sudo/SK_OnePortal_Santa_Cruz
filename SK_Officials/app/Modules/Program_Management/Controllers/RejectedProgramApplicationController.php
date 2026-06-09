<?php

namespace App\Modules\Program_Management\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProgramApplication;
use App\Modules\Program_Management\Services\ProgramApplicationReviewService;
use App\Services\RejectedProgramApplicationService;
use App\Services\SkOfficialActivityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                            ->orWhere('email', 'ilike', "%{$search}%")
                            ->orWhere('school_name', 'ilike', "%{$search}%");
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

    public function restore(int $id)
    {
        $user = Auth::user();

        if (! $user || ! $user->barangay_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $modelClass = $this->rejectedService->modelForLetter($this->letter);

        $rejectedRow = $modelClass::forBarangay($user->barangay_id)
            ->active()
            ->where('program_application_id', $id)
            ->first();

        if ($rejectedRow === null) {
            return response()->json(['success' => false, 'message' => 'Rejected record not found.'], 404);
        }

        $application = ProgramApplication::withTrashed()
            ->whereKey($id)
            ->whereHas('scheduleProgram', function ($query) use ($user) {
                $query->where('barangay_id', $user->barangay_id)
                    ->where('program_letter', $this->letter);
            })
            ->first();

        if ($application === null) {
            return response()->json(['success' => false, 'message' => 'Application not found.'], 404);
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
            ['application_id' => $id]
        );

        return response()->json([
            'success' => true,
            'message' => 'Application restored to pending requests.',
            'full_name' => $fullName !== '' ? $fullName : 'Applicant',
        ]);
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
