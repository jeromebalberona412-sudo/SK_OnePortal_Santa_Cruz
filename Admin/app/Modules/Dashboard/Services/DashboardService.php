<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Accounts\Database\Seeders\BarangaySeeder;
use App\Modules\Accounts\Models\Barangay;
use App\Modules\Manage_Kabataan\Models\Kabataan;
use App\Modules\Shared\Models\Tenant;
use App\Modules\Shared\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    /**
     * @return array{
     *     totalUsers: int,
     *     federationAccounts: int,
     *     officialAccounts: int,
     *     kabataanAccounts: int
     * }
     */
    public function getAccountMetrics(User $admin): array
    {
        $tenantId = $this->resolveTenantId($admin);

        $federationCount = User::query()
            ->where('tenant_id', $tenantId)
            ->where('role', User::ROLE_SK_FED)
            ->count();

        $officialCount = User::query()
            ->where('tenant_id', $tenantId)
            ->where('role', User::ROLE_SK_OFFICIAL)
            ->count();

        $kabataanCount = $this->getKabataanCount($tenantId);

        return [
            'totalUsers' => $federationCount + $officialCount + $kabataanCount,
            'federationAccounts' => $federationCount,
            'officialAccounts' => $officialCount,
            'kabataanAccounts' => $kabataanCount,
        ];
    }

    private function getKabataanCount(int $tenantId): int
    {
        if (! Schema::hasTable('kabataan')) {
            return 0;
        }

        $barangayIds = Barangay::query()
            ->where('tenant_id', $tenantId)
            ->pluck('id');

        $query = Kabataan::query();

        if ($barangayIds->isNotEmpty()) {
            $query->whereIn('barangay_id', $barangayIds);
        }

        return $query->count();
    }

    /**
     * @return Collection<int, Barangay>
     */
    public function getBarangays(User $admin): Collection
    {
        $tenantId = $this->resolveTenantId($admin);
        $this->ensureTenantBarangays($tenantId);

        return Barangay::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function ensureTenantBarangays(int $tenantId): void
    {
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            return;
        }

        if (Barangay::query()->where('tenant_id', $tenantId)->exists()) {
            return;
        }

        BarangaySeeder::seedTenant($tenant);
    }

    private function resolveTenantId(User $admin): int
    {
        if ($admin->tenant_id !== null) {
            return (int) $admin->tenant_id;
        }

        $tenant = Tenant::query()->firstOrCreate(
            ['code' => 'santa_cruz'],
            [
                'name' => 'Santa Cruz Federation',
                'municipality' => 'Santa Cruz',
                'province' => 'Laguna',
                'region' => 'IV-A CALABARZON',
                'is_active' => true,
            ]
        );

        $admin->forceFill(['tenant_id' => $tenant->id])->save();

        return (int) $tenant->id;
    }
}
