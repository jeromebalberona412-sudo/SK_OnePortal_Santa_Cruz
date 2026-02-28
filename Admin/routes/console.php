<?php

use App\Modules\Accounts\Models\Barangay;
use App\Modules\Shared\Models\Tenant;
use App\Modules\Shared\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('accounts:align-admin-tenants {--tenant-code=santa_cruz} {--force}', function () {
    $tenantCode = (string) $this->option('tenant-code');

    $targetTenant = Tenant::query()->firstOrCreate(
        ['code' => $tenantCode],
        [
            'name' => 'Santa Cruz Federation',
            'municipality' => 'Santa Cruz',
            'province' => 'Laguna',
            'region' => 'IV-A CALABARZON',
            'is_active' => true,
        ]
    );

    $adminTenantIds = User::query()
        ->where('role', User::ROLE_ADMIN)
        ->whereNotNull('tenant_id')
        ->distinct()
        ->pluck('tenant_id')
        ->map(fn ($id) => (int) $id)
        ->values();

    $sourceTenantIds = $adminTenantIds
        ->filter(fn (int $tenantId) => $tenantId !== (int) $targetTenant->id)
        ->values();

    $adminsWithoutTenant = User::query()
        ->where('role', User::ROLE_ADMIN)
        ->whereNull('tenant_id')
        ->count();

    if ($sourceTenantIds->isEmpty() && $adminsWithoutTenant === 0) {
        $this->info('All admin users are already aligned to tenant code: '.$tenantCode);

        return self::SUCCESS;
    }

    $sourceTenantList = $sourceTenantIds->isEmpty() ? 'none' : $sourceTenantIds->implode(', ');

    $this->warn('This command will move tenant-scoped records to tenant '.$targetTenant->id.' ('.$tenantCode.').');
    $this->line('Source tenant IDs to merge: '.$sourceTenantList);
    $this->line('Admins with NULL tenant_id: '.$adminsWithoutTenant);

    if (! $this->option('force') && ! $this->confirm('Proceed with tenant alignment?', false)) {
        $this->comment('Cancelled.');

        return self::SUCCESS;
    }

    $stats = [
        'users_updated' => 0,
        'official_profiles_updated' => 0,
        'activity_logs_updated' => 0,
        'admins_null_tenant_fixed' => 0,
        'barangays_created' => 0,
    ];

    DB::transaction(function () use ($sourceTenantIds, $targetTenant, $adminsWithoutTenant, &$stats) {
        $targetBarangays = Barangay::query()
            ->where('tenant_id', $targetTenant->id)
            ->get()
            ->keyBy(fn (Barangay $barangay) => mb_strtolower(trim($barangay->name)));

        $barangayIdMap = [];

        foreach ($sourceTenantIds as $sourceTenantId) {
            $sourceBarangays = Barangay::query()
                ->where('tenant_id', $sourceTenantId)
                ->get();

            foreach ($sourceBarangays as $sourceBarangay) {
                $normalizedName = mb_strtolower(trim($sourceBarangay->name));
                $targetBarangay = $targetBarangays->get($normalizedName);

                if (! $targetBarangay) {
                    $targetBarangay = Barangay::query()->create([
                        'tenant_id' => $targetTenant->id,
                        'name' => $sourceBarangay->name,
                        'municipality' => $sourceBarangay->municipality ?: $targetTenant->municipality,
                        'province' => $sourceBarangay->province ?: $targetTenant->province,
                        'region' => $sourceBarangay->region ?: $targetTenant->region,
                    ]);

                    $targetBarangays->put($normalizedName, $targetBarangay);
                    $stats['barangays_created']++;
                }

                $barangayIdMap[(int) $sourceBarangay->id] = (int) $targetBarangay->id;
            }

            $users = User::query()->where('tenant_id', $sourceTenantId)->get(['id', 'barangay_id']);

            foreach ($users as $user) {
                $newBarangayId = $user->barangay_id ? ($barangayIdMap[(int) $user->barangay_id] ?? null) : null;

                $affected = User::query()
                    ->where('id', $user->id)
                    ->update([
                        'tenant_id' => $targetTenant->id,
                        'barangay_id' => $newBarangayId,
                    ]);

                $stats['users_updated'] += $affected;
            }

            $stats['official_profiles_updated'] += DB::table('official_profiles')
                ->where('tenant_id', $sourceTenantId)
                ->update(['tenant_id' => $targetTenant->id]);

            $stats['activity_logs_updated'] += DB::table('admin_activity_logs')
                ->where('tenant_id', $sourceTenantId)
                ->update(['tenant_id' => $targetTenant->id]);
        }

        if ($adminsWithoutTenant > 0) {
            $stats['admins_null_tenant_fixed'] = User::query()
                ->where('role', User::ROLE_ADMIN)
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $targetTenant->id]);
        }
    });

    $this->info('Tenant alignment completed.');
    $this->table(
        ['Metric', 'Count'],
        [
            ['Users moved', $stats['users_updated']],
            ['Official profiles moved', $stats['official_profiles_updated']],
            ['Admin activity logs moved', $stats['activity_logs_updated']],
            ['Admins fixed from NULL tenant', $stats['admins_null_tenant_fixed']],
            ['Barangays created in target tenant', $stats['barangays_created']],
        ]
    );

    return self::SUCCESS;
})->purpose('One-time: align all admin-scoped tenant data to a single tenant');
