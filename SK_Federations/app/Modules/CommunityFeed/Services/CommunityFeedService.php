<?php

namespace App\Modules\CommunityFeed\Services;

use App\Modules\BarangayMonitoring\Services\BarangayMonitoringService;
use App\Modules\Profile\Models\Barangay;
use App\Modules\Shared\Models\Announcement;
use App\Modules\Shared\Models\User;
use App\Services\BarangayLogoUrlService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CommunityFeedService
{
    private const DEFAULT_PROFILE_COLOR = '#213F99';

    public function __construct(
        private readonly BarangayMonitoringService $monitoringService,
        private readonly BarangayLogoUrlService $logoUrls,
    ) {}

    /**
     * @return list<array{slug: string, name: string, id: int|null, logo_url: string|null, initials: string, color: string}>
     */
    public function listBarangayProfiles(?int $tenantId = null): array
    {
        $bySlug = [];
        foreach ($this->monitoringService->slugToNameMap() as $slug => $name) {
            $bySlug[$slug] = [
                'slug' => $slug,
                'name' => $name,
                'id' => null,
                'logo_url' => null,
                'initials' => $this->initials($name),
                'color' => self::DEFAULT_PROFILE_COLOR,
            ];
        }

        if (Schema::hasTable('barangays')) {
            $query = Barangay::query()->orderBy('name');
            if ($tenantId !== null) {
                $query->where('tenant_id', $tenantId);
            }

            foreach ($query->get() as $barangay) {
                $slug = Str::slug($barangay->name);
                $bySlug[$slug] = [
                    'slug' => $slug,
                    'name' => $barangay->name,
                    'id' => $barangay->id,
                    'logo_url' => $this->logoUrls->resolve($barangay->id),
                    'initials' => $this->initials($barangay->name),
                    'color' => self::DEFAULT_PROFILE_COLOR,
                ];

                foreach ($this->monitoringService->slugToNameMap() as $mappedSlug => $mappedName) {
                    if ($mappedSlug !== $slug && strcasecmp($mappedName, $barangay->name) === 0) {
                        unset($bySlug[$mappedSlug]);
                    }
                }
            }
        }

        return collect($bySlug)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    public function resolveBarangayProfile(string $slug, ?int $tenantId = null): ?array
    {
        $profiles = $this->listBarangayProfiles($tenantId);

        foreach ($profiles as $profile) {
            if ($profile['slug'] === $slug) {
                return $profile;
            }
        }

        $mappedName = $this->monitoringService->nameFromSlug($slug);
        if ($mappedName === null) {
            return null;
        }

        foreach ($profiles as $profile) {
            if (strcasecmp((string) $profile['name'], $mappedName) === 0) {
                return $profile;
            }
        }

        return [
            'slug' => $slug,
            'name' => $mappedName,
            'id' => null,
            'logo_url' => null,
            'initials' => $this->initials($mappedName),
            'color' => self::DEFAULT_PROFILE_COLOR,
        ];
    }

    /**
     * @return list<array{name: string, role: string, initials: string}>
     */
    public function listOfficialsForBarangay(?int $barangayId): array
    {
        if ($barangayId === null || ! Schema::hasTable('users')) {
            return [];
        }

        return User::query()
            ->with('officialProfile')
            ->where('barangay_id', $barangayId)
            ->where('role', User::ROLE_SK_OFFICIAL)
            ->where('status', User::STATUS_ACTIVE)
            ->get()
            ->map(function (User $official) {
                $name = trim((string) $official->name);
                $role = trim((string) ($official->officialProfile?->position ?? 'SK Official'));

                return [
                    'name' => $name !== '' ? $name : 'SK Official',
                    'role' => $role !== '' ? $role : 'SK Official',
                    'initials' => $this->initials($name !== '' ? $name : 'SK'),
                    'sort_key' => $this->positionSortKey($role),
                ];
            })
            ->sortBy([
                ['sort_key', 'asc'],
                ['name', 'asc'],
            ])
            ->map(fn (array $official) => [
                'name' => $official['name'],
                'role' => $official['role'],
                'initials' => $official['initials'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listPostsForBarangay(?int $barangayId): array
    {
        if ($barangayId === null) {
            return [];
        }

        return Announcement::query()
            ->with(['user', 'barangay', 'images'])
            ->withCount(['reactions', 'comments'])
            ->active()
            ->where('barangay_id', $barangayId)
            ->whereRaw('"is_federation_wide" = false')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Announcement $post) {
                $type = strtolower((string) $post->type);

                return [
                    'id' => $post->id,
                    'type' => ucfirst($type),
                    'type_class' => in_array($type, ['event', 'announcement', 'activity', 'program', 'update'], true)
                        ? $type
                        : 'update',
                    'author' => $post->user?->name ?: ('SK Brgy. '.($post->barangay?->name ?? '')),
                    'posted_at' => $post->created_at?->diffForHumans() ?? '',
                    'title' => $post->title ?: 'Barangay Update',
                    'text' => $post->body,
                    'likes' => (int) ($post->reactions_count ?? 0),
                    'comments' => (int) ($post->comments_count ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1).substr($parts[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    private function positionSortKey(string $role): int
    {
        $normalized = strtolower($role);

        return match (true) {
            str_contains($normalized, 'vice') => 1,
            str_contains($normalized, 'chair') => 0,
            str_contains($normalized, 'secretary') => 2,
            str_contains($normalized, 'treasurer') => 3,
            str_contains($normalized, 'auditor') => 4,
            str_contains($normalized, 'pro') || str_contains($normalized, 'public relations') => 5,
            str_contains($normalized, 'councilor') || str_contains($normalized, 'kagawad') => 6,
            default => 7,
        };
    }
}
