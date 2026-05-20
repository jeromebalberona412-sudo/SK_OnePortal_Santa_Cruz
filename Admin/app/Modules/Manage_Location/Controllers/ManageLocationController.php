<?php

namespace App\Modules\Manage_Location\Controllers;

use App\Modules\Shared\Controllers\Controller;
use App\Modules\Manage_Location\Models\Barangay;
use App\Modules\Manage_Location\Models\Purok;
use App\Modules\Manage_Location\Models\Sitio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ManageLocationController extends Controller
{
    /**
     * Display a listing of Barangay records with Purok and Sitio
     */
    public function index(Request $request)
    {
        try {
            $query = Barangay::query()->with(['puroks', 'sitios']);

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhereHas('puroks', function ($pq) use ($search) {
                          $pq->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('sitios', function ($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Barangay filter
            if ($request->filled('barangay_id')) {
                $query->where('id', $request->barangay_id);
            }

            $barangays = $query->orderBy('name', 'asc')->get();

            // Add computed attributes
            $barangays->each(function ($barangay) {
                $barangay->total_purok = $barangay->puroks->count();
                $barangay->total_sitio = $barangay->sitios->count();
            });

            return view('manage_location::manage_location', compact('barangays'));
        } catch (\Exception $e) {
            Log::error('Error loading Barangay records: ' . $e->getMessage());
            $barangays = collect();
            return view('manage_location::manage_location', compact('barangays'))
                ->with('error', 'Failed to load Barangay records. The database table may not exist yet.');
        }
    }

    /**
     * Show the details of a specific Barangay with Purok and Sitio
     */
    public function show($id)
    {
        try {
            $barangay = Barangay::with(['puroks', 'sitios'])->findOrFail($id);
            $barangay->total_purok = $barangay->puroks->count();
            $barangay->total_sitio = $barangay->sitios->count();

            return response()->json([
                'success' => true,
                'data' => $barangay
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Barangay details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Barangay details.'
            ], 500);
        }
    }

    /**
     * Store a new Barangay with Purok and Sitio
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:barangays,name',
                'municipality' => 'nullable|string|max:255',
                'province' => 'nullable|string|max:255',
                'region' => 'nullable|string|max:255',
                'status' => 'required|in:Active,Inactive',
                'puroks' => 'nullable|array',
                'puroks.*' => 'nullable|string|max:255',
                'sitios' => 'nullable|array',
                'sitios.*' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create Barangay
            $barangay = Barangay::create([
                'name' => $request->name,
                'municipality' => $request->municipality,
                'province' => $request->province,
                'region' => $request->region,
                'status' => $request->status,
            ]);

            // Create Puroks
            if ($request->filled('puroks')) {
                foreach ($request->puroks as $purokName) {
                    if (!empty(trim($purokName))) {
                        Purok::create([
                            'barangay_id' => $barangay->id,
                            'name' => trim($purokName),
                            'status' => 'Active',
                        ]);
                    }
                }
            }

            // Create Sitios
            if ($request->filled('sitios')) {
                foreach ($request->sitios as $sitioName) {
                    if (!empty(trim($sitioName))) {
                        Sitio::create([
                            'barangay_id' => $barangay->id,
                            'name' => trim($sitioName),
                            'status' => 'Active',
                        ]);
                    }
                }
            }

            DB::commit();

            $barangay->load(['puroks', 'sitios']);
            $barangay->total_purok = $barangay->puroks->count();
            $barangay->total_sitio = $barangay->sitios->count();

            return response()->json([
                'success' => true,
                'message' => 'Barangay created successfully.',
                'data' => $barangay
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating Barangay: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Barangay.'
            ], 500);
        }
    }

    /**
     * Update the specified Barangay with Purok and Sitio
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:barangays,name,' . $id,
                'municipality' => 'nullable|string|max:255',
                'province' => 'nullable|string|max:255',
                'region' => 'nullable|string|max:255',
                'status' => 'required|in:Active,Inactive',
                'puroks' => 'nullable|array',
                'puroks.*.id' => 'nullable|exists:puroks,id',
                'puroks.*.name' => 'nullable|string|max:255',
                'puroks.*.status' => 'nullable|in:Active,Inactive',
                'sitios' => 'nullable|array',
                'sitios.*.id' => 'nullable|exists:sitios,id',
                'sitios.*.name' => 'nullable|string|max:255',
                'sitios.*.status' => 'nullable|in:Active,Inactive',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $barangay = Barangay::findOrFail($id);
            $barangay->update([
                'name' => $request->name,
                'municipality' => $request->municipality,
                'province' => $request->province,
                'region' => $request->region,
                'status' => $request->status,
            ]);

            // Update or create Puroks
            if ($request->has('puroks')) {
                $existingPurokIds = [];
                foreach ($request->puroks as $purokData) {
                    if (is_array($purokData) && !empty($purokData['name'])) {
                        if (!empty($purokData['id'])) {
                            // Update existing
                            $purok = Purok::find($purokData['id']);
                            if ($purok && $purok->barangay_id == $barangay->id) {
                                $purok->update([
                                    'name' => $purokData['name'],
                                    'status' => $purokData['status'] ?? 'Active',
                                ]);
                                $existingPurokIds[] = $purok->id;
                            }
                        } else {
                            // Create new
                            $newPurok = Purok::create([
                                'barangay_id' => $barangay->id,
                                'name' => $purokData['name'],
                                'status' => $purokData['status'] ?? 'Active',
                            ]);
                            $existingPurokIds[] = $newPurok->id;
                        }
                    } elseif (is_string($purokData) && !empty(trim($purokData))) {
                        // Create new from string
                        $newPurok = Purok::create([
                            'barangay_id' => $barangay->id,
                            'name' => trim($purokData),
                            'status' => 'Active',
                        ]);
                        $existingPurokIds[] = $newPurok->id;
                    }
                }
                // Delete puroks not in the list
                Purok::where('barangay_id', $barangay->id)
                    ->whereNotIn('id', $existingPurokIds)
                    ->delete();
            }

            // Update or create Sitios
            if ($request->has('sitios')) {
                $existingSitioIds = [];
                foreach ($request->sitios as $sitioData) {
                    if (is_array($sitioData) && !empty($sitioData['name'])) {
                        if (!empty($sitioData['id'])) {
                            // Update existing
                            $sitio = Sitio::find($sitioData['id']);
                            if ($sitio && $sitio->barangay_id == $barangay->id) {
                                $sitio->update([
                                    'name' => $sitioData['name'],
                                    'status' => $sitioData['status'] ?? 'Active',
                                ]);
                                $existingSitioIds[] = $sitio->id;
                            }
                        } else {
                            // Create new
                            $newSitio = Sitio::create([
                                'barangay_id' => $barangay->id,
                                'name' => $sitioData['name'],
                                'status' => $sitioData['status'] ?? 'Active',
                            ]);
                            $existingSitioIds[] = $newSitio->id;
                        }
                    } elseif (is_string($sitioData) && !empty(trim($sitioData))) {
                        // Create new from string
                        $newSitio = Sitio::create([
                            'barangay_id' => $barangay->id,
                            'name' => trim($sitioData),
                            'status' => 'Active',
                        ]);
                        $existingSitioIds[] = $newSitio->id;
                    }
                }
                // Delete sitios not in the list
                Sitio::where('barangay_id', $barangay->id)
                    ->whereNotIn('id', $existingSitioIds)
                    ->delete();
            }

            DB::commit();

            $barangay->load(['puroks', 'sitios']);
            $barangay->total_purok = $barangay->puroks->count();
            $barangay->total_sitio = $barangay->sitios->count();

            return response()->json([
                'success' => true,
                'message' => 'Barangay updated successfully.',
                'data' => $barangay
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Barangay: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Barangay.'
            ], 500);
        }
    }

    /**
     * Remove the specified Barangay (soft delete)
     */
    public function destroy($id)
    {
        try {
            $barangay = Barangay::findOrFail($id);
            $barangay->delete();

            return response()->json([
                'success' => true,
                'message' => 'Barangay archived successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error archiving Barangay: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive Barangay.'
            ], 500);
        }
    }
}
