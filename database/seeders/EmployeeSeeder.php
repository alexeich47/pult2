<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Ported 1:1 from Pult 1.2 legacy `js/data.js` employees array.
     * null name => vacancy (status enforced).
     */
    public function run(): void
    {
        $rows = [
            ['name' => 'Алексей Шастин',  'position' => 'CEO',               'unit_id' => 'holding',    'department' => 'Руководство', 'email' => 'a.shastin@holding.com',   'telegram' => '@alexeich47', 'status' => 'active'],
            ['name' => null,              'position' => 'CFO',               'unit_id' => 'holding',    'department' => 'Финансы',     'email' => null,                      'telegram' => null,          'status' => 'vacancy'],
            ['name' => 'Елена Голубева',  'position' => 'COO',               'unit_id' => 'holding',    'department' => 'Операции',    'email' => 'e.golubeva@holding.com',  'telegram' => '@golubelena', 'status' => 'active'],
            ['name' => 'Ярослав Стецик',  'position' => 'Managing Director', 'unit_id' => 'affcatalog', 'department' => 'Руководство', 'email' => 'ya.stetsyk@affcatalog.com', 'telegram' => '@yarstetsyk', 'status' => 'active'],
            ['name' => null,              'position' => 'Head of Sales',     'unit_id' => 'affcatalog', 'department' => 'Продажи',     'email' => null,                      'telegram' => null,          'status' => 'vacancy'],
            ['name' => null,              'position' => 'CTO',               'unit_id' => 'holding',    'department' => 'Технологии',  'email' => null,                      'telegram' => null,          'status' => 'vacancy'],
            ['name' => 'Ольга Татаринова', 'position' => 'Managing Director', 'unit_id' => 'playduck',   'department' => 'Руководство', 'email' => 'o.tatarinova@playduck.com', 'telegram' => '@o7ga_7es',   'status' => 'active'],
            ['name' => null,              'position' => 'Product Manager',   'unit_id' => 'playduck',   'department' => 'Продукт',     'email' => null,                      'telegram' => null,          'status' => 'vacancy'],
            ['name' => null,              'position' => 'Lead Developer',    'unit_id' => 'playduck',   'department' => 'Технологии',  'email' => null,                      'telegram' => null,          'status' => 'vacancy'],
            ['name' => 'Олег Господарь',  'position' => 'Managing Director', 'unit_id' => 'citadel',    'department' => 'Руководство', 'email' => 'o.gospodar@citadel.com',  'telegram' => null,          'status' => 'active'],
            ['name' => null,              'position' => 'Managing Director', 'unit_id' => '3a',         'department' => 'Руководство', 'email' => null,                      'telegram' => null,          'status' => 'vacancy'],
        ];

        foreach ($rows as $row) {
            Employee::create($row);
        }
    }
}
