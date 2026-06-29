<?php

namespace App\Modules\PreviousKabataan\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Models\PreviousKabataan;
use App\Services\BarangayZoneService;
use App\Services\PreviousKabataanProfileMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PreviousKabataanController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('PreviousKabataan::previous-kabataan', [
            'barangayZones' => $user?->barangay_id
                ? app(BarangayZoneService::class)->activeZonesForBarangay((int) $user->barangay_id)
                : collect(),
        ]);
    }

    public function data(Request $request)
    {
        $user = Auth::user();
        $user->loadMissing('barangay');

        if (! $user || ! $user->barangay_id) {
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
                    ->orWhere('middle_name', 'ilike', "%{$q}%")
                    ->orWhere('email', 'ilike', "%{$q}%");
            });
        }

        if ($request->filled('purok')) {
            $purok = $request->purok;
            $query->where(function ($qb) use ($purok) {
                $qb->where('home_address', 'ilike', "%{$purok}%")
                    ->orWhereJsonContains('form_data->purok_zone', $purok)
                    ->orWhereJsonContains('form_data->purokZone', $purok);
            });
        }

        if ($request->filled('voter')) {
            $query->where('registered_voter', $request->voter);
        }

        $data = $query->get()->map(
            fn (PreviousKabataan $record) => PreviousKabataanProfileMapper::toApiArray($record)
        );

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
        $request->validate([
            'rows' => 'required|array|min:1',
            'replace_existing' => 'sometimes|boolean',
        ]);

        $user = Auth::user();
        $user->loadMissing('barangay');
        $year = now()->year;
        $count = 0;
        $barangayName = $user->barangay?->name;
        $replaceExisting = $request->boolean('replace_existing');

        DB::transaction(function () use ($request, $user, $year, $barangayName, $replaceExisting, &$count) {
            if ($replaceExisting) {
                PreviousKabataan::forBarangay($user->barangay_id)->delete();
            }

            foreach ($request->rows as $row) {
                $lastName = trim($row['last_name'] ?? $row['lastName'] ?? '');
                $firstName = trim($row['first_name'] ?? $row['firstName'] ?? '');
                $name = trim($row['name'] ?? '');

                if ($lastName === '' && $firstName === '' && $name === '') {
                    continue;
                }

                PreviousKabataan::create(
                    PreviousKabataanProfileMapper::buildCreateAttributes($row, [
                        'tenant_id' => $user->tenant_id,
                        'barangay_id' => $user->barangay_id,
                        'moved_by_user_id' => $user->id,
                        'profiling_year' => $row['year'] ?? $year,
                        'barangay_name' => $barangayName,
                    ])
                );
                $count++;
            }
        });

        return response()->json([
            'success' => true,
            'saved' => $count,
            'replaced' => $replaceExisting,
        ]);
    }

    /**
     * Called from KKProfilingRequests when approving and archiving.
     */
    public function moveFromActive(Request $request, int $registrationId)
    {
        $user = Auth::user();
        $user->loadMissing('barangay');

        $registration = KabataanRegistration::with('barangay')
            ->forBarangay($user->barangay_id)
            ->where('status', 'active')
            ->findOrFail($registrationId);

        DB::transaction(function () use ($registration, $user) {
            $formData = array_merge($registration->form_data ?? [], [
                'last_name' => $registration->last_name,
                'first_name' => $registration->first_name,
                'middle_name' => $registration->middle_name,
                'suffix' => $registration->suffix,
                'email' => $registration->email,
                'contact_number' => $registration->contact_number,
            ]);

            PreviousKabataan::create(
                PreviousKabataanProfileMapper::buildCreateAttributes($formData, [
                    'kabataan_registration_id' => $registration->id,
                    'tenant_id' => $registration->tenant_id,
                    'barangay_id' => $registration->barangay_id,
                    'moved_by_user_id' => $user->id,
                    'profiling_year' => now()->year,
                    'barangay_name' => $registration->barangay?->name,
                ])
            );

            $registration->update(['status' => 'archived']);
        });

        return response()->json(['success' => true]);
    }

    public function destroy(int $id)
    {
        $user = Auth::user();
        $user->loadMissing('barangay');

        PreviousKabataan::forBarangay($user->barangay_id)
            ->where('id', $id)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $user = Auth::user();
        $user->loadMissing('barangay');
        $ids = array_values(array_unique($validated['ids']));

        $deleted = PreviousKabataan::forBarangay($user->barangay_id)
            ->whereIn('id', $ids)
            ->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    }
}
