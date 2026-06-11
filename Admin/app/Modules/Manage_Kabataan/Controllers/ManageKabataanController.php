<?php

namespace App\Modules\Manage_Kabataan\Controllers;

use App\Modules\Accounts\Models\Barangay;
use App\Modules\Manage_Kabataan\Models\KabataanRegistration;
use App\Modules\Manage_Kabataan\Services\ManageKabataanService;
use App\Modules\Shared\Controllers\Controller;
use App\Modules\Shared\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManageKabataanController extends Controller
{
    public function __construct(private readonly ManageKabataanService $service)
    {
    }

    public function index(Request $request): View
    {
        $tenantId = $this->resolveTenantId($request->user());

        $barangays = Barangay::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('manage-kabataan::kabataan', [
            'barangays' => $barangays,
            'routes' => [
                'data' => route('manage-kabataan.data'),
                'show' => route('manage-kabataan.show', ['id' => '__ID__']),
            ],
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request->user());
        $records = $this->service
            ->listQuery($request, $tenantId)
            ->get();

        return response()->json([
            'data' => $records->map(fn (KabataanRegistration $row) => $this->service->mapListRow($row))->values(),
            'stats' => $this->service->summarize($records),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request->user());

        $registration = KabataanRegistration::query()
            ->with('barangay')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('submitted_at')
            ->findOrFail($id);

        return response()->json([
            'data' => $this->service->mapDetailRow($registration),
        ]);
    }

    private function resolveTenantId($user): int
    {
        if ($user?->tenant_id) {
            return (int) $user->tenant_id;
        }

        return (int) Tenant::query()->value('id');
    }
}
