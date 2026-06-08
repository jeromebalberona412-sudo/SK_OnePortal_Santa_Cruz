<?php

namespace App\Modules\Deleted_Kabataan\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Services\RespondentNumberService;
use App\Services\SkOfficialActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeletedKabataanController extends Controller
{
    public function __construct(private readonly SkOfficialActivityService $activityService)
    {
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

            $barangayLogoUrl = DB::table('barangay_logos')
                ->where('barangay_id', $user->barangay_id)
                ->value('url');
        }

        return view('Deleted_Kabataan::deleted-kabataan', [
            'barangayName'    => $barangayName,
            'barangayLogoUrl' => $barangayLogoUrl,
        ]);
    }

    public function data(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->barangay_id) {
            return response()->json(['data' => [], 'stats' => $this->emptyStats()]);
        }

        $query = KabataanRegistration::onlyTrashed()
            ->with('barangay')
            ->forBarangay($user->barangay_id)
            ->where('status', 'active')
            ->whereIn('evaluation_status', ['active', 'Auto Approved'])
            ->orderByDesc('deleted_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('last_name', 'ilike', "%{$s}%")
                  ->orWhere('first_name', 'ilike', "%{$s}%");
            });
        }

        $filter = $request->get('filter', 'all');
        if ($filter === 'today') {
            $query->whereDate('deleted_at', now()->toDateString());
        } elseif ($filter === 'week') {
            $query->where('deleted_at', '>=', now()->startOfWeek());
        } elseif ($filter === 'month') {
            $query->where('deleted_at', '>=', now()->startOfMonth());
        }

        $records = $query->get();

        $val = fn($fd, $key) => is_array($fd[$key] ?? null)
            ? ($fd[$key][0] ?? '—')
            : ($fd[$key] ?? '—');

        $data = $records->map(function ($r) use ($val) {
            $fd = $r->form_data ?? [];
            $deletedAt = $r->deleted_at;

            return [
                'id'                => $r->id,
                'respondent_display' => RespondentNumberService::displaySequence(
                    $r->respondent_sequence,
                    $r->respondent_number
                ),
                'last_name'         => $r->last_name,
                'first_name'        => $r->first_name,
                'middle_name'       => $r->middle_name,
                'suffix'            => $r->suffix,
                'full_name'         => $r->full_name,
                'age'               => $val($fd, 'age'),
                'sex'               => $val($fd, 'sex'),
                'birthday'          => $val($fd, 'birthday'),
                'email'             => $r->email,
                'contact_number'    => $r->contact_number,
                'barangay'          => $r->barangay?->name ?? '—',
                'region'            => $r->barangay?->region ?? 'Region IV-A (CALABARZON)',
                'province'          => $r->barangay?->province ?? 'Laguna',
                'city'              => $r->barangay?->municipality ?? 'Santa Cruz',
                'purok_zone'        => $val($fd, 'purok_zone'),
                'education'         => $val($fd, 'education'),
                'civil_status'      => $val($fd, 'civil_status'),
                'youth_classification' => $val($fd, 'youth_classification'),
                'youth_age_group'   => $val($fd, 'youth_age_group'),
                'work_status'       => $val($fd, 'work_status'),
                'sk_voter'          => $val($fd, 'sk_voter'),
                'national_voter'    => $val($fd, 'national_voter'),
                'sk_voted'          => $val($fd, 'sk_voted'),
                'kk_times'          => $val($fd, 'kk_times'),
                'kk_assembly'       => $val($fd, 'kk_assembly'),
                'kk_reason'         => $val($fd, 'kk_reason'),
                'facebook'          => $val($fd, 'facebook'),
                'group_chat'        => $val($fd, 'group_chat'),
                'signature'         => $fd['signature'] ?? null,
                'submitted_at'      => $r->submitted_at?->format('m/d/Y'),
                'deleted_date'      => $deletedAt?->format('M d, Y') ?? '—',
                'deleted_time'      => $deletedAt?->format('h:i A') ?? '—',
                'deleted_at'        => $deletedAt?->toIso8601String(),
                'barangay_logo_url' => DB::table('barangay_logos')
                    ->where('barangay_id', $r->barangay_id)
                    ->value('url'),
            ];
        });

        $all = KabataanRegistration::onlyTrashed()
            ->forBarangay($user->barangay_id)
            ->where('status', 'active')
            ->whereIn('evaluation_status', ['active', 'Auto Approved'])
            ->get();

        $stats = [
            'total' => $all->count(),
            'today' => $all->filter(fn($r) => $r->deleted_at?->isToday())->count(),
            'month' => $all->filter(fn($r) => $r->deleted_at?->isCurrentMonth())->count(),
        ];

        return response()->json(['data' => $data, 'stats' => $stats]);
    }

    public function restore(int $id)
    {
        $user = Auth::user();
        $registration = KabataanRegistration::onlyTrashed()
            ->forBarangay($user->barangay_id)
            ->findOrFail($id);

        $registration->restore();

        $this->activityService->log(
            $user,
            'kabataan.restore',
            'Restored deleted Kabataan record: '.$registration->full_name,
            ['registration_id' => $id]
        );

        return response()->json([
            'success' => true,
            'message' => 'Record restored to Kabataan list.',
            'full_name' => $registration->full_name,
        ]);
    }

    private function emptyStats(): array
    {
        return ['total' => 0, 'today' => 0, 'month' => 0];
    }
}
