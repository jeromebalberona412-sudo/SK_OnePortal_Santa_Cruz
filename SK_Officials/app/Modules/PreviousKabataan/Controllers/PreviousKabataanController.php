<?php

namespace App\Modules\PreviousKabataan\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Models\PreviousKabataan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PreviousKabataanController extends Controller
{
    public function index()
    {
        return view('PreviousKabataan::previous-kabataan');
    }

    public function data(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->barangay_id) {
            return response()->json(['data' => [], 'years' => []]);
        }

        $query = PreviousKabataan::with('barangay')
            ->forBarangay($user->barangay_id)
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($request->filled('year')) {
            $query->where('profiling_year', $request->year);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('last_name', 'ilike', "%{$q}%")
                   ->orWhere('first_name', 'ilike', "%{$q}%")
                   ->orWhere('email', 'ilike', "%{$q}%");
            });
        }

        if ($request->filled('purok')) {
            $query->whereJsonContains('form_data->purok_zone', $request->purok);
        }

        if ($request->filled('voter')) {
            $query->whereJsonContains('form_data->sk_voter', $request->voter);
        }

        $records = $query->get();

        $val = fn($fd, $key) => is_array($fd[$key] ?? null)
            ? ($fd[$key][0] ?? '—')
            : ($fd[$key] ?? $fd[lcfirst(str_replace('_', '', ucwords($key, '_')))] ?? '—');

        $data = $records->map(function ($r) use ($val) {
            $fd = $r->form_data ?? [];
            // Helper that checks both snake_case and camelCase
            $get = fn($snake, $camel) => $fd[$camel] ?? $fd[$snake] ?? '—';
            return [
                'id'                   => $r->id,
                'respondent_no'        => 'PK-' . $r->profiling_year . '-' . str_pad($r->id, 3, '0', STR_PAD_LEFT),
                'profiling_year'       => $r->profiling_year,
                'name'                 => $fd['name'] ?? null,
                'last_name'            => $r->last_name,
                'first_name'           => $r->first_name,
                'middle_name'          => $r->middle_name,
                'suffix'               => $r->suffix,
                'full_name'            => $r->full_name,
                'age'                  => $get('age', 'age'),
                'birthday'             => $get('birthday', 'birthday'),
                'sex'                  => $get('sex', 'sex'),
                'email'                => $r->email,
                'contact_number'       => $r->contact_number ?? $fd['contact'] ?? '—',
                'barangay'             => $r->barangay?->name ?? '—',
                'home_address'         => $fd['homeAddress'] ?? $fd['home_address'] ?? null,
                'purok_zone'           => $fd['purokZone'] ?? $fd['purok_zone'] ?? '—',
                'sk_voter'             => $get('sk_voter', 'skVoter'),
                'national_voter'       => $get('national_voter', 'natVoter'),
                'civil_status'         => $get('civil_status', 'civilStatus'),
                'youth_classification' => $get('youth_classification', 'youthClassification'),
                'youth_age_group'      => $get('youth_age_group', 'youthAgeGroup'),
                'work_status'          => $get('work_status', 'workStatus'),
                'education'            => $get('education', 'education'),
                'sk_voted'             => $get('sk_voted', 'votingHistory'),
                'vote_frequency'       => $get('vote_frequency', 'votingFrequency'),
                'kk_assembly'          => $get('kk_assembly', 'kkAssembly'),
                'kk_reason'            => is_array($fd['kk_reason'] ?? null) ? $fd['kk_reason'] : ($fd['votingReason'] ?? []),
                'facebook'             => $get('facebook', 'facebook'),
                'group_chat'           => $get('group_chat', 'groupChat'),
                'signature'            => $get('signature', 'signature'),
                'date'                 => $r->moved_at?->format('m/d/Y') ?? $r->created_at?->format('m/d/Y'),
                'region'               => $fd['region'] ?? 'Region IV-A (CALABARZON)',
                'province'             => $fd['province'] ?? 'Laguna',
                'city'                 => $fd['city'] ?? 'Santa Cruz',
            ];
        });

        // Available years for filter dropdown
        $years = PreviousKabataan::forBarangay($user->barangay_id)
            ->distinct()
            ->orderByDesc('profiling_year')
            ->pluck('profiling_year');

        return response()->json(['data' => $data, 'years' => $years]);
    }

    /**
     * Bulk upload from Excel (parsed client-side, rows POSTed as JSON)
     */
    public function upload(Request $request)
    {
        $request->validate(['rows' => 'required|array|min:1']);

        $user = Auth::user();
        $year = now()->year;
        $count = 0;

        // Process in chunks to avoid memory issues with large files
        foreach (array_chunk($request->rows, 100) as $chunk) {
            foreach ($chunk as $row) {
                $name      = trim($row['name'] ?? '');
                $lastName  = trim($row['last_name'] ?? $row['lastName'] ?? '');
                $firstName = trim($row['first_name'] ?? $row['firstName'] ?? '');

                if (!$lastName && !$firstName && !$name) continue;
                if (!$lastName && $name) $lastName = $name;

                PreviousKabataan::create([
                    'tenant_id'        => $user->tenant_id,
                    'barangay_id'      => $user->barangay_id,
                    'moved_by_user_id' => $user->id,
                    'last_name'        => $lastName,
                    'first_name'       => $firstName ?: $name,
                    'middle_name'      => $row['middle_name'] ?? $row['middleName'] ?? null,
                    'suffix'           => $row['suffix'] ?? null,
                    'email'            => $row['email'] ?? null,
                    'contact_number'   => $row['contact'] ?? $row['contact_number'] ?? null,
                    'form_data'        => $row,
                    'profiling_year'   => $row['year'] ?? $year,
                    'moved_at'         => now(),
                ]);
                $count++;
            }
        }

        return response()->json(['success' => true, 'saved' => $count]);
    }

    /**
     * Called from KKProfilingRequests when approving and archiving.
     */
    public function moveFromActive(Request $request, int $registrationId)
    {
        $user = Auth::user();

        $registration = KabataanRegistration::forBarangay($user->barangay_id)
            ->where('status', 'active')
            ->findOrFail($registrationId);

        DB::transaction(function () use ($registration, $user) {
            PreviousKabataan::create([
                'kabataan_registration_id' => $registration->id,
                'tenant_id'                => $registration->tenant_id,
                'barangay_id'              => $registration->barangay_id,
                'moved_by_user_id'         => $user->id,
                'last_name'                => $registration->last_name,
                'first_name'               => $registration->first_name,
                'middle_name'              => $registration->middle_name,
                'suffix'                   => $registration->suffix,
                'email'                    => $registration->email,
                'contact_number'           => $registration->contact_number,
                'form_data'                => $registration->form_data,
                'profiling_year'           => now()->year,
                'moved_at'                 => now(),
            ]);

            // Mark the original registration as archived
            $registration->update(['status' => 'archived']);
        });

        return response()->json(['success' => true]);
    }
}
