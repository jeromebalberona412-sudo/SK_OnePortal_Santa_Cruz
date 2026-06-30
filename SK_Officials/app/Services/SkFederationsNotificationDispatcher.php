<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SkFederationsNotificationDispatcher
{
    public function notifyAbyipSubmission(string $barangayName, int $fiscalYear): void
    {
        if (! Schema::hasTable('sk_federations_notifications')) {
            return;
        }

        $slug = $this->resolveBarangaySlug($barangayName);
        $actionUrl = $slug ? "/barangay-monitoring/{$slug}" : '/barangay-monitoring';

        $this->insertForAllFedUsers(
            'abyip',
            'New ABYIP Submission',
            "Barangay {$barangayName} submitted their ABYIP for CY {$fiscalYear}.",
            $actionUrl,
        );
    }

    public function notifyCommunityFeedLike(int $ownerUserId, string $reactorName, string $postLabel): void
    {
        if (! Schema::hasTable('sk_federations_notifications') || $ownerUserId <= 0) {
            return;
        }

        $this->insertForUser(
            $ownerUserId,
            'community_feed',
            "{$reactorName} liked your post",
            $postLabel,
            '/community-feed',
        );
    }

    public function notifyCommunityFeedComment(
        int $ownerUserId,
        string $commenterName,
        string $postLabel,
        ?string $commentBody = null,
    ): void {
        if (! Schema::hasTable('sk_federations_notifications') || $ownerUserId <= 0) {
            return;
        }

        $preview = trim((string) $commentBody) !== ''
            ? Str::limit(trim((string) $commentBody), 160)
            : $postLabel;

        $this->insertForUser(
            $ownerUserId,
            'community_feed',
            "{$commenterName} commented on your post",
            $preview,
            '/community-feed',
        );
    }

    public function postLabel(?string $title, ?string $body): string
    {
        $title = trim((string) $title);
        if ($title !== '') {
            return Str::limit($title, 80);
        }

        return Str::limit(trim(strip_tags((string) $body)), 80, '…') ?: 'your post';
    }

    private function insertForUser(
        int $userId,
        string $category,
        string $title,
        string $body,
        string $actionUrl,
    ): void {
        $now = now();

        DB::table('sk_federations_notifications')->insert([
            'user_id' => $userId,
            'category' => $category,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function insertForAllFedUsers(
        string $category,
        string $title,
        string $body,
        string $actionUrl,
    ): void {
        $fedUserIds = DB::table('users')
            ->where('role', 'sk_fed')
            ->where('status', 'ACTIVE')
            ->pluck('id');

        if ($fedUserIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $fedUserIds->map(fn ($userId) => [
            'user_id' => $userId,
            'category' => $category,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::table('sk_federations_notifications')->insert($rows);
    }

    private function resolveBarangaySlug(string $barangayName): ?string
    {
        $map = [
            'alipit' => 'Alipit',
            'bagumbayan' => 'Bagumbayan',
            'calios' => 'Calios',
            'duhat' => 'Duhat',
            'gatid' => 'Gatid',
            'jasaan' => 'Jasaan',
            'labuin' => 'Labuin',
            'malinao' => 'Malinao',
            'oogong' => 'Oogong',
            'pagsawitan' => 'Pagsawitan',
            'palasan' => 'Palasan',
            'patimbao' => 'Patimbao',
            'brgy-1-poblacion' => 'Poblacion I',
            'brgy-2-poblacion' => 'Poblacion II',
            'brgy-3-poblacion' => 'Poblacion III',
            'brgy-4-poblacion' => 'Poblacion IV',
            'brgy-5-poblacion' => 'Poblacion V',
            'san-jose' => 'San Jose',
            'san-juan' => 'San Juan',
            'san-pablo-norte' => 'San Pablo Norte',
            'san-pablo-sur' => 'San Pablo Sur',
            'santisima-cruz' => 'Santisima Cruz',
            'santo-angel-central' => 'Santo Angel Central',
            'santo-angel-norte' => 'Santo Angel Norte',
            'santo-angel-sur' => 'Santo Angel Sur',
        ];

        foreach ($map as $slug => $name) {
            if (strcasecmp($name, $barangayName) === 0) {
                return $slug;
            }
        }

        return null;
    }
}
