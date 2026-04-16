<?php

namespace Database\Seeders;

use App\Models\Process;
use Illuminate\Database\Seeder;

class ProcessSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'unit_id' => 'unityservices',
                'title' => 'Онбординг сотрудника',
                'description' => 'Процесс ввода нового сотрудника: документы, доступы, знакомство с командой, обучение.',
                'owner_id' => 3,
                'category' => 'HR',
                'maturity' => 'documented_testing',
                'document_url' => null,
                'tool' => null,
                'sort_order' => 1,
            ],
            [
                'unit_id' => null,
                'title' => 'Закрытие месяца',
                'description' => 'Ежемесячное сведение финансовых данных, сверка, подготовка отчётности.',
                'owner_id' => 4,
                'category' => 'Финансы',
                'maturity' => 'fully_automated',
                'document_url' => null,
                'tool' => '1C',
                'sort_order' => 2,
            ],
            [
                'unit_id' => null,
                'title' => 'Код-ревью',
                'description' => 'Обязательная проверка кода перед мержем: стандарты, чек-лист, ответственные.',
                'owner_id' => 1,
                'category' => 'Разработка',
                'maturity' => 'documented_digitized',
                'document_url' => null,
                'tool' => 'GitHub',
                'sort_order' => 3,
            ],
            [
                'unit_id' => 'affcatalog',
                'title' => 'Лидогенерация',
                'description' => 'Привлечение и квалификация лидов через рекламные каналы и партнёрские программы.',
                'owner_id' => 10,
                'category' => 'Маркетинг',
                'maturity' => 'not_documented',
                'document_url' => null,
                'tool' => null,
                'sort_order' => 4,
            ],
            [
                'unit_id' => null,
                'title' => 'Деплой продакшена',
                'description' => 'CI/CD пайплайн: тесты, билд, деплой на продакшен-серверы.',
                'owner_id' => 1,
                'category' => 'Разработка',
                'maturity' => 'fully_automated',
                'document_url' => null,
                'tool' => 'GitHub Actions',
                'sort_order' => 5,
            ],
            [
                'unit_id' => 'swiftpunk',
                'title' => 'Квартальное планирование',
                'description' => 'Подведение итогов квартала, постановка OKR и распределение ресурсов.',
                'owner_id' => 7,
                'category' => 'Операции',
                'maturity' => 'documented_testing',
                'document_url' => null,
                'tool' => null,
                'sort_order' => 6,
            ],
            [
                'unit_id' => 'unityservices',
                'title' => 'Обработка тикетов поддержки',
                'description' => 'Приём, классификация и решение клиентских обращений через тикет-систему.',
                'owner_id' => 3,
                'category' => 'Операции',
                'maturity' => 'documented_digitized',
                'document_url' => null,
                'tool' => 'Jira',
                'sort_order' => 7,
            ],
            [
                'unit_id' => 'ironduck',
                'title' => 'Оформление договоров',
                'description' => 'Подготовка, согласование и подписание юридических документов с контрагентами.',
                'owner_id' => null,
                'category' => 'Юридический',
                'maturity' => 'not_documented',
                'document_url' => null,
                'tool' => null,
                'sort_order' => 8,
            ],
        ];

        foreach ($rows as $row) {
            Process::create($row);
        }
    }
}
