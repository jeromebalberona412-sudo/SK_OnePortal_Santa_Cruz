<?php

namespace App\Modules\Archive_Management\Controllers;

use App\Modules\Archive_Management\Services\TermRecordsArchiveService;
use App\Modules\Shared\Controllers\Controller;
use App\Modules\Shared\Models\Tenant;
use App\Modules\Shared\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TermRecordsArchiveController extends Controller
{
    public function __construct(private readonly TermRecordsArchiveService $termRecordsArchiveService)
    {
    }

    public function officialsData(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request->user());

        return response()->json(
            $this->termRecordsArchiveService->listOfficialRecords($tenantId, $request)
        );
    }

    public function federationData(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request->user());

        return response()->json(
            $this->termRecordsArchiveService->listFederationRecords($tenantId, $request)
        );
    }

    private function resolveTenantId(User $user): int
    {
        if ($user->tenant_id) {
            return (int) $user->tenant_id;
        }

        return (int) Tenant::query()->where('code', 'santa_cruz')->value('id');
    }
}
