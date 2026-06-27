<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SkOfficialsNotificationDispatcher
{
    /**
     * @var array<string, string>
     */
    private const LETTER_COMMITTEE = [
        'B' => 'environmental',
        'C' => 'disaster',
        'D' => 'livelihood',
        'E' => 'medicines',
        'F' => 'antidrug',
        'G' => 'gender',
        'H' => 'feeding',
        'J' => 'others',
    ];

    public function notifyKkProfilingSubmission(int $barangayId, string $fullName): void
    {
        if (! Schema::hasTable('sk_officials_notifications')) {
            return;
        }

        $this->insertForBarangayOfficials(
            $barangayId,
            'kk_profiling',
            'New KK Profiling Request',
            "{$fullName} submitted a KK Profiling registration that needs your review.",
            '/kk-profiling-requests',
        );
    }

    public function notifyKkProfilingAutoApproved(int $barangayId, string $fullName): void
    {
        if (! Schema::hasTable('sk_officials_notifications')) {
            return;
        }

        $this->insertForBarangayOfficials(
            $barangayId,
            'kk_profiling',
            'KK Profiling Auto-Approved',
            "✅ {$fullName} has been automatically approved because all identity verification checks passed.",
            '/kabataan',
        );
    }

    public function notifySurveyResponse(
        int $barangayId,
        string $respondentName,
        string $programName,
        ?string $programLetter,
    ): void {
        if (! Schema::hasTable('sk_officials_notifications')) {
            return;
        }

        $letter = strtoupper(trim((string) $programLetter));
        $committee = self::LETTER_COMMITTEE[$letter] ?? null;
        $actionUrl = $committee ? "/{$committee}-survey-results" : '/schedule-programs';

        $this->insertForBarangayOfficials(
            $barangayId,
            'survey',
            'New Survey Response',
            "{$respondentName} submitted a response for {$programName}.",
            $actionUrl,
        );
    }

    public function notifyProgramApplication(
        int $barangayId,
        string $applicantName,
        string $programName,
        ?string $programLetter,
    ): void {
        if (! Schema::hasTable('sk_officials_notifications')) {
            return;
        }

        $letter = strtoupper(trim((string) $programLetter));
        $actionUrl = $letter === 'I' ? '/sports-requests' : '/scholarship-applications';
        $title = $letter === 'I' ? 'New Sports Application' : 'New Program Application';
        $body = $letter === 'I'
            ? "{$applicantName} submitted a sports program application for {$programName}."
            : "{$applicantName} submitted an application for {$programName}.";

        $this->insertForBarangayOfficials(
            $barangayId,
            'program',
            $title,
            $body,
            $actionUrl,
        );
    }

    private function insertForBarangayOfficials(
        int $barangayId,
        string $category,
        string $title,
        string $body,
        string $actionUrl,
    ): void {
        if ($barangayId <= 0) {
            return;
        }

        $officialIds = DB::table('users')
            ->where('barangay_id', $barangayId)
            ->where('role', 'sk_official')
            ->where('status', 'ACTIVE')
            ->pluck('id');

        if ($officialIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $officialIds->map(fn ($userId) => [
            'user_id' => $userId,
            'category' => $category,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::table('sk_officials_notifications')->insert($rows);
    }
}
