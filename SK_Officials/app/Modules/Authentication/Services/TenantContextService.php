<?php

namespace App\Modules\Authentication\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantContextService
{
    protected ?int $resolvedTenantId = null;

    public function tenantId(): ?int
    {
        if ($this->resolvedTenantId !== null) {
            return $this->resolvedTenantId;
        }

        $tenantCode = (string) config('sk_official_auth.tenant_code', 'santa_cruz');

        try {
            if (! Schema::hasTable('tenants')) {
                return null;
            }

            $tenant = DB::table('tenants')
                ->where('code', $tenantCode)
                ->first();

            if ($tenant === null) {
                return null;
            }

            $this->resolvedTenantId = (int) $tenant->id;

            return $this->resolvedTenantId;
        } catch (\Throwable) {
            return null;
        }
    }
}
