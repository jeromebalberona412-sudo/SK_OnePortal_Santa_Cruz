<?php

namespace App\Modules\Authentication\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantContextService
{
    protected ?int $resolvedTenantId = null;

    public function tenantId(): ?int
    {
        // Return instance-level cached value for the lifetime of this request
        if ($this->resolvedTenantId !== null) {
            return $this->resolvedTenantId;
        }

        $tenantCode = (string) config('sk_official_auth.tenant_code', 'santa_cruz');

        // Use a persistent cache so the tenant lookup only hits the DB once
        // per cache lifetime (1 hour) instead of on every login request.
        $cacheKey = "sk_official_tenant_id:{$tenantCode}";

        $tenantId = Cache::remember($cacheKey, 3600, function () use ($tenantCode) {
            try {
                if (! Schema::hasTable('tenants')) {
                    return 1; // No tenants table — use default
                }

                $tenant = DB::table('tenants')
                    ->where('code', $tenantCode)
                    ->value('id');

                return $tenant !== null ? (int) $tenant : null;
            } catch (\Throwable) {
                return null;
            }
        });

        $this->resolvedTenantId = $tenantId;

        return $this->resolvedTenantId;
    }
}
