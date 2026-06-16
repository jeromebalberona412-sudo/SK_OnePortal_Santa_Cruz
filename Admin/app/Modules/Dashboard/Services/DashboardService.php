<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Shared\Database\Seeders\BarangaySeeder;
use App\Modules\Shared\Models\Barangay;
use App\Modules\Shared\Models\OfficialTerm;
use App\Modules\Shared\Models\ArchivedSkFederationRecord;
use App\Modules\Shared\Models\ArchivedSkOfficialRecord;
use App\Modules\Shared\Models\Tenant;
use App\Modules\Shared\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    /**
     * @return array{
     *     totalUsers: int,
     *     federationAccounts: int,
     *     officialAccounts: int,
     *     kabataanAccounts: int,
     *     deletedSkFederation: int,
     *     deletedSkOfficials: int,
     *     skFederationRecords: int,
     *     skOfficialsRecords: int
     * }
     */
    /**
     * @param  array{year?: string, term?: string}  $filters
     */
    public function getAccountMetrics(User $admin, array $filters = []): array
    {
        $tenantId = $this->resolveTenantId($admin);
        $today = now()->startOfDay();
        $year = $filters['year'] ?? 'all';
        $term = $filters['term'] ?? 'all';

        $activeFederationCount = $this->activeRoleCount($tenantId, User::ROLE_SK_FED, $today, $year, $term);
        $activeOfficialCount = $this->activeRoleCount($tenantId, User::ROLE_SK_OFFICIAL, $today, $year, $term);
        $kabataanCount = $this->getKabataanCount($tenantId);

        return [
            'totalUsers' => $activeFederationCount + $activeOfficialCount + $kabataanCount,
            'federationAccounts' => $activeFederationCount,
            'officialAccounts' => $activeOfficialCount,
            'kabataanAccounts' => $kabataanCount,
            'deletedSkFederation' => User::onlyTrashed()
                ->where('tenant_id', $tenantId)
                ->where('role', User::ROLE_SK_FED)
                ->count(),
            'deletedSkOfficials' => User::onlyTrashed()
                ->where('tenant_id', $tenantId)
                ->where('role', User::ROLE_SK_OFFICIAL)
                ->count(),
            'skFederationRecords' => $this->archiveRecordCount(ArchivedSkFederationRecord::query(), $tenantId, $year, $term),
            'skOfficialsRecords' => $this->archiveRecordCount(ArchivedSkOfficialRecord::query(), $tenantId, $year, $term),
        ];
    }

    private function activeRoleCount(int $tenantId, string $role, $today, string $year = 'all', string $term = 'all'): int
    {
        return User::query()
            ->where('tenant_id', $tenantId)
            ->where('role', $role)
            ->whereHas('officialProfile.terms', function ($termQuery) use ($today, $year, $term) {
                $this->applyTermScope($termQuery, $year, $term, $today);
            })
            ->count();
    }

    private function applyTermScope($termQuery, string $year, string $term, $today): void
    {
        if ($term !== '' && $term !== 'all') {
            [$start, $end] = array_pad(explode('|', $term, 2), 2, null);
            if ($start && $end) {
                $termQuery->whereDate('term_start', $start)->whereDate('term_end', $end);

                return;
            }
        }

        if ($year !== '' && $year !== 'all') {
            $termQuery->where(function ($query) use ($year) {
                $query->whereYear('term_start', (int) $year)
                    ->orWhereYear('term_end', (int) $year);
            });

            return;
        }

        $termQuery
            ->where('status', OfficialTerm::STATUS_ACTIVE)
            ->whereDate('term_end', '>=', $today);
    }

    private function archiveRecordCount($query, int $tenantId, string $year, string $term): int
    {
        $query->where('tenant_id', $tenantId);

        if ($term !== '' && $term !== 'all') {
            [$start, $end] = array_pad(explode('|', $term, 2), 2, null);
            if ($start && $end) {
                return (int) $query
                    ->whereDate('term_start', $start)
                    ->whereDate('term_end', $end)
                    ->count();
            }
        }

        if ($year !== '' && $year !== 'all') {
            return (int) $query->where(function ($scoped) use ($year) {
                $scoped->whereYear('term_start', (int) $year)
                    ->orWhereYear('term_end', (int) $year);
            })->count();
        }

        return (int) $query->count();
    }

    private function getKabataanCount(int $tenantId): int
    {
        if (! Schema::hasTable('kabataan_registrations')) {
            return 0;
        }

        $barangayIds = Barangay::query()
            ->where('tenant_id', $tenantId)
            ->pluck('id');

        $query = DB::table('kabataan_registrations');

        if ($barangayIds->isNotEmpty()) {
            $query->whereIn('barangay_id', $barangayIds);
        }

        return (int) $query->count();
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

    /**
     * @return list<array<string, mixed>>
     */
    /**
     * @param  array{year?: string, term?: string}  $filters
     */
    public function getBarangayDistribution(User $admin, array $filters = []): array
    {
        $tenantId = $this->resolveTenantId($admin);
        $today = now()->startOfDay();
        $year = $filters['year'] ?? 'all';
        $term = $filters['term'] ?? 'all';
        $barangays = $this->getBarangays($admin);

        $federationCounts = User::query()
            ->selectRaw('barangay_id, COUNT(*) as total')
            ->where('tenant_id', $tenantId)
            ->where('role', User::ROLE_SK_FED)
            ->whereHas('officialProfile.terms', function ($termQuery) use ($today, $year, $term) {
                $this->applyTermScope($termQuery, $year, $term, $today);
            })
            ->groupBy('barangay_id')
            ->pluck('total', 'barangay_id');

        $officialCounts = User::query()
            ->selectRaw('barangay_id, COUNT(*) as total')
            ->where('tenant_id', $tenantId)
            ->where('role', User::ROLE_SK_OFFICIAL)
            ->whereHas('officialProfile.terms', function ($termQuery) use ($today, $year, $term) {
                $this->applyTermScope($termQuery, $year, $term, $today);
            })
            ->groupBy('barangay_id')
            ->pluck('total', 'barangay_id');

        $kabataanCounts = collect();
        if (Schema::hasTable('kabataan_registrations')) {
            $kabataanCounts = DB::table('kabataan_registrations')
                ->selectRaw('barangay_id, COUNT(*) as total')
                ->whereIn('barangay_id', $barangays->pluck('id'))
                ->groupBy('barangay_id')
                ->pluck('total', 'barangay_id');
        }

        return $barangays->map(function (Barangay $barangay) use ($federationCounts, $officialCounts, $kabataanCounts) {
            $federationCount = (int) ($federationCounts[$barangay->id] ?? 0);
            $officialCount = (int) ($officialCounts[$barangay->id] ?? 0);
            $kabataanCount = (int) ($kabataanCounts[$barangay->id] ?? 0);
            $totalUsers = $federationCount + $officialCount + $kabataanCount;

            return [
                'barangay' => $barangay->name,
                'barangayId' => $barangay->id,
                'skFederationAssigned' => $federationCount > 0,
                'federationCount' => $federationCount,
                'skOfficialsAssigned' => $officialCount,
                'kabataanCount' => $kabataanCount,
                'accountCount' => $totalUsers,
                'rolesSummary' => trim(implode(', ', array_filter([
                    $federationCount > 0 ? "{$federationCount} Federation" : null,
                    $officialCount > 0 ? "{$officialCount} Officials" : null,
                    $kabataanCount > 0 ? "{$kabataanCount} Kabataan" : null,
                ]))) ?: 'No active users',
            ];
        })->values()->all();
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

    /**
     * @return array{years: list<string>, terms: list<array{value: string, label: string}>}
     */
    public function getTermFilterOptions(User $admin): array
    {
        $tenantId = $this->resolveTenantId($admin);

        $terms = OfficialTerm::query()
            ->whereHas('officialProfile.user', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->orderByDesc('term_end')
            ->get(['term_start', 'term_end']);

        $termOptions = $terms
            ->map(fn (OfficialTerm $term) => [
                'value' => $term->term_start->format('Y-m-d').'|'.$term->term_end->format('Y-m-d'),
                'label' => $term->term_start->format('F j, Y').' to '.$term->term_end->format('F j, Y'),
            ])
            ->unique('value')
            ->values()
            ->all();

        $years = $terms
            ->flatMap(function (OfficialTerm $term) {
                $startYear = (int) $term->term_start->format('Y');
                $endYear = (int) $term->term_end->format('Y');

                return range($startYear, $endYear);
            })
            ->unique()
            ->sortDesc()
            ->values()
            ->map(fn (int $year) => (string) $year)
            ->all();

        return [
            'years' => $years,
            'terms' => $termOptions,
        ];
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
