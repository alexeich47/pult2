<?php

namespace Database\Seeders;

use App\Models\MvrEntry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MvrEntrySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $data = [
            ['month' => 1, 'target' => 50000, 'actual' => 42000],
            ['month' => 2, 'target' => 55000, 'actual' => 51000],
            ['month' => 3, 'target' => 60000, 'actual' => 58000],
            ['month' => 4, 'target' => 65000, 'actual' => 63000],
            ['month' => 5, 'target' => 70000, 'actual' => 72000],
            ['month' => 6, 'target' => 75000, 'actual' => 71000],
            ['month' => 7, 'target' => 80000, 'actual' => 78000],
            ['month' => 8, 'target' => 85000, 'actual' => 89000],
            ['month' => 9, 'target' => 90000, 'actual' => 87000],
            ['month' => 10, 'target' => 95000, 'actual' => 0],
            ['month' => 11, 'target' => 100000, 'actual' => 0],
            ['month' => 12, 'target' => 105000, 'actual' => 0],
        ];

        foreach ($data as $row) {
            MvrEntry::create([
                'unit_id' => null,
                'year' => 2025,
                'month' => $row['month'],
                'target' => $row['target'],
                'actual' => $row['actual'],
                'currency' => 'USD',
            ]);
        }
    }
}
