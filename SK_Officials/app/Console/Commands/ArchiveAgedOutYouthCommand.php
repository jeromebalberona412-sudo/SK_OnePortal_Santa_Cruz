<?php

namespace App\Console\Commands;

use App\Services\ArchiveAgedOutYouthService;
use Illuminate\Console\Command;

class ArchiveAgedOutYouthCommand extends Command
{
    protected $signature = 'kabataan:archive-aged-out {--barangay= : Limit archiving to a single barangay ID}';

    protected $description = 'Archive Kabataan records whose age exceeds the youth eligibility limit (30).';

    public function handle(ArchiveAgedOutYouthService $service): int
    {
        $barangayId = $this->option('barangay');
        $barangayId = is_numeric($barangayId) ? (int) $barangayId : null;

        $count = $service->archiveEligibleRegistrations($barangayId);

        $this->info("Archived {$count} aged-out Kabataan record(s).");

        return self::SUCCESS;
    }
}
