<?php

namespace App\Modules\CommunityFeed\Services;

use App\Modules\BarangayMonitoring\Services\BarangayMonitoringService;
use App\Modules\Profile\Models\Barangay;
use App\Services\BarangayLogoUrlService;
use Illuminate\Support\Facades\Schema;

class CommunityFeedService
{
    private const DEFAULT_PROFILE_COLOR = '#213F99';

    public function __construct(
        private readonly BarangayMonitoringService $monitoringService,
        private readonly BarangayLogoUrlService $logoUrls,
    ) {
    }

    /**
     * @return list<array{slug: string, name: string, id: int|null, logo_url: string|null, initials: string, color: string}>
     */
    public function listBarangayProfiles(?int $tenantId = null): array
    {
        $profiles = [];

        foreach ($this->monitoringService->slugToNameMap() as $slug => $name) {
            $barangay = null;
            if (Schema::hasTable('barangays')) {
                $query = Barangay::query()->where('name', $name);
                if ($tenantId !== null) {
                    $query->where('tenant_id', $tenantId);
                }
                $barangay = $query->first();
            }

            $profiles[] = [
                'slug' => $slug,
                'name' => $name,
                'id' => $barangay?->id,
                'logo_url' => $barangay ? $this->logoUrls->resolve($barangay->id) : null,
                'initials' => $this->initials($name),
                'color' => self::DEFAULT_PROFILE_COLOR,
            ];
        }

        return $profiles;
    }

    public function resolveBarangayProfile(string $slug, ?int $tenantId = null): ?array
    {
        $name = $this->monitoringService->nameFromSlug($slug);
        if ($name === null) {
            return null;
        }

        foreach ($this->listBarangayProfiles($tenantId) as $profile) {
            if ($profile['slug'] === $slug) {
                return $profile;
            }
        }

        return null;
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1).substr($parts[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }
}
