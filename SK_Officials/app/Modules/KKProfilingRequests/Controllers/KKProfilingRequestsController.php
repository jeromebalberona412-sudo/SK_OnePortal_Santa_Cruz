<?php

namespace App\Modules\KKProfilingRequests\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Services\BarangayLogoUrlService;
use App\Services\BarangayZoneService;
use App\Services\KkSupportingDocumentService;
use App\Services\KkProfilingRequestDataService;
use App\Services\KkSurveyResponseService;
use App\Services\RejectedKkProfilingService;
use App\Services\RespondentNumberService;
use App\Modules\KKProfilingRequests\Notifications\KabataanApprovedNotification;
use App\Modules\KKProfilingRequests\Notifications\KabataanRejectedNotification;
use App\Services\SkOfficialActivityService;
use App\Support\KabataanApprovedStatuses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class KKProfilingRequestsController extends Controller
{
    public function __construct(
        private readonly SkOfficialActivityService $activityService,
        private readonly KkSupportingDocumentService $supportingDocumentService,
        private readonly KkProfilingRequestDataService $profilingDataService,
    ) {
    }

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
            'barangayName'    => $barangayName,
            'barangayLogoUrl' => $barangayLogoUrl,
            'barangayZones'   => $user?->barangay_id
                ? app(BarangayZoneService::class)->activeZonesForBarangay((int) $user->barangay_id)
                : collect(),
        ]);
    }

    public function data(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->barangay_id) {
            return response()->json(['data' => [], 'stats' => $this->emptyStats()]);
        }

        $query = KabataanRegistration::with('barangay')
            ->forBarangay($user->barangay_id);
        KabataanApprovedStatuses::applyPendingProfilingScope($query);
        $query->orderBy('last_name')->orderBy('first_name');

        // Status filter — filter by evaluation_status
        if ($request->filled('status') && $request->status !== 'All') {
            $query->where('evaluation_status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('last_name', 'ilike', "%{$q}%")
                   ->orWhere('first_name', 'ilike', "%{$q}%")
                   ->orWhere('email', 'ilike', "%{$q}%")
                   ->orWhere('contact_number', 'ilike', "%{$q}%");
            });
        }

        // Purok filter
        if ($request->filled('purok')) {
            $query->whereJsonContains('form_data->purok_zone', $request->purok);
        }

        // Voter filter
        if ($request->filled('voter')) {
            $query->whereJsonContains('form_data->sk_voter', $request->voter);
        }

        $registrations = $query->get();

        $pendingSurveys = $this->profilingDataService->pendingSurveysForBarangay($user->barangay_id);
        $surveysByRegistrationId = $this->profilingDataService->surveysKeyedByRegistrationId($pendingSurveys);
        $registrationIds = $registrations->pluck('id')->map(fn ($id) => (int) $id)->all();

        $data = $registrations->map(function ($r) use ($surveysByRegistrationId) {
            $formData = $r->form_data ?? [];

            // Helper: extract value whether stored as string or array
            $val = fn($key) => is_array($formData[$key] ?? null)
                ? ($formData[$key][0] ?? '—')
                : ($formData[$key] ?? '—');

            $idVerification = is_array($formData['id_verification'] ?? null)
                ? $formData['id_verification']
                : null;

            $payload = [
                'id'              => $r->id,
                'respondent_number'   => $r->respondent_number,
                'respondent_sequence' => $r->respondent_sequence,
                'respondent_display'  => RespondentNumberService::displaySequence(
                    $r->respondent_sequence,
                    $r->respondent_number
                ),
                'last_name'       => $r->last_name,
                'first_name'      => $r->first_name,
                'middle_name'     => $r->middle_name ?: ($val('middle_name') !== '—' ? $val('middle_name') : null),
                'suffix'          => $this->profilingDataService->resolveSuffixForDisplay($r->suffix, $formData),
                'suffix_other'    => is_array($formData['custom_suffix'] ?? null)
                    ? ($formData['custom_suffix'][0] ?? null)
                    : ($formData['custom_suffix'] ?? ($formData['suffix_other'] ?? null)),
                'form_data'       => $formData,
                'full_name'       => $r->full_name,
                'age'             => $val('age'),
                'birthday'        => $val('birthday'),
                'sex'             => $val('sex'),
                'email'           => $r->email,
                'contact_number'  => $r->contact_number,
                'barangay'        => $r->barangay?->name ?? '—',
                'region'          => $r->barangay?->region ?? 'Region IV-A (CALABARZON)',
                'province'        => $r->barangay?->province ?? 'Laguna',
                'city'            => $r->barangay?->municipality ?? 'Santa Cruz',
                'purok_zone'      => $val('purok_zone'),
                'sk_voter'        => $val('sk_voter'),
                'national_voter'  => $val('national_voter'),
                'civil_status'    => $val('civil_status'),
                'youth_classification' => $val('youth_classification'),
                'youth_age_group' => $val('youth_age_group'),
                'work_status'     => $val('work_status'),
                'education'       => $val('education'),
                'sk_voted'        => $val('sk_voted'),
                'kk_assembly'     => $val('kk_assembly'),
                'kk_times'        => $val('kk_times'),
                'kk_reason'       => $val('kk_reason'),
                'facebook'        => $val('facebook_profile_url') ?: $val('facebook'),
                'group_chat'      => $val('group_chat'),
                'signature'       => $formData['signature'] ?? '—',
                'status'          => $r->status,
                'evaluation_status' => $r->evaluation_status,
                'evaluation_notes'  => $r->evaluation_notes,
                'submitted_at'    => $r->submitted_at?->format('m/d/Y'),
                'review_notes'    => $r->review_notes,
                'barangay_logo_url' => app(BarangayLogoUrlService::class)->resolve($r->barangay_id),
                'supporting_documents' => $this->supportingDocumentService->formatForApi($r),
                'id_verification' => $idVerification ? [
                    'name_match' => (bool) ($idVerification['name_match'] ?? false),
                    'barangay_match' => (bool) ($idVerification['barangay_match'] ?? false),
                    'duplicate_detected' => (bool) ($idVerification['duplicate_detected'] ?? false),
                    'message' => $idVerification['message'] ?? null,
                    'match_reason' => $idVerification['match_reason'] ?? null,
                    'matched_barangay' => $idVerification['matched_barangay'] ?? null,
                ] : null,
            ];

            return $this->profilingDataService->mergeSurveyIntoRegistrationPayload(
                $payload,
                $surveysByRegistrationId[$r->id] ?? null,
            );
        })->values();

        foreach ($pendingSurveys as $survey) {
            $registrationId = (int) ($survey->kabataan_registration_id ?? 0);

            if ($registrationId > 0 && in_array($registrationId, $registrationIds, true)) {
                continue;
            }

            $surveyPayload = $this->profilingDataService->registrationPayloadFromSurvey($survey);

            if ($surveyPayload === null) {
                continue;
            }

            $surveyPayload['supporting_documents'] = $this->supportingDocumentService->formatForApi($registration);
            $surveyPayload['id_verification'] = null;

            $data->push($surveyPayload);
        }

        $all = KabataanRegistration::forBarangay($user->barangay_id)->get();

        $stats = [
            'active'               => $all->filter(
                fn (KabataanRegistration $r) => KabataanApprovedStatuses::isListedInKabataan($r)
            )->count(),
            'pending_verification' => $all->filter(
                fn (KabataanRegistration $r) => KabataanApprovedStatuses::isPendingInKkProfiling($r)
            )->count(),
            'rejected'             => $all->where('evaluation_status', 'Duplicate')->count() + $all->where('status', 'rejected')->count(),
            'total'                => $data->count(),
        ];

        return response()->json(['data' => $data, 'stats' => $stats]);
    }

    public function approve(Request $request, int $id)
    {
        try {
            $user = Auth::user();
            
            if (!$user || !$user->barangay_id) {
                \Log::error('Approve failed: No authenticated user or barangay_id', ['id' => $id]);
                return response()->json(['success' => false, 'message' => 'Authentication error'], 401);
            }

            $registration = KabataanRegistration::forBarangay($user->barangay_id)->findOrFail($id);
            
            \Log::info('Approving registration', [
                'id' => $id,
                'current_status' => $registration->status,
                'evaluation_status' => $registration->evaluation_status,
                'user_id' => $user->id
            ]);

            DB::transaction(function () use ($registration, $user) {
                app(RespondentNumberService::class)->ensureAssigned($registration);

                $registration->update([
                    'status'              => 'active',
                    'evaluation_status'   => 'active',
                    'reviewed_by_user_id' => $user->id,
                    'reviewed_at'         => now(),
                    'review_notes'        => null,
                ]);

                app(RespondentNumberService::class)->ensureAssigned($registration->fresh());

                app(KkSurveyResponseService::class)->syncStatus($registration->fresh(), 'approved');

                \Log::info('Updated registration status', ['id' => $registration->id, 'new_status' => 'active']);

                if ($registration->user_id) {
                    $kabataanUser = \App\Models\User::find($registration->user_id);
                    if ($kabataanUser) {
                        $kabataanUser->update(['status' => 'ACTIVE']);
                        $kabataanUser->notify(new KabataanApprovedNotification());
                        \Log::info('Notified kabataan user', ['user_id' => $kabataanUser->id]);
                    }
                }
            });

            $approved = KabataanRegistration::find($id);

            \Log::info('Approval completed successfully', ['id' => $id]);

            $this->activityService->log(
                $user,
                'kk.approve',
                'Approved KK profiling request: '.($approved?->full_name ?? 'Registration #'.$id),
                ['registration_id' => $id]
            );

            return response()->json([
                'success'             => true,
                'message'             => 'KK Profiling approved successfully.',
                'respondent_sequence' => $approved?->respondent_sequence,
                'respondent_display'  => RespondentNumberService::displaySequence(
                    $approved?->respondent_sequence,
                    $approved?->respondent_number
                ),
            ]);
        } catch (\Exception $e) {
            \Log::error('Approve failed with exception', [
                'id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to approve: ' . $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, int $id)
    {
        $request->validate(['reasons' => 'required|array|min:1']);

        $user = Auth::user();
        $reasons = implode('; ', $request->reasons);
        $rejectedService = app(RejectedKkProfilingService::class);
        $respondentService = app(RespondentNumberService::class);
        $surveyService = app(KkSurveyResponseService::class);

        $alreadyRejected = false;

        DB::transaction(function () use ($user, $id, $reasons, $rejectedService, $respondentService, $surveyService, &$alreadyRejected) {
            $registration = KabataanRegistration::forBarangay($user->barangay_id)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($rejectedService->isAlreadyRejected($registration)) {
                $alreadyRejected = true;

                return;
            }

            $respondentService->ensureAssigned($registration);
            $registration = $registration->fresh();

            $previousRegistrationStatus = $registration->status;
            $previousEvaluationStatus = $registration->evaluation_status;
            $previousUserStatus = null;
            if ($registration->user_id) {
                $previousUserStatus = \App\Models\User::find($registration->user_id)?->status;
            }

            $registration->update([
                'status'              => 'rejected',
                'reviewed_by_user_id' => $user->id,
                'reviewed_at'         => now(),
                'review_notes'        => $reasons,
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
                $kabataanUser = \App\Models\User::find($registration->user_id);
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

    private function emptyStats(): array
    {
        return ['active' => 0, 'pending_verification' => 0, 'rejected' => 0, 'total' => 0];
    }
}
