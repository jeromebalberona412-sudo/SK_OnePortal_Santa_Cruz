<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Modules\KKProfiling\Controllers\KKProfilingController;
use App\Services\BarangayZoneService;
use App\Modules\Dashboard\Services\BarangaySkProfileService;
use App\Modules\Programs\Services\KabataanProgramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private readonly KabataanProgramService $programService,
        private readonly BarangaySkProfileService $barangaySkProfileService,
        private readonly BarangayZoneService $barangayZoneService,
    ) {
    }

    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $registration = KabataanRegistration::with('barangay')
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $formData = $registration?->form_data ?? [];
        $respondentNumber = $formData['respondent_number'] ?? null;

        $barangayName = $registration?->barangay?->name ?? 'Santa Cruz';

        $tenantId = (int) ($user->tenant_id ?? $registration?->barangay?->tenant_id ?? 0);
        $barangayProfiles = $tenantId > 0
            ? $this->barangaySkProfileService->listForTenant($tenantId)
            : [];

        return view('dashboard::dashboard', [
            'user'                => $user,
            'barangayName'        => $barangayName,
            'barangayProfiles'    => $barangayProfiles,
            'programsPayload'     => $this->programService->getDashboardPayload($user),
            'showKkUpdateModal'   => (bool) ($registration && session()->pull('show_kk_profiling_update', false)),
            'kkUpdateBarangay'    => $registration ? $barangayName : null,
            'kkRespondentNumber'  => $respondentNumber ?? '',
            'kkRespondentDisplay' => KKProfilingController::formatRespondentDisplay($respondentNumber),
            'kkBarangayLogoUrl'   => KKProfilingController::getBarangayLogoUrl($registration?->barangay_id),
            'kkBarangayZones'     => $registration
                ? $this->barangayZoneService->activeZonesForBarangay((int) $registration->barangay_id)
                : collect(),
            'kkSelectedPurokZone' => is_array($formData['purok_zone'] ?? null)
                ? ($formData['purok_zone'][0] ?? '')
                : ($formData['purok_zone'] ?? ''),
            'kkSelectedFacebookProfileUrl' => is_array($formData['facebook_profile_url'] ?? null)
                ? ($formData['facebook_profile_url'][0] ?? '')
                : ($formData['facebook_profile_url'] ?? ($formData['facebook'] ?? '')),
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }
    public function barangay(Request $request, string $slug)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
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
            'user'          => $user,
            'slug'          => $profile['slug'],
            'name'          => $profile['name'],
            'color'         => $profile['color'],
            'logo_url'      => $profile['logo_url'],
            'initials'      => $profile['initials'],
            'location'      => $profile['location'],
            'term_label'    => $profile['term_label'],
            'post_count'    => $profile['post_count'],
            'officer_count' => $profile['officer_count'],
            'officials'     => $profile['officials'],
            'posts'         => $profile['posts'],
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }
}

