<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Idea;
use App\Models\RiskEntry;
use App\Models\Service;
use Illuminate\Console\Command;

class GenerateDemoData extends Command
{
    protected $signature = 'pult:demo {--count=50 : Number of records per entity}';

    protected $description = 'Generate demo data for all Pult entities (on top of seeders)';

    public function handle(): int
    {
        $count = (int) $this->option('count');

        $this->info("Generating {$count} records per entity...");

        Employee::factory()->count($count)->create();
        $this->info("  ✓ {$count} employees");

        Idea::factory()->count($count)->create();
        $this->info("  ✓ {$count} ideas");

        RiskEntry::factory()->count($count)->create();
        $this->info("  ✓ {$count} risk entries");

        Service::factory()->count($count)->create();
        $this->info("  ✓ {$count} services");

        $total = $count * 4;
        $this->newLine();
        $this->info("Done — {$total} records generated. Pagination will now be visible on all tables.");

        return self::SUCCESS;
    }
}
