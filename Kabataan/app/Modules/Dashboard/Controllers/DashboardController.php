<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Modules\Dashboard\Services\BarangaySkProfileService;
use App\Modules\Profile\Services\ProfileImageService;
use App\Modules\Programs\Services\KabataanProgramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly KabataanProgramService $programService,
        private readonly BarangaySkProfileService $barangaySkProfileService,
    ) {}

    public function index(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('sign-in');
        }

        $user = Auth::user();

        $registration = Cache::remember(
            "kabataan_reg_user_{$user->id}",
            60,
            fn () => KabataanRegistration::with('barangay')->where('user_id', $user->id)->latest()->first()
        );

        $barangayName = $registration?->barangay?->name ?? 'Santa Cruz';

        $tenantId = (int) ($user->tenant_id ?? $registration?->barangay?->tenant_id ?? 0);
        $barangayProfiles = $this->barangaySkProfileService->listForTenant($tenantId);

        $programsPayload = $this->programService->getDashboardPayload($user);

        $viewData = [
            'user' => $user,
            'userAvatarUrl' => app(ProfileImageService::class)->resolveDisplayUrl($user),
            'barangayName' => $barangayName,
            'barangayProfiles' => $barangayProfiles,
            'programsPayload' => $programsPayload,
            'commentPreviewPost' => null,
        ];

        return view('dashboard::dashboard', $viewData)->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }

    public function comments(Request $request, int $id)
    {
        if (! Auth::check()) {
            return redirect()->route('sign-in');
        }

        $post = app(AnnouncementFeedController::class)->formattedVisiblePost(Auth::user(), $id);
        $response = $this->index($request);
        if ($response instanceof View) {
            return $response->with('commentPreviewPost', $post);
        }
        if (isset($response->original) && $response->original instanceof View) {
            $response->original->with('commentPreviewPost', $post);
        }

        return $response;
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
