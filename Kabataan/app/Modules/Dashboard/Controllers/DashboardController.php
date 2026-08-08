<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Modules\Dashboard\Services\BarangaySkProfileService;
use App\Modules\KKProfiling\Controllers\KKProfilingController;
use App\Modules\Profile\Services\ProfileImageService;
use App\Modules\Programs\Services\KabataanProgramService;
use App\Services\BarangayZoneService;
use App\Services\KabataanProfilingHistoryService;
use App\Services\KkProfilingScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private readonly KabataanProgramService $programService,
        private readonly BarangaySkProfileService $barangaySkProfileService,
        private readonly BarangayZoneService $barangayZoneService,
        private readonly KkProfilingScheduleService $kkProfilingScheduleService,
        private readonly KabataanProfilingHistoryService $profilingHistoryService,
    ) {}

    public function index(Request $request)
    {
        $startTime = microtime(true);
        $logData = [];

        // Enable query logging for this request
        \DB::enableQueryLog();

        if (! Auth::check()) {
            return redirect()->route('sign-in');
        }

        $user = Auth::user();

        $registration = \Illuminate\Support\Facades\Cache::remember(
            "kabataan_reg_user_{$user->id}",
            60,
            fn () => KabataanRegistration::with('barangay')->where('user_id', $user->id)->latest()->first()
        );

        $formData = $registration?->form_data ?? [];
        $respondentNumber = $formData['respondent_number'] ?? null;

        $barangayName = $registration?->barangay?->name ?? 'Santa Cruz';

        $profilingCheckStart = microtime(true);
        $requiresKkUpdate = $registration
            && $this->kkProfilingScheduleService->hasActiveProfilingSchedule((int) $registration->barangay_id)
            && $this->kkProfilingScheduleService->requiresProfilingUpdate($registration);
        $logData['profiling_check_ms'] = round((microtime(true) - $profilingCheckStart) * 1000, 2);

        $barangayProfileStart = microtime(true);
        $tenantId = (int) ($user->tenant_id ?? $registration?->barangay?->tenant_id ?? 0);
        $barangayProfiles = $tenantId > 0
            ? $this->barangaySkProfileService->listForTenant($tenantId)
            : [];
        $logData['barangay_profiles_ms'] = round((microtime(true) - $barangayProfileStart) * 1000, 2);

        $programsStart = microtime(true);
        $programsPayload = $this->programService->getDashboardPayload($user);
        $logData['programs_payload_ms'] = round((microtime(true) - $programsStart) * 1000, 2);

        $viewDataStart = microtime(true);
        $viewData = [
            'user' => $user,
            'userAvatarUrl' => app(ProfileImageService::class)->resolveDisplayUrl($user),
            'barangayName' => $barangayName,
            'barangayProfiles' => $barangayProfiles,
            'programsPayload' => $programsPayload,
            'showKkUpdateModal' => $requiresKkUpdate,
            'kkProfilingUpdateRequired' => $requiresKkUpdate,
            'kkProfilingFormData' => $requiresKkUpdate && $registration
                ? $this->profilingHistoryService->formDataForUpdate($registration)
                : [],
            'kkProfilingOriginalEmail' => $requiresKkUpdate && $registration
                ? $registration->email
                : null,
            'kkProfilingTargetYear' => $requiresKkUpdate && $registration
                ? $this->kkProfilingScheduleService->targetProfilingYearForRegistration($registration)
                : null,
            'kkUpdateBarangay' => $requiresKkUpdate && $registration ? $barangayName : null,
            'kkRespondentNumber' => $requiresKkUpdate ? ($respondentNumber ?? '') : '',
            'kkRespondentDisplay' => $requiresKkUpdate
                ? KKProfilingController::formatRespondentDisplay($respondentNumber)
                : '01',
            'kkBarangayLogoUrl' => $requiresKkUpdate && $registration
                ? KKProfilingController::getBarangayLogoUrl($registration->barangay_id)
                : null,
            'kkBarangayZones' => $requiresKkUpdate && $registration
                ? $this->barangayZoneService->activeZonesForBarangay((int) $registration->barangay_id)
                : collect(),
            'kkSelectedPurokZone' => $requiresKkUpdate && is_array($formData['purok_zone'] ?? null)
                ? ($formData['purok_zone'][0] ?? '')
                : ($requiresKkUpdate ? ($formData['purok_zone'] ?? '') : ''),
            'kkSelectedFacebookProfileUrl' => '',
        ];
        $logData['view_data_prep_ms'] = round((microtime(true) - $viewDataStart) * 1000, 2);

        $logData['total_dashboard_ms'] = round((microtime(true) - $startTime) * 1000, 2);

        // Log query information
        $queries = \DB::getQueryLog();
        $logData['query_count'] = count($queries);
        $logData['queries'] = array_map(function($query) {
            return [
                'sql' => $query['query'],
                'time_ms' => round($query['time'] * 1000, 2),
            ];
        }, $queries);

        \Log::info('Dashboard controller profile', $logData);

        return view('dashboard::dashboard', $viewData)->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }

    public function barangay(Request $request, string $slug)
    {
        if (! Auth::check()) {
            return redirect()->route('sign-in');
        }

        $user = Auth::user();

        $registration = KabataanRegistration::with('barangay')->where('user_id', $user->id)->latest()->first();
        $tenantId = (int) ($user->tenant_id ?? $registration?->barangay?->tenant_id ?? 0);
        $barangay = $this->barangaySkProfileService->findBySlug($slug, $tenantId > 0 ? $tenantId : null);

        if ($barangay === null) {
            abort(404);
        }

        $profile = $this->barangaySkProfileService->buildProfile($barangay);

        return view('dashboard::barangay', [
            'user' => $user,
            'slug' => $profile['slug'],
            'name' => $profile['name'],
            'color' => $profile['color'],
            'logo_url' => $profile['logo_url'],
            'initials' => $profile['initials'],
            'location' => $profile['location'],
            'term_label' => $profile['term_label'],
            'post_count' => $profile['post_count'],
            'officer_count' => $profile['officer_count'],
            'officials' => $profile['officials'],
            'posts' => $profile['posts'],
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }
}
