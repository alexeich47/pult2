<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Ported 1:1 from Pult 1.2 legacy `js/data.js` COMPANIES array.
     * Order matters for sidebar display; 'holding' is hidden (unit_type null).
     */
    public function run(): void
    {
        $units = [
            ['id' => 'holding',       'name' => 'Холдинг',       'color' => '#6c63ff', 'unit_type' => null,      'sort_order' => 0],

            // Revenue units
            ['id' => 'affcatalog',    'name' => 'AFFCatalog',    'color' => '#3b82f6', 'unit_type' => 'revenue', 'sort_order' => 10],
            ['id' => 'playduck',      'name' => 'PlayDuck',      'color' => '#22c55e', 'unit_type' => 'revenue', 'sort_order' => 20],
            ['id' => 'citadel',       'name' => 'Citadel',       'color' => '#f59e0b', 'unit_type' => 'revenue', 'sort_order' => 30],
            ['id' => '3a',            'name' => '3A',            'color' => '#ec4899', 'unit_type' => 'revenue', 'sort_order' => 40],

            // Service units
            ['id' => 'ironduck',      'name' => 'Iron Duck',     'color' => '#64748b', 'unit_type' => 'service', 'sort_order' => 50],
            ['id' => 'unityservices', 'name' => 'Unity Services', 'color' => '#22d3ee', 'unit_type' => 'service', 'sort_order' => 60],
            ['id' => 'cfo',           'name' => 'CFO',           'color' => '#c084fc', 'unit_type' => 'service', 'sort_order' => 70],
            ['id' => 'cto',           'name' => 'CTO',           'color' => '#fb923c', 'unit_type' => 'service', 'sort_order' => 80],
        ];

        foreach ($units as $attrs) {
            Unit::updateOrCreate(['id' => $attrs['id']], $attrs);
        }
    }
}
