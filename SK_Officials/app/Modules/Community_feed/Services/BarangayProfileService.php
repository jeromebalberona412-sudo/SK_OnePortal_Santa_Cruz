<?php

namespace App\Modules\Community_feed\Services;

use App\Models\CommunityFeed;
use App\Models\Barangay;
use App\Models\OfficialTerm;
use App\Models\User;
use App\Modules\Committees\Services\CommitteeService;
use App\Services\BarangayLogoUrlService;
use App\Services\CloudinaryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BarangayProfileService
{
    /** @var list<string> */
    private const POSITION_ORDER = [
        'sk chair',
        'chairperson',
        'chairman',
        'vice chair',
        'secretary',
        'treasurer',
        'auditor',
        'public relations',
        'pro',
        'councilor',
    ];

    public function __construct(
        private readonly CommitteeService $committeeService,
        private readonly BarangayLogoUrlService $logoUrlService,
        private readonly CloudinaryService $cloudinary,
        private readonly CommunityFeedPresenter $communityFeedPresenter,
    ) {
    }

    public function findBySlug(string $slug, ?int $tenantId = null): ?Barangay
    {
        $query = Barangay::query();

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->get()->first(function (Barangay $barangay) use ($slug) {
            return Str::slug($barangay->name) === $slug;
        });
    }

    /**
     * @return list<array{id: int, name: string, slug: string, logo_url: ?string, initials: string}>
     */
    public function listForTenant(int $tenantId): array
    {
        return Barangay::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(function (Barangay $barangay) {
                return [
                    'id' => $barangay->id,
                    'name' => $barangay->name,
                    'slug' => Str::slug($barangay->name),
                    'logo_url' => $this->logoUrlService->resolve($barangay->id),
                    'initials' => $this->buildInitials($barangay->name),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function buildProfile(Barangay $barangay): array
    {
        $officials = $this->listOfficials($barangay->id);
        $posts     = $this->listPosts($barangay->id);
        $termLabel = $this->resolveTermLabel($barangay->id);
        $logoUrl   = $this->logoUrlService->resolve($barangay->id);

        return [
            'barangay' => $barangay,
            'slug' => Str::slug($barangay->name),
            'name' => $barangay->name,
            'logo_url' => $logoUrl,
            'initials' => $this->buildInitials($barangay->name),
            'location' => trim(implode(', ', array_filter([
                'Barangay '.$barangay->name,
                $barangay->municipality,
                $barangay->province,
            ]))),
            'post_count' => $posts->count(),
            'officer_count' => $officials->count(),
            'term_label' => $termLabel,
            'officials' => $officials->values()->all(),
            'posts' => $posts->values()->all(),
        ];
    }

    /**
     * @return Collection<int, array{name: string, role: string, initials: string}>
     */
    private function listOfficials(int $barangayId): Collection
    {
        $logoUrl = $this->logoUrlService->resolve($barangayId);

        return User::query()
            ->with('officialProfile')
            ->where('barangay_id', $barangayId)
            ->where('role', User::ROLE_SK_OFFICIAL)
            ->where('status', User::STATUS_ACTIVE)
            ->get()
            ->map(function (User $official) use ($logoUrl) {
                $fullName = $this->committeeService->buildOfficialFullName($official);
                $role     = trim((string) ($official->officialProfile?->position ?? 'SK Official'));

                return [
                    'name' => $fullName !== '' ? $fullName : trim((string) $official->name),
                    'role' => $role !== '' ? $role : 'SK Official',
                    'initials' => $this->buildInitials($fullName !== '' ? $fullName : (string) $official->name),
                    'logo_url' => $logoUrl,
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
                'logo_url' => $official['logo_url'],
            ])
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function listPosts(int $barangayId): Collection
    {
        // Provide full `post.comments` tree so the existing comment-preview JS can render comments
        // (it does not fetch comments on its own).
        return CommunityFeed::query()
            ->with([
                'user',
                'barangay',
                'images',
                'reactions',
                'comments.reactions',
            ])
            ->active()
            ->withCount(['reactions', 'comments'])
            ->where('barangay_id', $barangayId)
            ->whereRaw('"is_federation_wide" = false')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (CommunityFeed $post) {
                // userId=0 so `owned/liked` are effectively disabled for non-owner viewing.
                $data = $this->communityFeedPresenter->formatPost($post, 0, 'sk_official');

                $type = strtolower((string) ($data['type'] ?? 'update'));
                $data['type_class'] = $type;
                $data['type_label'] = ucfirst($type);
                $data['posted_time'] = (string) ($data['time'] ?? '');

                return $data;
            });
    }

    private function resolveTermLabel(int $barangayId): string
    {
        $terms = OfficialTerm::query()
            ->where('status', OfficialTerm::STATUS_ACTIVE)
            ->whereHas('officialProfile.user', function ($query) use ($barangayId) {
                $query->where('barangay_id', $barangayId)
                    ->where('role', User::ROLE_SK_OFFICIAL);
            })
            ->get(['term_start', 'term_end']);

        if ($terms->isEmpty()) {
            return '—';
        }

        $start = $terms->min('term_start');
        $end   = $terms->max('term_end');

        if ($start === null || $end === null) {
            return '—';
        }

        return $start->format('Y').'–'.$end->format('Y');
    }

    private function buildInitials(string $value): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        if ($clean === '') {
            return 'SK';
        }

        $parts = explode(' ', $clean);

        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1).substr($parts[count($parts) - 1], 0, 1));
        }

        return strtoupper(substr($clean, 0, 2));
    }

    private function positionSortKey(string $position): int
    {
        $normalized = mb_strtolower(trim($position), 'UTF-8');

        foreach (self::POSITION_ORDER as $index => $needle) {
            if (str_contains($normalized, $needle)) {
                return $index;
            }
        }

        return count(self::POSITION_ORDER) + 1;
    }
}
