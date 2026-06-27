<?php

namespace App\Services;

use App\Models\SkOfficialsNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SkOfficialsNotificationService
{
    public const CATEGORY_KK_PROFILING = 'kk_profiling';

    public const CATEGORY_SURVEY = 'survey';

    public const CATEGORY_PROGRAM = 'program';

    public const CATEGORY_REMINDER = 'reminder';

    public const CATEGORY_ANNOUNCEMENT = 'announcement';

    /**
     * @return list<array{id: int, category: string, category_label: string, title: string, text: string, time: string, unread: bool, action_url: string|null}>
     */
    public function recentForUser(?User $user, int $limit = 5): array
    {
        if (! $user) {
            return [];
        }

        return $this->forUserQuery($user)
            ->limit($limit)
            ->get()
            ->map(fn (SkOfficialsNotification $notification) => $this->formatNotification($notification))
            ->all();
    }

    /**
     * @return list<array{id: int, category: string, category_label: string, title: string, text: string, time: string, unread: bool, action_url: string|null}>
     */
    public function allForUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return $this->forUserQuery($user)
            ->get()
            ->map(fn (SkOfficialsNotification $notification) => $this->formatNotification($notification))
            ->all();
    }

    public function unreadCountForUser(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        return $this->forUserQuery($user)
            ->whereNull('read_at')
            ->count();
    }

    public function markRead(User $user, int $notificationId): bool
    {
        $notification = SkOfficialsNotification::query()
            ->where('user_id', $user->id)
            ->whereKey($notificationId)
            ->first();

        if (! $notification || $notification->read_at !== null) {
            return false;
        }

        $notification->update(['read_at' => now()]);

        return true;
    }

    public function markAllRead(User $user): int
    {
        return SkOfficialsNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function notifyUser(
        User $user,
        string $category,
        string $title,
        string $body,
        ?string $actionUrl = null,
    ): SkOfficialsNotification {
        return SkOfficialsNotification::create([
            'user_id' => $user->id,
            'category' => $category,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
        ]);
    }

    public function notifyBarangayOfficials(
        int $barangayId,
        string $category,
        string $title,
        string $body,
        ?string $actionUrl = null,
    ): void {
        $officials = User::query()
            ->where('barangay_id', $barangayId)
            ->where('role', User::ROLE_SK_OFFICIAL)
            ->where('status', User::STATUS_ACTIVE)
            ->get();

        foreach ($officials as $official) {
            $this->notifyUser($official, $category, $title, $body, $actionUrl);
        }
    }

    public function notifyKkProfilingSubmission(int $barangayId, string $fullName): void
    {
        $this->notifyBarangayOfficials(
            $barangayId,
            self::CATEGORY_KK_PROFILING,
            'New KK Profiling Request',
            "{$fullName} submitted a KK Profiling registration that needs your review.",
            url('/kk-profiling-requests'),
        );
    }

    public function notifySurveyResponse(int $barangayId, string $respondentName, string $programName, string $resultsUrl): void
    {
        $this->notifyBarangayOfficials(
            $barangayId,
            self::CATEGORY_SURVEY,
            'New Survey Response',
            "{$respondentName} submitted a response for {$programName}.",
            $resultsUrl,
        );
    }

    public function notifyProgramApplication(
        int $barangayId,
        string $applicantName,
        string $programName,
        string $actionUrl = '/scholarship-applications',
        ?string $programLetter = null,
    ): void {
        $letter = strtoupper(trim((string) $programLetter));
        $title = $letter === 'I' ? 'New Sports Application' : 'New Program Application';
        $body = $letter === 'I'
            ? "{$applicantName} submitted a sports program application for {$programName}."
            : "{$applicantName} submitted an application for {$programName}.";

        $this->notifyBarangayOfficials(
            $barangayId,
            self::CATEGORY_PROGRAM,
            $title,
            $body,
            url($actionUrl),
        );
    }

    /**
     * @return Builder<SkOfficialsNotification>
     */
    private function forUserQuery(User $user)
    {
        return SkOfficialsNotification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at');
    }

    /**
     * @return array{id: int, category: string, category_label: string, title: string, text: string, time: string, unread: bool, action_url: string|null}
     */
    private function formatNotification(SkOfficialsNotification $notification): array
    {
        return [
            'id' => (int) $notification->id,
            'category' => $notification->category,
            'category_label' => $this->categoryLabel($notification->category),
            'title' => $notification->title,
            'text' => $notification->body,
            'time' => $this->formatTime($notification->created_at),
            'unread' => $notification->isUnread(),
            'action_url' => $notification->action_url,
        ];
    }

    private function categoryLabel(string $category): string
    {
        return match ($category) {
            self::CATEGORY_KK_PROFILING => 'KK Profiling',
            self::CATEGORY_SURVEY => 'Survey',
            self::CATEGORY_PROGRAM => 'Program',
            self::CATEGORY_REMINDER => 'Reminder',
            self::CATEGORY_ANNOUNCEMENT => 'Announcement',
            default => 'General',
        };
    }

    private function formatTime(?Carbon $timestamp): string
    {
        if (! $timestamp) {
            return '—';
        }

        return $timestamp->format('g:i A').' · '.$timestamp->format('M j, Y');
    }
}
