<?php

namespace App\Console\Commands;

use App\Modules\Announcement\Services\AnnouncementArchiveService;
use Illuminate\Console\Command;

class PurgeArchivedAnnouncements extends Command
{
    protected $signature = 'announcements:purge-archived';

    protected $description = 'Permanently delete archived announcements older than 30 days';

    public function handle(AnnouncementArchiveService $archiveService): int
    {
        $count = $archiveService->purgeExpired();

        $this->info("Purged {$count} archived announcement(s).");

        return self::SUCCESS;
    }
}
