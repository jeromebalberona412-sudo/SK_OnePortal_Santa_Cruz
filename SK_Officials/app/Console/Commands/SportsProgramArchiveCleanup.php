<?php

namespace App\Console\Commands;

use App\Modules\Sports_Programs\Services\SportsProgramArchiveService;
use Illuminate\Console\Command;

class SportsProgramArchiveCleanup extends Command
{
    protected $signature = 'sports-programs:purge-archived';

    protected $description = 'Permanently delete archived sports programs older than 30 days';

    public function handle(SportsProgramArchiveService $archiveService): int
    {
        $count = $archiveService->purgeExpired();

        $this->info("Purged {$count} archived sports program(s).");

        return self::SUCCESS;
    }
}
