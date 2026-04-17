<?php

namespace Database\Seeders;

use App\Models\MvrDailyEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MvrDailySeeder extends Seeder
{
    public function run(): void
    {
        // Seed daily data for current month + last 2 months
        $start = Carbon::now()->subMonths(2)->startOfMonth();
        $end = Carbon::now();

        $dailyTarget = 2500; // base daily plan

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $isWeekend = $date->isWeekend();
            $plan = $isWeekend ? 0 : $dailyTarget + rand(-200, 200);
            $fact = $isWeekend ? rand(0, 300) : $plan + rand(-800, 1200);
            if ($fact < 0) {
                $fact = 0;
            }

            MvrDailyEntry::create([
                'date' => $date->toDateString(),
                'plan' => $plan,
                'fact' => $fact,
            ]);
        }
    }
}
