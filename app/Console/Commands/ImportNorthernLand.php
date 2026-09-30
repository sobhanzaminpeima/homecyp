<?php

namespace App\Console\Commands;

use App\Services\PropertyImportService;
use Illuminate\Console\Command;

class ImportNorthernLand extends Command
{
    protected $signature = 'import:northernland {--url= : Import a single URL instead of the default set}';
    protected $description = 'Import projects from Northernland into the HomeCyp database';

    protected array $urls = [
        'https://northernland.com/en/grand-sapphire-resort-and-residences',
        'https://northernland.com/en/grand-sapphire-phase-4',
        'https://northernland.com/en/northernland-villas-catalkoy',
        'https://northernland.com/en/casa-del-mare',
        'https://northernland.com/en/emerald-villas-tuzla',
        'https://northernland.com/en/emerald-villas-yeni-bogazici',
        'https://northernland.com/en/kentplus',
    ];

    public function handle(PropertyImportService $service): int
    {
        $urls = $this->option('url') ? [$this->option('url')] : $this->urls;

        foreach ($urls as $url) {
            $this->info("Importing: {$url}");
            try {
                $project = $service->importProject($url, 'project');
                $this->line("  ✓ Created project #{$project->id}: {$project->translation('en')?->title}");
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('Import complete.');
        return self::SUCCESS;
    }
}
