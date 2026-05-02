<?php

namespace App\Modules\BarangayLogos\Controllers;

use App\Modules\Accounts\Models\Barangay;
use App\Modules\BarangayLogos\Models\BarangayLogo;
use App\Modules\BarangayLogos\Requests\BarangayLogoRequest;
use App\Modules\BarangayLogos\Services\CloudinaryService;
use App\Modules\Shared\Controllers\Controller;
use App\Modules\Shared\Models\Tenant;
use App\Modules\Shared\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class BarangayLogoController extends Controller
{
    public function __construct(private readonly CloudinaryService $cloudinary)
    {
    }

    /**
     * Display the barangay logos management page.
     */
    public function index(Request $request): View
    {
        $user     = $request->user();
        $tenantId = $this->resolveTenantId($user);

        $barangays = Barangay::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        $logos = BarangayLogo::query()
            ->where('tenant_id', $tenantId)
            ->get()
            ->keyBy('barangay_id');

        return view('barangay_logos::barangay-logos', [
            'user'      => $user,
            'barangays' => $barangays,
            'logos'     => $logos,
        ]);
    }

    /**
     * Upload (or replace) a barangay logo to Cloudinary.
     */
    public function upload(BarangayLogoRequest $request): JsonResponse
    {
        $user      = $request->user();
        $tenantId  = $this->resolveTenantId($user);
        $barangayId = (int) $request->input('barangay_id');

        // Verify the barangay belongs to this tenant
        $barangay = Barangay::query()
            ->where('id', $barangayId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $existing = BarangayLogo::query()
            ->where('barangay_id', $barangayId)
            ->where('tenant_id', $tenantId)
            ->first();

        try {
            // Delete old Cloudinary asset if replacing
            if ($existing) {
                $this->cloudinary->delete($existing->cloudinary_public_id);
            }

            $publicId = 'barangay_' . $barangayId . '_tenant_' . $tenantId;
            $result   = $this->cloudinary->upload($request->file('logo'), $publicId);

            $logo = BarangayLogo::updateOrCreate(
                ['barangay_id' => $barangayId, 'tenant_id' => $tenantId],
                [
                    'uploaded_by'          => $user->id,
                    'cloudinary_public_id' => $result['public_id'],
                    'url'                  => $result['url'],
                ]
            );

            return response()->json([
                'id'  => $logo->id,
                'url' => $logo->url,
            ]);
        } catch (Throwable $e) {
            Log::error('Cloudinary upload failed', [
                'barangay_id' => $barangayId,
                'error'       => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Upload failed. Please try again.'], 500);
        }
    }

    /**
     * Delete a barangay logo from Cloudinary and the database.
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        $user     = $request->user();
        $tenantId = $this->resolveTenantId($user);

        $logo = BarangayLogo::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        try {
            $this->cloudinary->delete($logo->cloudinary_public_id);
            $logo->delete();

            return response()->json(['message' => 'Logo removed.']);
        } catch (Throwable $e) {
            Log::error('Cloudinary delete failed', [
                'logo_id' => $id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Delete failed. Please try again.'], 500);
        }
    }

    private function resolveTenantId(User $admin): int
    {
        if ($admin->tenant_id !== null) {
            return $admin->tenant_id;
        }

        $tenant = Tenant::query()->firstOrCreate(
            ['code' => 'santa_cruz'],
            [
                'name'         => 'Santa Cruz Federation',
                'municipality' => 'Santa Cruz',
                'province'     => 'Laguna',
                'region'       => 'IV-A CALABARZON',
                'is_active'    => true,
            ]
        );

        $admin->forceFill(['tenant_id' => $tenant->id])->save();

        return $tenant->id;
    }
}
