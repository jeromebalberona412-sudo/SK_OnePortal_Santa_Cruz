<?php

namespace App\Modules\Rejected_KKProfiling\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Models\RejectedKkProfiling;
use App\Models\User;
use App\Services\BarangayLogoUrlService;
use App\Services\KkSurveyResponseService;
use App\Services\RejectedKkProfilingService;
use App\Services\RespondentNumberService;
use App\Services\SkOfficialActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RejectedKKProfilingController extends Controller
{
    public function __construct(
        private readonly SkOfficialActivityService $activityService,
        private readonly RejectedKkProfilingService $rejectedService,
        private readonly KkSurveyResponseService $surveyService,
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

            $barangayLogoUrl = app(BarangayLogoUrlService::class)->resolve($user->barangay_id);
        }

        return view('Rejected_KKProfiling::rejected-kkprofiling', [
            'barangayName'    => $barangayName,
            'barangayLogoUrl' => $barangayLogoUrl,
        ]);
    }

    public function data(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->barangay_id) {
            return response()->json(['data' => [], 'stats' => $this->emptyStats()]);
        }

        $query = RejectedKkProfiling::with(['registration.barangay'])
            ->forBarangay($user->barangay_id)
            ->active()
            ->orderByDesc('rejected_at')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('registration', function ($qb) use ($search) {
                $qb->where('last_name', 'ilike', "%{$search}%")
                    ->orWhere('first_name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('respondent_number', 'ilike', "%{$search}%");
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
        $data = $records->map(fn (RejectedKkProfiling $row) => $this->formatRecord($row));

        $all = RejectedKkProfiling::forBarangay($user->barangay_id)->active()->get();

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

        $rejectedRow = RejectedKkProfiling::forBarangay($user->barangay_id)
            ->active()
            ->where('kabataan_registration_id', $id)
            ->first();

        if ($rejectedRow === null) {
            return response()->json(['success' => false, 'message' => 'Rejected record not found.'], 404);
        }

        $registration = KabataanRegistration::forBarangay($user->barangay_id)->findOrFail($id);

        $restoreState = $this->rejectedService->resolveRestoreState($rejectedRow, $registration);

        $registration->update([
            'status'              => $restoreState['status'],
            'evaluation_status'   => $restoreState['evaluation_status'],
            'review_notes'        => null,
            'reviewed_by_user_id' => null,
            'reviewed_at'         => null,
        ]);

        $this->rejectedService->markRestored($registration);
        $this->surveyService->syncStatus($registration->fresh(), 'pending');
        $this->surveyService->restoreUserAccount(
            $registration->fresh(),
            $restoreState['user_status']
        );

        $this->activityService->log(
            $user,
            'kk.restore',
            'Restored rejected KK profiling request: '.$registration->full_name,
            ['registration_id' => $id]
        );

        return response()->json([
            'success'   => true,
            'message'   => 'Record restored to KK Profiling Requests.',
            'full_name' => $registration->full_name,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatRecord(RejectedKkProfiling $row): array
    {
        $r = $row->registration;
        if ($r === null) {
            return ['id' => $row->kabataan_registration_id];
        }

        $formData = $r->form_data ?? [];
        $val = fn (string $key) => is_array($formData[$key] ?? null)
            ? ($formData[$key][0] ?? '—')
            : ($formData[$key] ?? '—');

        $rejectedAt = $row->rejected_at;

        return [
            'id'                  => $r->id,
            'respondent_number'   => $r->respondent_number,
            'respondent_sequence' => $r->respondent_sequence,
            'respondent_display'  => RespondentNumberService::displaySequence(
                $r->respondent_sequence,
                $r->respondent_number
            ),
            'last_name'           => $r->last_name,
            'first_name'          => $r->first_name,
            'middle_name'         => $r->middle_name,
            'suffix'              => $r->suffix,
            'full_name'           => $r->full_name,
            'age'                 => $val('age'),
            'birthday'            => $val('birthday'),
            'sex'                 => $val('sex'),
            'email'               => $r->email,
            'contact_number'      => $r->contact_number,
            'barangay'            => $r->barangay?->name ?? '—',
            'region'              => $r->barangay?->region ?? 'Region IV-A (CALABARZON)',
            'province'            => $r->barangay?->province ?? 'Laguna',
            'city'                => $r->barangay?->municipality ?? 'Santa Cruz',
            'purok_zone'          => $val('purok_zone'),
            'sk_voter'            => $val('sk_voter'),
            'national_voter'      => $val('national_voter'),
            'civil_status'        => $val('civil_status'),
            'youth_classification'=> $val('youth_classification'),
            'youth_age_group'     => $val('youth_age_group'),
            'work_status'         => $val('work_status'),
            'education'           => $val('education'),
            'sk_voted'            => $val('sk_voted'),
            'kk_assembly'         => $val('kk_assembly'),
            'kk_times'            => $val('kk_times'),
            'kk_reason'           => $val('kk_reason'),
            'facebook'            => $val('facebook_profile_url') ?: $val('facebook'),
            'group_chat'          => $val('group_chat'),
            'signature'           => $formData['signature'] ?? null,
            'status'              => $r->status,
            'evaluation_status'   => $r->evaluation_status,
            'rejection_reason'    => $row->rejection_reason ?: '—',
            'submitted_at'        => $r->submitted_at?->format('m/d/Y'),
            'rejected_date'       => $rejectedAt?->format('M j, Y') ?? '—',
            'rejected_time'       => $rejectedAt?->format('g:i A') ?? '—',
            'rejected_at'         => $rejectedAt?->toIso8601String(),
            'barangay_logo_url'   => app(BarangayLogoUrlService::class)->resolve($r->barangay_id),
        ];
    }

    /**
     * @return array{total: int, today: int, month: int}
     */
    private function emptyStats(): array
    {
        return ['total' => 0, 'today' => 0, 'month' => 0];
    }
}
