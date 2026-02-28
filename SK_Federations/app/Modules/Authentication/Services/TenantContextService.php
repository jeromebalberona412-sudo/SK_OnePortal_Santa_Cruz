<?php

namespace App\Modules\Authentication\Services;

use Illuminate\Support\Facades\DB;

class TenantContextService
{
    protected ?int $cachedTenantId = null;

    public function tenantId(): ?int
    {
        if ($this->cachedTenantId !== null) {
            return $this->cachedTenantId;
        }

        $tenantCode = config('sk_fed_auth.tenant_code');

        if (! is_string($tenantCode) || $tenantCode === '') {
            return null;
        }

        $tenant = DB::table('tenants')->where('code', $tenantCode)->first();

        if ($tenant === null) {
            return null;
        }

        $this->cachedTenantId = (int) $tenant->id;

        return $this->cachedTenantId;
    }
}
