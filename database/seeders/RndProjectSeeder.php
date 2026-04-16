<?php

namespace Database\Seeders;

use App\Models\RndProject;
use Illuminate\Database\Seeder;

class RndProjectSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'unit_id' => 'unityservices',
                'title' => 'AI-ассистент для клиентской поддержки',
                'description' => 'Внедрение AI-чатбота для автоматизации первой линии поддержки клиентов Unity Services.',
                'owner_id' => 3,
                'priority' => 'high',
                'status' => 'testing',
                'budget' => 15000.00,
                'currency' => 'USD',
                'deadline' => '2026-07-01',
                'success_criteria' => 'Бот закрывает 40% обращений без участия оператора, CSAT не ниже 4.2.',
                'kill_criteria' => 'CSAT падает ниже 3.5 или бот генерирует более 10% некорректных ответов за неделю.',
                'started_at' => '2026-03-15',
            ],
            [
                'unit_id' => 'affcatalog',
                'title' => 'Автоматизация биллинга',
                'description' => 'Полная автоматизация выставления счетов и отслеживания оплат для AFFCatalog.',
                'owner_id' => 4,
                'priority' => 'critical',
                'status' => 'pilot',
                'budget' => 8000.00,
                'currency' => 'USD',
                'deadline' => '2026-06-01',
                'success_criteria' => 'Время на биллинг сокращается с 3 дней до 2 часов, 0 ошибок в счетах за месяц.',
                'kill_criteria' => 'Интеграция с банком невозможна технически или стоимость поддержки превышает $2000/мес.',
                'started_at' => '2026-02-01',
            ],
            [
                'unit_id' => 'swiftpunk',
                'title' => 'Новый UI-кит на Vue 4',
                'description' => 'Разработка единой дизайн-системы и компонентной библиотеки для всех проектов холдинга.',
                'owner_id' => 1,
                'priority' => 'medium',
                'status' => 'research',
                'budget' => 3000.00,
                'currency' => 'USD',
                'deadline' => '2026-09-01',
                'success_criteria' => 'Библиотека покрывает 80% UI-паттернов, время верстки новых страниц сокращается на 50%.',
                'kill_criteria' => 'Vue 4 не выходит в стабильной версии до Q3 2026 или команда не может выделить ресурсы.',
                'started_at' => null,
            ],
            [
                'unit_id' => 'citadel',
                'title' => 'ML-скоринг лидов',
                'description' => 'Модель машинного обучения для приоритизации входящих лидов по вероятности конверсии.',
                'owner_id' => 10,
                'priority' => 'high',
                'status' => 'idea',
                'budget' => 12000.00,
                'currency' => 'USD',
                'deadline' => '2026-12-01',
                'success_criteria' => 'Конверсия обработанных лидов растёт на 25%, экономия времени сейлзов — 10 часов/неделю.',
                'kill_criteria' => 'Данных для обучения модели недостаточно (менее 5000 лидов) или точность ниже 60%.',
                'started_at' => null,
            ],
            [
                'unit_id' => 'playduck',
                'title' => 'Мобильное приложение PlayDuck',
                'description' => 'Нативное мобильное приложение для основного продукта PlayDuck на iOS и Android.',
                'owner_id' => 7,
                'priority' => 'high',
                'status' => 'testing',
                'budget' => 25000.00,
                'currency' => 'USD',
                'deadline' => '2026-08-01',
                'success_criteria' => 'DAU мобильного приложения достигает 30% от веб-аудитории за 3 месяца после запуска.',
                'kill_criteria' => 'Retention D7 ниже 15% на протяжении двух недель после soft-launch.',
                'started_at' => '2026-01-10',
            ],
            [
                'unit_id' => 'ironduck',
                'title' => 'Внутренний чат-бот HR',
                'description' => 'Телеграм-бот для автоматизации HR-процессов: отпуска, справки, онбординг.',
                'owner_id' => 3,
                'priority' => 'low',
                'status' => 'paused',
                'budget' => 2000.00,
                'currency' => 'USD',
                'deadline' => null,
                'success_criteria' => 'HR-менеджер экономит 5 часов в неделю, 90% сотрудников используют бота.',
                'kill_criteria' => 'Менее 30% сотрудников начинают пользоваться ботом за первый месяц.',
                'started_at' => '2026-03-01',
            ],
        ];

        foreach ($rows as $row) {
            RndProject::create($row);
        }
    }
}
