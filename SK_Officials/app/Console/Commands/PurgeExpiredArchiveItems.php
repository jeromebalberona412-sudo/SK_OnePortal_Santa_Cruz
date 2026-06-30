<?php

namespace App\Console\Commands;

use App\Models\Abyip;
use App\Models\RejectedKkProfiling;
use App\Models\RejectedScholarship;
use App\Models\RejectedSports;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class PurgeExpiredArchiveItems extends Command
{
    protected $signature = 'archive:purge-expired';

    protected $description = 'Permanently delete archived/rejected items older than 30 days (except Archived Youth Records)';

    private const RETENTION_DAYS = 30;

    public function handle(): int
    {
        $cutoff = now()->subDays(self::RETENTION_DAYS);
        $total = 0;

        if (Schema::hasTable('rejected_kk_profiling')) {
            $total += RejectedKkProfiling::query()
                ->whereNull('restored_at')
                ->where('rejected_at', '<=', $cutoff)
                ->delete();
        }

        if (Schema::hasTable('rejected_scholarships')) {
            $total += RejectedScholarship::query()
                ->whereNull('restored_at')
                ->where('rejected_at', '<=', $cutoff)
                ->delete();
        }

        if (Schema::hasTable('rejected_sports')) {
            $total += RejectedSports::query()
                ->whereNull('restored_at')
                ->where('rejected_at', '<=', $cutoff)
                ->delete();
        }

        if (Schema::hasTable('abyips') && Schema::hasColumn('abyips', 'deleted_at')) {
            $total += Abyip::onlyTrashed()
                ->where('deleted_at', '<=', $cutoff)
                ->forceDelete();
        }

        $this->info("Purged {$total} expired archive item(s).");

        return self::SUCCESS;
    }
}
