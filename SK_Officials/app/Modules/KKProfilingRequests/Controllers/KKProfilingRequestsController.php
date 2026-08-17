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
            $val = function ($key) use ($formData) {
                $raw = $formData[$key] ?? null;
                if (is_array($raw)) {
                    $raw = $raw[0] ?? null;
                }
                if ($raw === null || $raw === '' || $raw === '—') {
                    return null;
                }

                return $raw;
            };

            $idVerification = is_array($formData['id_verification'] ?? null)
                ? $formData['id_verification']
                : null;

            $payload = [
                'id' => $r->id,
                'respondent_number' => $r->respondent_number,
                'respondent_sequence' => $r->respondent_sequence,
                'respondent_display' => RespondentNumberService::displaySequence(
                    $r->respondent_sequence,
                    $r->respondent_number
                ),
                'last_name' => $r->last_name,
                'first_name' => $r->first_name,
                'middle_name' => $r->middle_name ?: ($val('middle_name') !== '—' ? $val('middle_name') : null),
                'suffix' => $this->profilingDataService->resolveSuffixForDisplay($r->suffix, $formData),
                'suffix_raw' => $r->suffix,
                'suffix_other' => is_array($formData['custom_suffix'] ?? null)
                    ? ($formData['custom_suffix'][0] ?? null)
                    : ($formData['custom_suffix'] ?? ($formData['suffix_other'] ?? null)),
                'form_data' => $formData,
                'full_name' => $r->full_name,
                'age' => $val('age'),
                'birthday' => $val('birthday'),
                'sex' => $val('sex'),
                'email' => $r->email,
                'contact_number' => $r->contact_number,
                'barangay' => $r->barangay?->name ?? '—',
                'region' => $r->barangay?->region ?? 'Region IV-A (CALABARZON)',
                'province' => $r->barangay?->province ?? 'Laguna',
                'city' => $r->barangay?->municipality ?? 'Santa Cruz',
                'purok_zone' => $val('purok_zone'),
                'sk_voter' => $val('sk_voter'),
                'national_voter' => $val('national_voter'),
                'civil_status' => $val('civil_status'),
                'youth_classification' => $val('youth_classification'),
                'youth_age_group' => $val('youth_age_group'),
                'work_status' => $val('work_status'),
                'education' => $val('education'),
                'sk_voted' => $val('sk_voted'),
                'kk_assembly' => $val('kk_assembly'),
                'kk_times' => $val('kk_times'),
                'kk_reason' => $val('kk_reason'),
                'facebook' => $val('facebook_profile_url') ?: $val('facebook'),
                'group_chat' => $val('group_chat'),
                'signature' => $formData['signature'] ?? '—',
                'status' => $r->status,
                'evaluation_status' => $r->evaluation_status,
                'evaluation_notes' => $r->evaluation_notes,
                'submitted_at' => $r->submitted_at?->format('m/d/Y'),
                'review_notes' => $r->review_notes,
                'barangay_logo_url' => app(BarangayLogoUrlService::class)->resolve($r->barangay_id),
                'supporting_documents' => $this->supportingDocumentService->formatForApi($r),
                'has_email' => filled($r->email),
                'has_account' => ! empty($r->user_id) && ! empty($r->password_set_at),
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

            $linkedRegistration = $registrationId > 0
                ? KabataanRegistration::find($registrationId)
                : null;

            $surveyPayload['supporting_documents'] = $linkedRegistration
                ? $this->supportingDocumentService->formatForApi($linkedRegistration)
                : [];
            $surveyPayload['id_verification'] = null;

            $data->push($surveyPayload);
        }

        $stats = KabataanApprovedStatuses::statsForBarangay((int) $user->barangay_id);

        return response()->json(['data' => $data, 'stats' => $stats]);
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
        } catch (\Illuminate\Validation\ValidationException $e) {
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
                \Log::error('Approve failed: No authenticated user or barangay_id', ['id' => $id]);

                return response()->json(['success' => false, 'message' => 'Authentication error'], 401);
            }

            $registration = KabataanRegistration::forBarangay($user->barangay_id)->findOrFail($id);

            if (! KabataanApprovedStatuses::hasVerifiedAccount($registration)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This youth must verify their email and create an account before the KK profiling request can be approved.',
                ], 422);
            }

            \Log::info('Approving registration', [
                'id' => $id,
                'current_status' => $registration->status,
                'evaluation_status' => $registration->evaluation_status,
                'user_id' => $user->id,
            ]);

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

                \Log::info('Updated registration status', ['id' => $registration->id, 'new_status' => 'active']);

                if ($registration->user_id) {
                    $kabataanUser = User::find($registration->user_id);
                    if ($kabataanUser) {
                        $kabataanUser->update(['status' => 'ACTIVE']);
                        $kabataanUser->notify(new KabataanApprovedNotification);
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
                'success' => true,
                'message' => 'KK Profiling approved successfully.',
                'respondent_sequence' => $approved?->respondent_sequence,
                'respondent_display' => RespondentNumberService::displaySequence(
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
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to approve: '.$e->getMessage()], 500);
        }
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

    private function emptyStats(): array
    {
        return ['active' => 0, 'pending' => 0, 'pending_verification' => 0, 'rejected' => 0, 'total' => 0];
    }
}
