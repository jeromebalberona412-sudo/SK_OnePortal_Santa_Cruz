<?php

namespace App\Modules\Kabataan\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Services\BarangayLogoUrlService;
use App\Services\RespondentNumberService;
use App\Services\SkOfficialActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KabataanController extends Controller
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

            $barangayLogoUrl = app(BarangayLogoUrlService::class)->resolve($user->barangay_id);
        }

        return view('Kabataan::kabataan', [
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

        $query = KabataanRegistration::with('barangay')
            ->forBarangay($user->barangay_id)
            ->where('status', 'active')
            ->whereIn('evaluation_status', ['active', 'Auto Approved'])
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('last_name', 'ilike', "%{$s}%")
                  ->orWhere('first_name', 'ilike', "%{$s}%")
                  ->orWhere('respondent_number', 'ilike', "%{$s}%");
            });
        }

        $records = $query->get();

        $val = fn($fd, $key) => is_array($fd[$key] ?? null)
            ? ($fd[$key][0] ?? '—')
            : ($fd[$key] ?? '—');

        $data = $records->map(function ($r) use ($val) {
            $fd = $r->form_data ?? [];
            return [
                'id'             => $r->id,
                'respondent_no'       => RespondentNumberService::displaySequence(
                    $r->respondent_sequence,
                    $r->respondent_number
                ),
                'respondent_sequence' => $r->respondent_sequence,
                'last_name'      => $r->last_name,
                'first_name'     => $r->first_name,
                'middle_name'    => $r->middle_name,
                'suffix'         => $r->suffix,
                'full_name'      => $r->full_name,
                'age'            => $val($fd, 'age'),
                'sex'            => $val($fd, 'sex'),
                'birthday'       => $val($fd, 'birthday'),
                'email'          => $r->email,
                'contact_number' => $r->contact_number,
                'barangay'       => $r->barangay?->name ?? '—',
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
                'facebook'       => $val($fd, 'facebook'),
                'group_chat'     => $val($fd, 'group_chat'),
                'signature'      => $fd['signature'] ?? null,
                'submitted_at'   => $r->submitted_at?->format('m/d/Y'),
                'reviewed_at'    => $r->reviewed_at?->format('m/d/Y'),
                'evaluation_status' => $r->evaluation_status,
            ];
        });

        $all = KabataanRegistration::forBarangay($user->barangay_id)->get();
        $stats = [
            'active'   => $all->where('status', 'active')
                ->whereIn('evaluation_status', ['active', 'Auto Approved'])->count(),
            'pending'  => $all->whereIn('evaluation_status', ['Not Profiled', 'Wrong Credentials'])
                ->whereNotIn('status', ['rejected'])->count(),
            'rejected' => $all->where('status', 'rejected')->count(),
            'total'    => $all->count(),
        ];

        return response()->json(['data' => $data, 'stats' => $stats]);
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

    public function destroy(int $id)
    {
        $user = Auth::user();
        $registration = KabataanRegistration::forBarangay($user->barangay_id)->findOrFail($id);
        $fullName = $registration->full_name;
        $registration->delete();

        $this->activityService->log(
            $user,
            'kabataan.delete',
            'Deleted Kabataan record: '.$fullName,
            ['registration_id' => $id]
        );

        return response()->json(['success' => true, 'message' => 'Kabataan record moved to Deleted Items.']);
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $user = Auth::user();
        $ids = array_values(array_unique($validated['ids']));

        $registrations = KabataanRegistration::forBarangay($user->barangay_id)
            ->whereIn('id', $ids)
            ->get();

        if ($registrations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No matching Kabataan records were found.',
            ], 422);
        }

        foreach ($registrations as $registration) {
            $fullName = $registration->full_name;
            $registrationId = $registration->id;
            $registration->delete();

            $this->activityService->log(
                $user,
                'kabataan.delete',
                'Deleted Kabataan record: '.$fullName,
                ['registration_id' => $registrationId]
            );
        }

        return response()->json([
            'success' => true,
            'message' => $registrations->count().' Kabataan record(s) moved to Deleted Items.',
            'deleted_count' => $registrations->count(),
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
