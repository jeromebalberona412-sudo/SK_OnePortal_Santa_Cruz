<?php

namespace App\Modules\Manage_Kabataan\Controllers;

use App\Modules\Shared\Controllers\Controller;
use App\Modules\Manage_Kabataan\Models\Kabataan;
use App\Modules\Accounts\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ManageKabataanController extends Controller
{
    /**
     * Display a listing of Kabataan records
     */
    public function index(Request $request)
    {
        try {
            $query = Kabataan::query()->with('barangay');

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('kk_number', 'like', "%{$search}%")
                      ->orWhere('contact_number', 'like', "%{$search}%");
                });
            }

            // Barangay filter
            if ($request->filled('barangay_id')) {
                $query->where('barangay_id', $request->barangay_id);
            }

            // Gender filter
            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            // Age range filter
            if ($request->filled('age_min')) {
                $query->where('age', '>=', $request->age_min);
            }
            if ($request->filled('age_max')) {
                $query->where('age', '<=', $request->age_max);
            }

            // Voter status filter (maps to national_voter column)
            if ($request->filled('voter_status')) {
                $query->where('national_voter', $request->voter_status);
            }

            // Account status filter
            if ($request->filled('account_status')) {
                $query->where('account_status', $request->account_status);
            }

            // Verification status filter
            if ($request->filled('verification_status')) {
                $query->where('verification_status', $request->verification_status);
            }

            $kabataan = $query->orderBy('created_at', 'desc')->get();
            $barangays = Barangay::orderBy('name')->get();

            // Inject frontend-only sample data when table is empty
            if ($kabataan->isEmpty()) {
                $kabataan = collect([
                    (object)[
                        'id'                     => 0,
                        'first_name'             => 'Juan',
                        'last_name'              => 'Dela Cruz',
                        'middle_name'            => 'Santos',
                        'suffix'                 => null,
                        'kk_number'              => 'KK-2024-00001',
                        'age'                    => 19,
                        'gender'                 => 'Male',
                        'birthday'               => '2005-03-15',
                        'barangay_id'            => null,
                        'barangay'               => (object)['name' => 'Poblacion I'],
                        'purok_zone'             => 'Purok 2',
                        'contact_number'         => '09171234567',
                        'email'                  => 'juan.delacruz@example.com',
                        'youth_classification'   => 'In-School Youth',
                        'educational_background' => 'College Level',
                        'work_status'            => 'Student',
                        'civil_status'           => 'Single',
                        'sk_voter'               => 'Yes',
                        'national_voter'         => 'Registered',
                        'kk_assembly_attendance' => null,
                        'account_status'         => 'Active',
                        'verification_status'    => 'Verified',
                    ],
                    (object)[
                        'id'                     => 0,
                        'first_name'             => 'Maria',
                        'last_name'              => 'Reyes',
                        'middle_name'            => 'Bautista',
                        'suffix'                 => null,
                        'kk_number'              => 'KK-2024-00002',
                        'age'                    => 22,
                        'gender'                 => 'Female',
                        'birthday'               => '2002-07-28',
                        'barangay_id'            => null,
                        'barangay'               => (object)['name' => 'San Juan'],
                        'purok_zone'             => 'Purok 5',
                        'contact_number'         => '09281234567',
                        'email'                  => 'maria.reyes@example.com',
                        'youth_classification'   => 'Working Youth',
                        'educational_background' => 'College Graduate',
                        'work_status'            => 'Employed',
                        'civil_status'           => 'Single',
                        'sk_voter'               => 'Yes',
                        'national_voter'         => 'Registered',
                        'kk_assembly_attendance' => null,
                        'account_status'         => 'Active',
                        'verification_status'    => 'Verified',
                    ],
                ]);
            }

            return view('manage_kabataan::manage_kabataan', compact('kabataan', 'barangays'));
        } catch (\Exception $e) {
            Log::error('Error loading Kabataan records: ' . $e->getMessage());
            $kabataan = collect();
            $barangays = collect();
            return view('manage_kabataan::manage_kabataan', compact('kabataan', 'barangays'))
                ->with('error', 'Failed to load Kabataan records. The database table may not exist yet.');
        }
    }

    /**
     * Show the details of a specific Kabataan
     */
    public function show($id)
    {
        try {
            $kabataan = Kabataan::with('barangay')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $kabataan
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Kabataan details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Kabataan details.'
            ], 500);
        }
    }

    /**
     * Update the specified Kabataan record
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'suffix' => 'nullable|string|max:50',
                'kk_number' => 'required|string|max:100|unique:kabataan,kk_number,' . $id,
                'age' => 'required|integer|min:15|max:30',
                'gender' => 'required|in:Male,Female',
                'birthday' => 'required|date',
                'barangay_id' => 'required|exists:barangays,id',
                'purok_zone' => 'nullable|string|max:255',
                'contact_number' => 'required|string|max:20',
                'email' => 'required|email|max:255|unique:kabataan,email,' . $id,
                'youth_classification' => 'required|string|max:255',
                'educational_background' => 'nullable|string|max:255',
                'work_status' => 'nullable|string|max:255',
                'civil_status' => 'nullable|string|max:50',
                'sk_voter' => 'nullable|in:Yes,No',
                'national_voter' => 'nullable|in:Registered,Not Registered',
                'kk_assembly_attendance' => 'nullable|string|max:255',
                'account_status' => 'required|in:Active,Inactive',
                'verification_status' => 'required|in:Verified,Unverified',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $kabataan = Kabataan::findOrFail($id);
            $kabataan->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Kabataan record updated successfully.',
                'data' => $kabataan->load('barangay')
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating Kabataan record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Kabataan record.'
            ], 500);
        }
    }

    /**
     * Remove the specified Kabataan record
     */
    public function destroy($id)
    {
        try {
            $kabataan = Kabataan::findOrFail($id);
            $kabataan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kabataan record deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting Kabataan record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Kabataan record.'
            ], 500);
        }
    }
}
