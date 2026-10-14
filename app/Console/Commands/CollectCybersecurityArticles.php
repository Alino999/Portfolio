<?php

namespace App\Console\Commands;

use App\Services\CybersecurityFeedImportService;
use Illuminate\Console\Command;

class CollectCybersecurityArticles extends Command
{
    protected $signature = 'articles:collect-cybersecurity {--days= : Number of recent days to collect}';

    protected $description = 'Collect recent cybersecurity items from trusted official RSS feeds as drafts';

    public function handle(CybersecurityFeedImportService $importer): int
    {
        $days = $this->option('days') !== null ? (int) $this->option('days') : null;
        $result = $importer->collect($days);

        $this->info("{$result['imported']} brouillon(s) ajouté(s), {$result['duplicates']} doublon(s) ignoré(s), {$result['ignored']} élément(s) hors période ou non valide(s).");

        foreach ($result['errors'] as $error) {
            $this->error($error);
        }

        return $result['errors'] === [] ? self::SUCCESS : self::FAILURE;
    }
}