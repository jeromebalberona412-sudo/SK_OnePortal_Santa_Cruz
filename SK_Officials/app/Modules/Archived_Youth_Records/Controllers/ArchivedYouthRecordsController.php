<?php

namespace App\Modules\Archived_Youth_Records\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Services\BarangayLogoUrlService;
use App\Services\RespondentNumberService;
use App\Support\KabataanApprovedStatuses;
use App\Support\KabataanLocationResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArchivedYouthRecordsController extends Controller
{
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

        return view('Archived_Youth_Records::archived-youth-records', [
            'barangayName' => $barangayName,
            'barangayLogoUrl' => $barangayLogoUrl,
        ]);
    }

    public function data(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->barangay_id) {
            return response()->json(['data' => [], 'stats' => $this->emptyStats()]);
        }

        $query = KabataanRegistration::with('barangay')
            ->forBarangay($user->barangay_id)
            ->where('status', 'archived')
            ->whereIn('evaluation_status', KabataanApprovedStatuses::evaluationStatuses())
            ->orderByDesc('archived_at')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('last_name', 'ilike', "%{$search}%")
                    ->orWhere('first_name', 'ilike', "%{$search}%")
                    ->orWhere('respondent_number', 'ilike', "%{$search}%");
            });
        }

        $filter = $request->get('filter', 'all');
        if ($filter === 'today') {
            $query->whereDate('archived_at', now()->toDateString());
        } elseif ($filter === 'week') {
            $query->where('archived_at', '>=', now()->startOfWeek());
        } elseif ($filter === 'month') {
            $query->where('archived_at', '>=', now()->startOfMonth());
        }

        $records = $query->get();

        $val = fn ($fd, $key) => is_array($fd[$key] ?? null)
            ? ($fd[$key][0] ?? '—')
            : ($fd[$key] ?? '—');

        $data = $records->map(function ($r) use ($val) {
            $fd = $r->form_data ?? [];
            $location = KabataanLocationResolver::forRegistration($r);
            $archivedAt = $r->archived_at;

            return [
                'id' => $r->id,
                'respondent_display' => RespondentNumberService::displaySequence(
                    $r->respondent_sequence,
                    $r->respondent_number
                ),
                'last_name' => $r->last_name,
                'first_name' => $r->first_name,
                'middle_name' => $r->middle_name,
                'suffix' => $r->suffix,
                'full_name' => $r->full_name,
                'age' => $val($fd, 'age'),
                'sex' => $val($fd, 'sex'),
                'birthday' => $val($fd, 'birthday'),
                'email' => $r->email,
                'contact_number' => $r->contact_number,
                'barangay' => $r->barangay?->name ?? '—',
                'region' => $location['region'],
                'province' => $location['province'],
                'city' => $location['city'],
                'purok_zone' => $val($fd, 'purok_zone'),
                'education' => $val($fd, 'education'),
                'civil_status' => $val($fd, 'civil_status'),
                'youth_classification' => $val($fd, 'youth_classification'),
                'youth_age_group' => $val($fd, 'youth_age_group'),
                'work_status' => $val($fd, 'work_status'),
                'sk_voter' => $val($fd, 'sk_voter'),
                'national_voter' => $val($fd, 'national_voter'),
                'sk_voted' => $val($fd, 'sk_voted'),
                'kk_times' => $val($fd, 'kk_times'),
                'kk_assembly' => $val($fd, 'kk_assembly'),
                'kk_reason' => $val($fd, 'kk_reason'),
                'facebook' => $val($fd, 'facebook_profile_url') ?: $val($fd, 'facebook'),
                'group_chat' => $val($fd, 'group_chat'),
                'signature' => $fd['signature'] ?? null,
                'submitted_at' => $r->submitted_at?->format('m/d/Y'),
                'archive_reason' => $r->archive_reason ?? 'aged_out',
                'archived_date' => $archivedAt?->format('M d, Y') ?? '—',
                'archived_time' => $archivedAt?->format('h:i A') ?? '—',
                'archived_at' => $archivedAt?->toIso8601String(),
                'barangay_logo_url' => app(BarangayLogoUrlService::class)->resolve($r->barangay_id),
            ];
        });

        $all = KabataanRegistration::forBarangay($user->barangay_id)
            ->where('status', 'archived')
            ->whereIn('evaluation_status', KabataanApprovedStatuses::evaluationStatuses())
            ->get();

        $stats = [
            'total' => $all->count(),
            'today' => $all->filter(fn ($r) => $r->archived_at?->isToday())->count(),
            'month' => $all->filter(fn ($r) => $r->archived_at?->isCurrentMonth())->count(),
        ];

        return response()->json(['data' => $data, 'stats' => $stats]);
    }

    private function emptyStats(): array
    {
        return ['total' => 0, 'today' => 0, 'month' => 0];
    }
}
