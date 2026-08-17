<?php

namespace App\Modules\KKProfilingRequests\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Models\User;
use App\Modules\KKProfilingRequests\Notifications\KabataanApprovedNotification;
use App\Modules\KKProfilingRequests\Notifications\KabataanRejectedNotification;
use App\Services\BarangayLogoUrlService;
use App\Services\BarangayZoneService;
use App\Services\KkProfilingOfficialUpdateService;
use App\Services\KkProfilingRequestDataService;
use App\Services\KkSupportingDocumentService;
use App\Services\KkSurveyResponseService;
use App\Services\RejectedKkProfilingService;
use App\Services\RespondentNumberService;
use App\Services\SkOfficialActivityService;
use App\Support\KabataanApprovedStatuses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KKProfilingRequestsController extends Controller
{
    public function __construct(
        private readonly SkOfficialActivityService $activityService,
        private readonly KkSupportingDocumentService $supportingDocumentService,
        private readonly KkProfilingRequestDataService $profilingDataService,
        private readonly KkProfilingOfficialUpdateService $officialUpdateService,
    ) {}

    public function index()
    {
        $user = Auth::user();
        $barangayName = null;
        $barangayLogoUrl = null;

        if ($user?->barangay_id) {
            $barangay = DB::table('barangays')->where('id', $user->barangay_id)->first();
            $barangayName = $barangay?->name;
            $barangayLogoUrl = app(BarangayLogoUrlService::class)->resolve($user->barangay_id);
        }

        return view('KKProfilingRequests::kkprofiling-requests', [
            'barangayName' => $barangayName,
            'barangayLogoUrl' => $barangayLogoUrl,
            'barangayZones' => $user?->barangay_id
                ? app(BarangayZoneService::class)->activeZonesForBarangay((int) $user->barangay_id)
                : collect(),
        ]);
    }

    public function data(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->barangay_id) {
            return response()->json(['data' => [], 'stats' => $this->emptyStats()]);
        }

        $barangayId = (int) $user->barangay_id;
        $barangayLogoUrl = app(BarangayLogoUrlService::class)->resolve($barangayId);

        $query = KabataanRegistration::with([
            'barangay',
            'survey' => function ($surveyQuery) {
                $surveyQuery->select($this->profilingDataService->listSurveyColumns());
            },
        ])
            ->forBarangay($barangayId);
        KabataanApprovedStatuses::applyPendingProfilingScope($query);
        $query->orderBy('last_name')->orderBy('first_name');

        if ($request->filled('status') && $request->status !== 'All') {
            $query->where('evaluation_status', $request->status);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('last_name', 'ilike', "%{$q}%")
                    ->orWhere('first_name', 'ilike', "%{$q}%")
                    ->orWhere('email', 'ilike', "%{$q}%")
                    ->orWhere('contact_number', 'ilike', "%{$q}%");
            });
        }

        if ($request->filled('purok')) {
            $query->whereJsonContains('form_data->purok_zone', $request->purok);
        }

        if ($request->filled('voter')) {
            $query->whereJsonContains('form_data->sk_voter', $request->voter);
        }

        $registrations = $query->get();
        $registrationIds = $registrations->modelKeys();

        $data = $registrations->map(function (KabataanRegistration $registration) use ($barangayLogoUrl) {
            return $this->profilingDataService->formatListRow(
                $registration,
                $registration->survey,
                $barangayLogoUrl,
            );
        })->values();

        foreach ($this->profilingDataService->unmatchedPendingSurveys($barangayId, $registrationIds) as $survey) {
            $linked = $survey->registration;

            if ($linked === null) {
                continue;
            }

            $data->push($this->profilingDataService->formatListRow($linked, $survey, $barangayLogoUrl));
        }

        $stats = KabataanApprovedStatuses::statsForBarangay($barangayId);

        return response()->json(['data' => $data, 'stats' => $stats]);
    }

    public function show(int $id)
    {
        $user = Auth::user();

        if (! $user || ! $user->barangay_id) {
            return response()->json(['success' => false, 'message' => 'Authentication error'], 401);
        }

        $registration = KabataanRegistration::with(['barangay', 'survey'])
            ->forBarangay((int) $user->barangay_id)
            ->findOrFail($id);

        $barangayLogoUrl = app(BarangayLogoUrlService::class)->resolve((int) $user->barangay_id);

        return response()->json([
            'success' => true,
            'data' => $this->profilingDataService->formatDetailRow(
                $registration,
                $registration->survey,
                $barangayLogoUrl,
                $this->supportingDocumentService->formatForApi($registration),
            ),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $user = Auth::user();

        if (! $user || ! $user->barangay_id) {
            return response()->json(['success' => false, 'message' => 'Authentication error'], 401);
        }

        $validated = $request->validate([
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'custom_suffix' => ['nullable', 'string', 'max:5'],
            'email' => ['nullable', 'email', 'max:254'],
            'contact_number' => ['nullable', 'string', 'max:15'],
            'age' => ['nullable', 'integer', 'min:15', 'max:30'],
            'birthday' => ['nullable', 'string', 'max:20'],
            'sex' => ['nullable', 'in:Male,Female'],
            'purok_zone' => ['nullable', 'string', 'max:100'],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'youth_classification' => ['nullable', 'string', 'max:80'],
            'youth_age_group' => ['nullable', 'string', 'max:80'],
            'work_status' => ['nullable', 'string', 'max:80'],
            'education' => ['nullable', 'string', 'max:80'],
            'sk_voter' => ['nullable', 'string', 'max:10'],
            'national_voter' => ['nullable', 'string', 'max:10'],
            'sk_voted' => ['nullable', 'string', 'max:10'],
            'kk_assembly' => ['nullable', 'string', 'max:10'],
            'kk_times' => ['nullable', 'string', 'max:40'],
            'kk_reason' => ['nullable', 'string', 'max:120'],
            'facebook' => ['nullable', 'string', 'max:50'],
            'facebook_profile_url' => ['nullable', 'string', 'max:50'],
            'group_chat' => ['nullable', 'string', 'max:10'],
        ]);

        $registration = KabataanRegistration::forBarangay($user->barangay_id)->findOrFail($id);

        try {
            $result = $this->officialUpdateService->update($user, $registration, $validated);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        }

        $message = 'KK Profiling record updated.';
        if ($result['invite_sent']) {
            $message = 'KK Profiling updated and an email was sent to the kabataan.';
        } elseif ($result['invite_error']) {
            $message = $result['invite_error'];
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'invite_sent' => $result['invite_sent'],
            'email' => $result['registration']->email,
        ]);
    }

    public function approve(Request $request, int $id)
    {
        try {
            $user = Auth::user();

            if (! $user || ! $user->barangay_id) {
                return response()->json(['success' => false, 'message' => 'Authentication error'], 401);
            }

            $registration = KabataanRegistration::forBarangay($user->barangay_id)->findOrFail($id);

            return response()->json($this->approveRegistration($user, $registration));
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first()
                    ?: 'This youth must verify their email and create an account before the KK profiling request can be approved.',
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Approve failed with exception', [
                'id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to approve: '.$e->getMessage()], 500);
        }
    }

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $user = Auth::user();

        if (! $user || ! $user->barangay_id) {
            return response()->json(['success' => false, 'message' => 'Authentication error'], 401);
        }

        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));
        $registrations = KabataanRegistration::forBarangay($user->barangay_id)
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $approved = [];
        $skipped = [];

        foreach ($ids as $id) {
            $registration = $registrations->get($id);

            if ($registration === null) {
                $skipped[] = ['id' => $id, 'message' => 'Record not found.'];

                continue;
            }

            if (! KabataanApprovedStatuses::hasVerifiedAccount($registration)) {
                $skipped[] = [
                    'id' => $id,
                    'name' => $registration->full_name,
                    'message' => 'Must verify email and create an account before approval.',
                ];

                continue;
            }

            try {
                $result = $this->approveRegistration($user, $registration);
                $approved[] = [
                    'id' => $id,
                    'name' => $registration->full_name,
                    'respondent_display' => $result['respondent_display'] ?? null,
                ];
            } catch (\Exception $e) {
                $skipped[] = [
                    'id' => $id,
                    'name' => $registration->full_name,
                    'message' => $e->getMessage(),
                ];
            }
        }

        $approvedCount = count($approved);
        $skippedCount = count($skipped);

        if ($approvedCount === 0) {
            return response()->json([
                'success' => false,
                'message' => $skipped[0]['message'] ?? 'No KK Profiling requests were approved.',
                'approved' => $approved,
                'skipped' => $skipped,
                'approved_count' => 0,
                'skipped_count' => $skippedCount,
            ], 422);
        }

        $message = $approvedCount === 1
            ? '1 KK Profiling request approved.'
            : $approvedCount.' KK Profiling requests approved.';

        if ($skippedCount > 0) {
            $message .= ' '.$skippedCount.' skipped.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'approved' => $approved,
            'skipped' => $skipped,
            'approved_count' => $approvedCount,
            'skipped_count' => $skippedCount,
        ]);
    }

    public function reject(Request $request, int $id)
    {
        $request->validate(['reasons' => 'required|array|min:1']);

        $user = Auth::user();
        $reasons = implode('; ', $request->reasons);
        $rejectedService = app(RejectedKkProfilingService::class);
        $surveyService = app(KkSurveyResponseService::class);

        $alreadyRejected = false;

        DB::transaction(function () use ($user, $id, $reasons, $rejectedService, $surveyService, &$alreadyRejected) {
            $registration = KabataanRegistration::forBarangay($user->barangay_id)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($rejectedService->isAlreadyRejected($registration)) {
                $alreadyRejected = true;

                return;
            }

            $previousRegistrationStatus = $registration->status;
            $previousEvaluationStatus = $registration->evaluation_status;
            $previousUserStatus = null;
            if ($registration->user_id) {
                $previousUserStatus = User::find($registration->user_id)?->status;
            }

            $registration->update([
                'status' => 'rejected',
                'reviewed_by_user_id' => $user->id,
                'reviewed_at' => now(),
                'review_notes' => $reasons,
            ]);

            $rejectedService->recordRejection(
                $registration,
                $user,
                $reasons,
                $previousUserStatus,
                $previousRegistrationStatus,
                $previousEvaluationStatus,
            );

            $surveyService->syncStatus($registration->fresh(), 'rejected');

            if ($registration->user_id) {
                $kabataanUser = User::find($registration->user_id);
                if ($kabataanUser) {
                    $kabataanUser->update(['status' => 'REJECTED']);
                    $kabataanUser->notify(new KabataanRejectedNotification($reasons));
                }
            }
        });

        if ($alreadyRejected) {
            return response()->json([
                'success' => true,
                'message' => 'Registration already rejected.',
                'already_rejected' => true,
            ]);
        }

        $registration = KabataanRegistration::find($id);

        $this->activityService->log(
            $user,
            'kk.reject',
            'Rejected KK profiling request: '.($registration?->full_name ?? 'Registration #'.$id),
            ['registration_id' => $id, 'reasons' => $reasons]
        );

        return response()->json(['success' => true, 'message' => 'Registration rejected.']);
    }

    public function document(Request $request, int $id, int $documentIndex, string $side)
    {
        $user = Auth::user();

        if (! $user || ! $user->barangay_id) {
            abort(401);
        }

        if (! in_array($side, ['front', 'back'], true)) {
            abort(404);
        }

        $registration = KabataanRegistration::forBarangay($user->barangay_id)->findOrFail($id);

        return $this->supportingDocumentService->streamForOfficial(
            $registration,
            $documentIndex,
            $side,
            $request->boolean('download'),
        );
    }

    /**
     * @return array{success: bool, message: string, respondent_sequence: mixed, respondent_display: string}
     */
    private function approveRegistration(User $user, KabataanRegistration $registration): array
    {
        if (! KabataanApprovedStatuses::hasVerifiedAccount($registration)) {
            throw ValidationException::withMessages([
                'account' => ['This youth must verify their email and create an account before the KK profiling request can be approved.'],
            ]);
        }

        DB::transaction(function () use ($registration, $user) {
            $registration->update([
                'status' => 'active',
                'evaluation_status' => 'active',
                'reviewed_by_user_id' => $user->id,
                'reviewed_at' => now(),
                'review_notes' => null,
            ]);

            app(RespondentNumberService::class)->ensureAssigned($registration->fresh());
            app(KkSurveyResponseService::class)->syncStatus($registration->fresh(), 'approved');

            if ($registration->user_id) {
                $kabataanUser = User::find($registration->user_id);
                if ($kabataanUser) {
                    $kabataanUser->update(['status' => 'ACTIVE']);
                    $kabataanUser->notify(new KabataanApprovedNotification);
                }
            }
        });

        $approved = KabataanRegistration::find($registration->id);

        $this->activityService->log(
            $user,
            'kk.approve',
            'Approved KK profiling request: '.($approved?->full_name ?? 'Registration #'.$registration->id),
            ['registration_id' => $registration->id]
        );

        return [
            'success' => true,
            'message' => 'KK Profiling approved successfully.',
            'respondent_sequence' => $approved?->respondent_sequence,
            'respondent_display' => RespondentNumberService::displaySequence(
                $approved?->respondent_sequence,
                $approved?->respondent_number
            ),
        ];
    }

    private function emptyStats(): array
    {
        return ['active' => 0, 'pending' => 0, 'pending_verification' => 0, 'rejected' => 0, 'total' => 0];
    }
}
