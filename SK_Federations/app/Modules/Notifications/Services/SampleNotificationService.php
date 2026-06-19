<?php

namespace App\Modules\Notifications\Services;

class SampleNotificationService
{
    /**
     * Sample dropdown notifications for the header bell (demo data).
     *
     * @return list<array{id: int, icon: string, title: string, text: string, time: string, unread: bool}>
     */
    public function dropdownSamples(): array
    {
        return [
            [
                'id' => 1,
                'icon' => 'fa-users',
                'title' => 'New SK Official Account Request',
                'text' => 'A new SK Official account for Barangay San Roque is pending federation review.',
                'time' => '9:12 AM · Jun 19, 2026',
                'unread' => true,
            ],
            [
                'id' => 2,
                'icon' => 'fa-file-invoice-dollar',
                'title' => 'Barangay ABYIP Submission Reminder',
                'text' => 'Three barangays have not yet submitted their ABYIP for the current fiscal year.',
                'time' => '8:05 AM · Jun 19, 2026',
                'unread' => true,
            ],
            [
                'id' => 3,
                'icon' => 'fa-calendar-check',
                'title' => 'Federation General Assembly',
                'text' => 'SK Federation General Assembly is scheduled on July 5, 2026 at 9:00 AM at the municipal hall.',
                'time' => '6:30 AM · Jun 19, 2026',
                'unread' => true,
            ],
        ];
    }

    /**
     * @return list<array{id: int, icon: string, title: string, text: string, time: string, unread: bool}>
     */
    public function allSamples(): array
    {
        return array_merge($this->dropdownSamples(), [
            [
                'id' => 4,
                'icon' => 'fa-chart-bar',
                'title' => 'Monthly Reports Available',
                'text' => 'Barangay monitoring reports for May 2026 are now ready for review.',
                'time' => '3:45 PM · Jun 18, 2026',
                'unread' => false,
            ],
            [
                'id' => 5,
                'icon' => 'fa-id-badge',
                'title' => 'Federation Account Updated',
                'text' => 'SK Federation officer profile details were updated successfully.',
                'time' => '10:20 AM · Jun 17, 2026',
                'unread' => false,
            ],
        ]);
    }

    public function unreadCount(): int
    {
        return collect($this->dropdownSamples())
            ->filter(fn (array $item): bool => $item['unread'] ?? false)
            ->count();
    }
}
