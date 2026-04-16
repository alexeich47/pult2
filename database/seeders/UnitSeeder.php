<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Org structure for the Swift Punk holding.
     * 'swiftpunk' is the root (unit_type null).
     * Iron Duck has two sub-units: findep and techdep.
     */
    public function run(): void
    {
        $units = [
            ['id' => 'swiftpunk',      'name' => 'Swift Punk',      'color' => '#6c63ff', 'unit_type' => null,      'parent_id' => null,         'sort_order' => 0],

            // Revenue units
            ['id' => 'affcatalog',     'name' => 'AFFCatalog',      'color' => '#3b82f6', 'unit_type' => 'revenue', 'parent_id' => 'swiftpunk',  'sort_order' => 10],
            ['id' => 'playduck',       'name' => 'PlayDuck',        'color' => '#22c55e', 'unit_type' => 'revenue', 'parent_id' => 'swiftpunk',  'sort_order' => 20],
            ['id' => '3a',             'name' => '3A',              'color' => '#ec4899', 'unit_type' => 'revenue', 'parent_id' => 'swiftpunk',  'sort_order' => 30],
            ['id' => 'citadel',        'name' => 'Citadel',         'color' => '#f59e0b', 'unit_type' => 'revenue', 'parent_id' => 'swiftpunk',  'sort_order' => 40],

            // Service units
            ['id' => 'ironduck',       'name' => 'Iron Duck',       'color' => '#64748b', 'unit_type' => 'service', 'parent_id' => 'swiftpunk',  'sort_order' => 50],
            ['id' => 'findep',         'name' => 'Фин. деп',       'color' => '#c084fc', 'unit_type' => 'service', 'parent_id' => 'ironduck',   'sort_order' => 60],
            ['id' => 'techdep',        'name' => 'Тех. деп',       'color' => '#fb923c', 'unit_type' => 'service', 'parent_id' => 'ironduck',   'sort_order' => 70],
            ['id' => 'unityservices',  'name' => 'Unity Services',  'color' => '#22d3ee', 'unit_type' => 'service', 'parent_id' => 'swiftpunk',  'sort_order' => 80],
            ['id' => 'us-hr',          'name' => 'HR',              'color' => '#a78bfa', 'unit_type' => 'service', 'parent_id' => 'unityservices', 'sort_order' => 81],
            ['id' => 'us-recruiting',  'name' => 'Рекрутинг',       'color' => '#f472b6', 'unit_type' => 'service', 'parent_id' => 'unityservices', 'sort_order' => 82],
            ['id' => 'us-it',          'name' => 'IT',              'color' => '#38bdf8', 'unit_type' => 'service', 'parent_id' => 'unityservices', 'sort_order' => 83],
        ];

        foreach ($units as $attrs) {
            Unit::updateOrCreate(['id' => $attrs['id']], $attrs);
        }
    }
}
