<?php

namespace App\Modules\Kabataan\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Services\BarangayLogoUrlService;
use App\Services\BarangayZoneService;
use App\Services\KabataanInAppNotificationService;
use App\Services\KabataanProfilingHistoryService;
use App\Services\KkSupportingDocumentService;
use App\Services\KkProfilingRequestDataService;
use App\Services\KkSurveyResponseService;
use App\Services\RespondentNumberService;
use App\Services\SkOfficialActivityService;
use App\Support\KabataanApprovedStatuses;
use App\Support\KabataanLocationResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KabataanController extends Controller
{
    public function __construct(
        private readonly SkOfficialActivityService $activityService,
        private readonly KkSupportingDocumentService $supportingDocumentService,
        private readonly KkProfilingRequestDataService $profilingDataService,
        private readonly KabataanProfilingHistoryService $profilingHistoryService,
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

        return view('Kabataan::kabataan', [
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
            return response()->json(['data' => [], 'stats' => $this->emptyStats(), 'years' => []]);
        }

        $availableYears = $this->profilingHistoryService->availableYears((int) $user->barangay_id);
        $selectedYear = (int) ($request->input('year') ?: ($availableYears[0] ?? (int) now()->format('Y')));
        $isHistorical = $this->profilingHistoryService->isHistoricalYear($selectedYear);

        $records = $this->profilingHistoryService->recordsForYear((int) $user->barangay_id, $selectedYear);

        if ($request->filled('search')) {
            $s = strtolower((string) $request->search);
            $records = $records->filter(function ($record) use ($s) {
                $last = strtolower((string) ($record->last_name ?? ''));
                $first = strtolower((string) ($record->first_name ?? ''));
                $respondent = strtolower((string) ($record->respondent_number ?? ''));

                return str_contains($last, $s) || str_contains($first, $s) || str_contains($respondent, $s);
            })->values();
        }

        $approvedSurveys = $this->profilingDataService
            ->surveysKeyedByRegistrationId(
                $this->profilingDataService->approvedSurveysForBarangay($user->barangay_id)
            );

        $val = fn($fd, $key) => is_array($fd[$key] ?? null)
            ? ($fd[$key][0] ?? '—')
            : ($fd[$key] ?? '—');

        $data = $records->map(function ($record) use ($val, $approvedSurveys, $selectedYear, $isHistorical) {
            $registration = $record instanceof \App\Models\KabataanProfilingHistory
                ? $record->registration
                : $record;

            if (! $registration) {
                return null;
            }

            $fd = $record instanceof \App\Models\KabataanProfilingHistory
                ? ($record->form_data ?? [])
                : ($registration->form_data ?? []);

            $location = KabataanLocationResolver::forRegistration($registration);
            $supportingDocuments = $this->supportingDocumentService->formatForApi($registration, 'kabataan.document');
            $idVerification = is_array($fd['id_verification'] ?? null) ? $fd['id_verification'] : null;
            $rawDocuments = $fd['supporting_documents'] ?? [];
            $hasDocuments = $supportingDocuments !== []
                || (is_array($rawDocuments) && $rawDocuments !== []);

            $survey = $approvedSurveys[$registration->id] ?? null;
            if ($survey !== null) {
                $surveyDocuments = $survey->supporting_documents ?? [];
                if (is_array($surveyDocuments) && $surveyDocuments !== []) {
                    $hasDocuments = true;
                }
            }

            $submittedAt = $record instanceof \App\Models\KabataanProfilingHistory
                ? $record->submitted_at
                : $registration->submitted_at;

            return [
                'id'             => $registration->id,
                'profiling_year' => $selectedYear,
                'is_historical'  => $isHistorical,
                'can_modify'     => ! $isHistorical,
                'respondent_no'       => RespondentNumberService::displaySequence(
                    $registration->respondent_sequence,
                    $registration->respondent_number
                ),
                'respondent_sequence' => $registration->respondent_sequence,
                'last_name'      => $record->last_name ?? $registration->last_name,
                'first_name'     => $record->first_name ?? $registration->first_name,
                'middle_name'    => $record->middle_name ?? $registration->middle_name,
                'suffix'         => $record->suffix ?? $registration->suffix,
                'full_name'      => trim(implode(' ', array_filter([
                    $record->last_name ?? $registration->last_name,
                    $record->first_name ?? $registration->first_name,
                    $record->middle_name ?? $registration->middle_name,
                ]))),
                'age'            => $val($fd, 'age'),
                'sex'            => $val($fd, 'sex'),
                'birthday'       => $val($fd, 'birthday'),
                'email'          => $record->email ?? $registration->email,
                'contact_number' => $record->contact_number ?? $registration->contact_number,
                'barangay'       => $registration->barangay?->name ?? '—',
                'region'         => $location['region'],
                'province'       => $location['province'],
                'city'           => $location['city'],
                'purok_zone'     => $val($fd, 'purok_zone'),
                'education'      => $val($fd, 'education'),
                'civil_status'   => $val($fd, 'civil_status'),
                'youth_classification' => $val($fd, 'youth_classification'),
                'youth_age_group'      => $val($fd, 'youth_age_group'),
                'work_status'    => $val($fd, 'work_status'),
                'sk_voter'       => $val($fd, 'sk_voter'),
                'national_voter' => $val($fd, 'national_voter'),
                'sk_voted'       => $val($fd, 'sk_voted'),
                'kk_times'       => $val($fd, 'kk_times'),
                'kk_assembly'    => $val($fd, 'kk_assembly'),
                'kk_reason'      => $val($fd, 'kk_reason'),
                'facebook'       => $val($fd, 'facebook_profile_url') ?: $val($fd, 'facebook'),
                'group_chat'     => $val($fd, 'group_chat'),
                'signature'      => $fd['signature'] ?? null,
                'submitted_at'   => $submittedAt?->format('m/d/Y'),
                'reviewed_at'    => $registration->reviewed_at?->format('m/d/Y'),
                'evaluation_status' => $registration->evaluation_status,
                'supporting_documents' => $supportingDocuments,
                'id_verification' => $idVerification,
                'has_supporting_documents' => $hasDocuments,
            ];
        })->filter()->values();

        $all = KabataanRegistration::forBarangay($user->barangay_id)->get();
        $listedCountQuery = KabataanRegistration::forBarangay($user->barangay_id);
        KabataanApprovedStatuses::applyKabataanListScope($listedCountQuery);

        $stats = [
            'active'   => $listedCountQuery->count(),
            'pending'  => $all->whereIn('evaluation_status', KabataanApprovedStatuses::pendingEvaluationStatuses())
                ->whereNotIn('status', ['rejected'])->count(),
            'rejected' => $all->where('status', 'rejected')->count()
                + $all->whereIn('evaluation_status', KabataanApprovedStatuses::rejectedEvaluationStatuses())->count(),
            'total'    => $all->count(),
        ];

        return response()->json([
            'data' => $data,
            'stats' => $stats,
            'years' => $availableYears,
            'selected_year' => $selectedYear,
            'is_historical' => $isHistorical,
        ]);
    }

    public function print(Request $request, int $id)
    {
        $user = Auth::user();
        if (! $user || ! $user->barangay_id) {
            abort(401);
        }

        $year = (int) $request->query('year', $this->profilingHistoryService->currentProfilingYear());
        $registration = KabataanRegistration::forBarangay($user->barangay_id)->findOrFail($id);

        $history = \App\Models\KabataanProfilingHistory::query()
            ->where('kabataan_registration_id', $registration->id)
            ->where('profiling_year', $year)
            ->first();

        $formData = $history?->form_data ?? $registration->form_data ?? [];
        $barangayLogoUrl = app(BarangayLogoUrlService::class)->resolve((int) $user->barangay_id);

        return view('Kabataan::print-kk-profiling', [
            'registration' => $registration,
            'formData' => $formData,
            'profilingYear' => $year,
            'submittedAt' => $history?->submitted_at ?? $registration->submitted_at,
            'barangayLogoUrl' => $barangayLogoUrl,
        ]);
    }

    public function batchPrint(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'year' => ['nullable', 'integer'],
        ]);

        $user = Auth::user();
        if (! $user || ! $user->barangay_id) {
            abort(401);
        }

        $year = (int) ($validated['year'] ?? $this->profilingHistoryService->currentProfilingYear());
        $ids = array_values(array_unique($validated['ids']));

        $records = [];
        foreach ($ids as $id) {
            $registration = KabataanRegistration::forBarangay($user->barangay_id)->find($id);
            if (! $registration) {
                continue;
            }

            $history = \App\Models\KabataanProfilingHistory::query()
                ->where('kabataan_registration_id', $registration->id)
                ->where('profiling_year', $year)
                ->first();

            $records[] = [
                'registration' => $registration,
                'formData' => $history?->form_data ?? $registration->form_data ?? [],
                'submittedAt' => $history?->submitted_at ?? $registration->submitted_at,
            ];
        }

        $barangayLogoUrl = app(BarangayLogoUrlService::class)->resolve((int) $user->barangay_id);

        return view('Kabataan::print-kk-profiling-batch', [
            'records' => $records,
            'profilingYear' => $year,
            'barangayLogoUrl' => $barangayLogoUrl,
        ]);
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

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->barangay_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'last_name'  => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'email'      => 'nullable|email|max:150',
        ]);

        $barangay = DB::table('barangays')->where('id', $user->barangay_id)->first();
        if (!$barangay) {
            return response()->json(['success' => false, 'message' => 'Barangay not found.'], 422);
        }

        $formData = $this->buildFormDataFromRequest($request, $barangay);

        $registration = KabataanRegistration::create([
            'tenant_id'         => $barangay->tenant_id,
            'barangay_id'       => $user->barangay_id,
            'last_name'         => $request->input('last_name'),
            'first_name'        => $request->input('first_name'),
            'middle_name'       => $request->input('middle_name'),
            'suffix'            => $request->input('suffix'),
            'email'             => $request->input('email'),
            'contact_number'    => $request->input('contact_number'),
            'form_data'         => $formData,
            'status'            => 'active',
            'evaluation_status' => 'active',
            'submitted_at'      => now(),
            'reviewed_by_user_id' => $user->id,
            'reviewed_at'       => now(),
        ]);

        $respondentNumber = app(RespondentNumberService::class)
            ->assignToRegistration($registration->fresh());

        $this->activityService->log(
            $user,
            'kabataan.create',
            'Added Kabataan record: '.$registration->full_name,
            ['registration_id' => $registration->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'Kabataan record saved.',
            'data'    => [
                'id'                => $registration->id,
                'respondent_number' => $respondentNumber,
            ],
        ]);
    }

    public function update(Request $request, int $id)
    {
        $user = Auth::user();
        $registration = KabataanRegistration::forBarangay($user->barangay_id)->findOrFail($id);

        $request->validate([
            'last_name'  => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
        ]);

        $barangay = DB::table('barangays')->where('id', $user->barangay_id)->first();
        $formData = $this->buildFormDataFromRequest($request, $barangay, $registration->form_data ?? []);

        $registration->update([
            'last_name'      => $request->input('last_name'),
            'first_name'     => $request->input('first_name'),
            'middle_name'    => $request->input('middle_name'),
            'suffix'         => $request->input('suffix'),
            'email'          => $request->input('email'),
            'contact_number' => $request->input('contact_number'),
            'form_data'      => $formData,
        ]);

        $this->activityService->log(
            $user,
            'kabataan.update',
            'Updated Kabataan record: '.$registration->full_name,
            ['registration_id' => $registration->id]
        );

        return response()->json(['success' => true, 'message' => 'Kabataan record updated.']);
    }

    public function destroy(Request $request, int $id)
    {
        $validated = $request->validate([
            'revoke_reason' => ['required', 'string', 'max:500'],
            'year' => ['nullable', 'integer'],
        ]);

        $user = Auth::user();
        $registration = KabataanRegistration::forBarangay($user->barangay_id)->findOrFail($id);

        $year = (int) ($validated['year'] ?? $this->profilingHistoryService->currentProfilingYear());
        if ($this->profilingHistoryService->isHistoricalYear($year)) {
            return response()->json([
                'success' => false,
                'message' => 'Historical KK Profiling records cannot be revoked.',
            ], 422);
        }

        if (! KabataanApprovedStatuses::isListedInKabataan($registration)) {
            return response()->json([
                'success' => false,
                'message' => 'Only approved Kabataan records can be revoked.',
            ], 422);
        }

        $fullName = $registration->full_name;
        $revokeReason = trim($validated['revoke_reason']);
        $registration->update([
            'status' => $registration->user_id ? 'active' : 'pending_verification',
            'evaluation_status' => 'Not Profiled',
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'review_notes' => 'Revoked: '.$revokeReason,
        ]);

        app(KkSurveyResponseService::class)->syncStatus($registration->fresh(), 'pending');

        if ($registration->user_id) {
            app(KabataanInAppNotificationService::class)->notifyKkProfilingRevoked(
                (int) $registration->user_id,
                $revokeReason,
            );
        }

        $this->activityService->log(
            $user,
            'kabataan.revoke',
            'Revoked Kabataan record to pending KK profiling: '.$fullName.' ('.$revokeReason.')',
            ['registration_id' => $id, 'revoke_reason' => $revokeReason]
        );

        return response()->json(['success' => true, 'message' => 'Kabataan record moved to pending KK Profiling Requests.']);
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'year'  => ['nullable', 'integer'],
            'revoke_reason' => ['required', 'string', 'max:500'],
        ]);

        $user = Auth::user();
        $ids = array_values(array_unique($validated['ids']));
        $year = (int) ($validated['year'] ?? $this->profilingHistoryService->currentProfilingYear());

        if ($this->profilingHistoryService->isHistoricalYear($year)) {
            return response()->json([
                'success' => false,
                'message' => 'Historical KK Profiling records cannot be revoked.',
            ], 422);
        }

        $registrations = KabataanRegistration::forBarangay($user->barangay_id)
            ->whereIn('id', $ids)
            ->get()
            ->filter(fn (KabataanRegistration $registration) => KabataanApprovedStatuses::isListedInKabataan($registration));

        if ($registrations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No revocable Kabataan records were found.',
            ], 422);
        }

        $surveyService = app(KkSurveyResponseService::class);
        $notificationService = app(KabataanInAppNotificationService::class);
        $revokeReason = trim($validated['revoke_reason']);

        foreach ($registrations as $registration) {
            $fullName = $registration->full_name;
            $registrationId = $registration->id;

            $registration->update([
                'status' => $registration->user_id ? 'active' : 'pending_verification',
                'evaluation_status' => 'Not Profiled',
                'reviewed_by_user_id' => null,
                'reviewed_at' => null,
                'review_notes' => 'Revoked: '.$revokeReason,
            ]);

            $surveyService->syncStatus($registration->fresh(), 'pending');

            if ($registration->user_id) {
                $notificationService->notifyKkProfilingRevoked((int) $registration->user_id, $revokeReason);
            }

            $this->activityService->log(
                $user,
                'kabataan.revoke',
                'Revoked Kabataan record to pending KK profiling: '.$fullName.' ('.$revokeReason.')',
                ['registration_id' => $registrationId, 'revoke_reason' => $revokeReason]
            );
        }

        return response()->json([
            'success' => true,
            'message' => $registrations->count().' Kabataan record(s) moved to pending KK Profiling Requests.',
            'revoked_count' => $registrations->count(),
        ]);
    }

    private function buildFormDataFromRequest(Request $request, object $barangay, array $existing = []): array
    {
        return array_merge($existing, array_filter([
            'age'                  => $request->input('age'),
            'sex'                  => $request->input('sex'),
            'birthday'             => $request->input('birthday'),
            'purok_zone'           => $request->input('purok_zone'),
            'civil_status'         => $request->input('civil_status'),
            'youth_classification' => $request->input('youth_classification'),
            'youth_age_group'      => $request->input('youth_age_group'),
            'work_status'          => $request->input('work_status'),
            'education'            => $request->input('education'),
            'sk_voter'             => $request->input('sk_voter'),
            'national_voter'       => $request->input('national_voter'),
            'sk_voted'             => $request->input('sk_voted'),
            'kk_assembly'          => $request->input('kk_assembly'),
            'kk_times'             => $request->input('kk_times'),
            'kk_reason'            => $request->input('kk_reason'),
            'facebook'             => $request->input('facebook'),
            'group_chat'           => $request->input('group_chat'),
            'signature'            => $request->input('signature'),
            'region'               => $barangay->region ?? 'Region IV-A (CALABARZON)',
            'province'             => $barangay->province ?? 'Laguna',
            'city'                 => $barangay->municipality ?? 'Santa Cruz',
            'barangay'             => $barangay->name,
        ], fn($v) => $v !== null && $v !== ''));
    }

    private function emptyStats(): array
    {
        return ['active' => 0, 'pending' => 0, 'rejected' => 0, 'total' => 0];
    }
}
