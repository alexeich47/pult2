<?php

namespace Database\Seeders;

use App\Models\MvrEntry;
use Illuminate\Database\Seeder;

class MvrEntrySeeder extends Seeder
{
    public function run(): void
    {
        // 2026 monthly targets matching PlayDuck daily data
        // Feb: 28 days × $10K = $280K target, actual = sum of daily facts
        // Mar: 31 days × $10K = $310K target, actual = sum of daily facts
        // Apr: $300K target, actual not yet known
        $data = [
            ['year' => 2026, 'month' => 1, 'target' => 300000, 'actual' => 287000],
            ['year' => 2026, 'month' => 2, 'target' => 280000, 'actual' => 46219],
            ['year' => 2026, 'month' => 3, 'target' => 310000, 'actual' => 316305],
            ['year' => 2026, 'month' => 4, 'target' => 300000, 'actual' => 0],
            ['year' => 2026, 'month' => 5, 'target' => 320000, 'actual' => 0],
            ['year' => 2026, 'month' => 6, 'target' => 330000, 'actual' => 0],
        ];

        foreach ($data as $row) {
            MvrEntry::create([
                'unit_id' => null,
                'year' => $row['year'],
                'month' => $row['month'],
                'target' => $row['target'],
                'actual' => $row['actual'],
                'currency' => 'USD',
            ]);
        }
    }
}
