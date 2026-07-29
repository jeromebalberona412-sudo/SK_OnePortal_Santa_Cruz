<?php

namespace App\Console\Commands;

use App\Models\Abyip;
use App\Modules\ABYIP\Services\AbyipService;
use Illuminate\Console\Command;

class ReparseAbyipDocument extends Command
{
    protected $signature = 'abyip:reparse {document_id? : The ABYIP document row id} {--all : Reparse every stored PDF document}';

    protected $description = 'Re-extract ABYIP data from stored PDFs and refresh database rows';

    public function handle(AbyipService $abyipService): int
    {
        $query = Abyip::query()
            ->documents()
            ->whereNotNull('pdf_data')
            ->orderBy('id');

        if ($this->option('all')) {
            $documents = $query->get();
        } else {
            $documentId = $this->argument('document_id');

            if ($documentId === null) {
                $this->error('Provide a document_id or use --all.');

                return self::FAILURE;
            }

            $documents = $query->where('id', $documentId)->get();

            if ($documents->isEmpty()) {
                $this->error('ABYIP document not found or has no stored PDF.');

                return self::FAILURE;
            }
        }

        foreach ($documents as $document) {
            try {
                $abyipService->reparseDocument($document);
                $document->refresh();
                $this->info("Reparsed document #{$document->id} ({$document->barangay_name}, CY {$document->fiscal_year}).");
                $this->line("  Barangay budget: {$document->barangay_estimated_budget}");
                $this->line("  SK fund: {$document->sk_fund_amount}");
                $this->line("  Total budget: {$document->total_budget}");
            } catch (\Throwable $exception) {
                $this->error("Failed document #{$document->id}: {$exception->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
