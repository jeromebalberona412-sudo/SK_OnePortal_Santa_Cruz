<?php

namespace App\Console\Commands;

use App\Modules\Community_feed\Services\CommunityFeedArchiveService;
use Illuminate\Console\Command;

class PurgeArchivedCommunityFeeds extends Command
{
    protected $signature = 'community-feed:purge-archived';

    protected $description = 'Permanently delete archived community feed posts older than 30 days';

    public function handle(CommunityFeedArchiveService $archiveService): int
    {
        $count = $archiveService->purgeExpired();

        $this->info("Purged {$count} archived community feed post(s).");

        return self::SUCCESS;
    }
}
