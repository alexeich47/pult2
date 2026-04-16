<?php

namespace Database\Seeders;

use App\Models\OkrEntry;
use Illuminate\Database\Seeder;

class OkrEntrySeeder extends Seeder
{
    public function run(): void
    {
        // North Star
        $northStar = OkrEntry::create([
            'unit_id' => null,
            'type' => 'north_star',
            'title' => 'Стать лидером рынка ERM к 2026',
            'description' => 'Построить лучшую ERM-платформу для управления холдингами среднего бизнеса в СНГ и Европе.',
            'period' => '2026',
            'progress' => 35,
            'status' => 'active',
            'sort_order' => 0,
        ]);

        // Objective 1
        $obj1 = OkrEntry::create([
            'unit_id' => null,
            'type' => 'objective',
            'title' => 'Увеличить выручку холдинга на 40%',
            'description' => 'Рост совокупной выручки всех бизнес-юнитов.',
            'period' => 'Q2 2026',
            'progress' => 20,
            'status' => 'active',
            'sort_order' => 1,
        ]);

        OkrEntry::create([
            'unit_id' => null,
            'type' => 'key_result',
            'title' => 'MRR AffCatalog достигнет $50K',
            'period' => 'Q2 2026',
            'progress' => 30,
            'status' => 'active',
            'parent_id' => $obj1->id,
            'sort_order' => 0,
        ]);

        OkrEntry::create([
            'unit_id' => null,
            'type' => 'key_result',
            'title' => 'Запустить 2 новых продукта в PlayDuck',
            'period' => 'Q2 2026',
            'progress' => 10,
            'status' => 'active',
            'parent_id' => $obj1->id,
            'sort_order' => 1,
        ]);

        // Objective 2
        $obj2 = OkrEntry::create([
            'unit_id' => null,
            'type' => 'objective',
            'title' => 'Построить культуру прозрачности и документирования',
            'description' => 'Все процессы, решения и метрики задокументированы в Pult.',
            'period' => 'Q2 2026',
            'progress' => 45,
            'status' => 'active',
            'sort_order' => 2,
        ]);

        OkrEntry::create([
            'unit_id' => null,
            'type' => 'key_result',
            'title' => '100% сотрудников используют Pult ежедневно',
            'period' => 'Q2 2026',
            'progress' => 60,
            'status' => 'active',
            'parent_id' => $obj2->id,
            'sort_order' => 0,
        ]);

        OkrEntry::create([
            'unit_id' => null,
            'type' => 'key_result',
            'title' => 'Все SLA между юнитами зафиксированы в системе',
            'period' => 'Q2 2026',
            'progress' => 20,
            'status' => 'active',
            'parent_id' => $obj2->id,
            'sort_order' => 1,
        ]);

        OkrEntry::create([
            'unit_id' => null,
            'type' => 'key_result',
            'title' => 'Еженедельные отчёты автоматизированы',
            'period' => 'Q2 2026',
            'progress' => 50,
            'status' => 'active',
            'parent_id' => $obj2->id,
            'sort_order' => 2,
        ]);

        // Objective 3
        $obj3 = OkrEntry::create([
            'unit_id' => 'swiftpunk',
            'type' => 'objective',
            'title' => 'Закрыть все критичные вакансии в холдинге',
            'description' => 'CFO, CTO и Head of Sales должны быть наняты до конца квартала.',
            'period' => 'Q2 2026',
            'progress' => 15,
            'status' => 'active',
            'sort_order' => 3,
        ]);

        OkrEntry::create([
            'unit_id' => 'swiftpunk',
            'type' => 'key_result',
            'title' => 'CFO нанят и прошёл онбординг',
            'period' => 'Q2 2026',
            'progress' => 0,
            'status' => 'active',
            'parent_id' => $obj3->id,
            'sort_order' => 0,
        ]);

        OkrEntry::create([
            'unit_id' => 'swiftpunk',
            'type' => 'key_result',
            'title' => 'CTO нанят и приступил к работе',
            'period' => 'Q2 2026',
            'progress' => 25,
            'status' => 'active',
            'parent_id' => $obj3->id,
            'sort_order' => 1,
        ]);
    }
}
