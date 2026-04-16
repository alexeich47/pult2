<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitDetailsSeeder extends Seeder
{
    /**
     * Populate unit details (head, deputy, metadata) after employees exist.
     */
    public function run(): void
    {
        Unit::where('id', 'swiftpunk')->update([
            'head_id' => 1,
            'deputy_id' => 3,
            'founded_at' => '2023-01-15',
            'website' => 'swiftpunk.com',
            'stage' => 'growth',
            'description' => 'Управляющая компания холдинга Swift Punk',
            'legal_name' => 'Swift Punk Holdings LLC',
            'inn' => null,
        ]);

        Unit::where('id', 'affcatalog')->update([
            'head_id' => 4,
            'deputy_id' => null,
            'founded_at' => '2023-06-01',
            'website' => 'affcatalog.com',
            'stage' => 'growth',
            'description' => 'Платформа партнёрского маркетинга и каталог офферов',
            'legal_name' => 'AFFCatalog Ltd',
            'inn' => null,
        ]);

        Unit::where('id', 'playduck')->update([
            'head_id' => 7,
            'deputy_id' => null,
            'founded_at' => '2024-02-10',
            'website' => 'playduck.io',
            'stage' => 'startup',
            'description' => 'Игровая платформа нового поколения',
            'legal_name' => 'PlayDuck Inc',
            'inn' => null,
        ]);

        Unit::where('id', '3a')->update([
            'head_id' => null,
            'deputy_id' => null,
            'founded_at' => '2024-09-01',
            'website' => null,
            'stage' => 'startup',
            'description' => 'Экспериментальный бизнес-юнит',
            'legal_name' => null,
            'inn' => null,
        ]);

        Unit::where('id', 'citadel')->update([
            'head_id' => 10,
            'deputy_id' => null,
            'founded_at' => '2023-11-20',
            'website' => 'citadel.fund',
            'stage' => 'maturity',
            'description' => 'Инвестиционный и финансовый юнит холдинга',
            'legal_name' => 'Citadel Finance LLC',
            'inn' => '7701234567',
        ]);

        Unit::where('id', 'ironduck')->update([
            'head_id' => 3,
            'deputy_id' => null,
            'founded_at' => '2023-03-01',
            'website' => null,
            'stage' => 'maturity',
            'description' => 'Сервисная компания: финансы и технологии для холдинга',
            'legal_name' => 'Iron Duck Services LLC',
            'inn' => null,
        ]);

        Unit::where('id', 'unityservices')->update([
            'head_id' => 3,
            'deputy_id' => null,
            'founded_at' => '2024-01-15',
            'website' => null,
            'stage' => 'growth',
            'description' => 'HR, рекрутинг и IT-поддержка холдинга',
            'legal_name' => 'Unity Services LLC',
            'inn' => null,
        ]);
    }
}
