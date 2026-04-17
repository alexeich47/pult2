<?php

namespace Database\Seeders;

use App\Models\ServicePageAccess;
use App\Support\ServicePages;
use Illuminate\Database\Seeder;

class ServicePageAccessSeeder extends Seeder
{
    /**
     * Seeds the initial per-page access list using ServicePages::defaultsFor().
     * Idempotent: only inserts rows that don't already exist.
     */
    public function run(): void
    {
        foreach (ServicePages::metadata() as $page) {
            foreach (ServicePages::defaultsFor($page['slug']) as $employeeId) {
                ServicePageAccess::firstOrCreate([
                    'page_slug' => $page['slug'],
                    'employee_id' => $employeeId,
                ]);
            }
        }
    }
}
