<?php

namespace Database\Seeders;

use App\Models\Idea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class IdeaSeeder extends Seeder
{
    /**
     * Ported 1:1 from Pult 1.2 legacy `js/pages/IdeasPage.js` IDEAS_DATA array.
     * Legacy `createdAt` DD.MM.YYYY strings are promoted to real datetimes.
     * authorId=1 in legacy refers to Алексей Шастин (first employee).
     */
    public function run(): void
    {
        $rows = [
            [
                'unit_id' => 'affcatalog',
                'author_id' => 1,
                'priority' => 'high',
                'status' => 'under_review',
                'title' => 'Автоматический парсинг каталога конкурентов',
                'description' => 'Настроить автоматический парсинг каталогов ключевых конкурентов для отслеживания ценовых изменений и новых позиций в режиме реального времени.',
                'impact' => 'Позволит оперативно реагировать на изменения рынка, удерживать конкурентные цены и не упускать новые категории товаров. Ожидаемый эффект — снижение времени на мониторинг конкурентов на 80%.',
                'created_at' => Carbon::parse('2025-04-01'),
            ],
            [
                'unit_id' => 'playduck',
                'author_id' => 1,
                'priority' => 'high',
                'status' => 'approved',
                'title' => 'Реферальная программа для клиентов',
                'description' => 'Запустить реферальную программу: действующий клиент приводит нового — получает скидку или бонус на следующий период подписки.',
                'impact' => 'Снижение CAC на 30-40%. Вирусный рост через доверие существующих клиентов. При конверсии 5% от текущей базы — +20 новых клиентов в первый квартал.',
                'created_at' => Carbon::parse('2025-04-03'),
            ],
            [
                'unit_id' => 'holding',
                'author_id' => 1,
                'priority' => 'high',
                'status' => 'in_progress',
                'title' => 'Единый дашборд KPI по всем компаниям',
                'description' => 'Создать единый экран с ключевыми метриками всех компаний холдинга: выручка, маржа, количество активных клиентов, NPS.',
                'impact' => 'Сокращение времени на сбор отчётности с 4 часов в неделю до реального времени. Руководство холдинга видит полную картину одним взглядом.',
                'created_at' => Carbon::parse('2025-04-05'),
            ],
            [
                'unit_id' => 'citadel',
                'author_id' => 1,
                'priority' => 'medium',
                'status' => 'new',
                'title' => 'Пакетные предложения для корпоративных клиентов',
                'description' => 'Сформировать пакеты услуг для корпоративного сегмента с фиксированной ценой и прозрачным SLA.',
                'impact' => 'Увеличение среднего чека на 40-60%, предсказуемый доход по подписочной модели.',
                'created_at' => Carbon::parse('2025-04-08'),
            ],
            [
                'unit_id' => '3a',
                'author_id' => 1,
                'priority' => 'medium',
                'status' => 'new',
                'title' => 'Автоматизация онбординга новых сотрудников',
                'description' => 'Создать автоматический чек-лист для онбординга: документы, доступы, знакомство с командой, первые задачи на 30-60-90 дней.',
                'impact' => 'Сокращение времени выхода на продуктивность с 3 недель до 1 недели. Снижение нагрузки на HR на 50%.',
                'created_at' => Carbon::parse('2025-04-10'),
            ],
            [
                'unit_id' => 'affcatalog',
                'author_id' => 1,
                'priority' => 'low',
                'status' => 'rejected',
                'title' => 'Программа лояльности с накопительными баллами',
                'description' => 'Внедрить накопительные баллы за покупки с возможностью обмена на скидку или бонусный товар.',
                'impact' => 'Увеличение LTV клиента. Однако высокая стоимость разработки при текущей базе ставит под вопрос окупаемость — идея отложена.',
                'created_at' => Carbon::parse('2025-04-12'),
            ],
        ];

        foreach ($rows as $index => $row) {
            $idea = new Idea;
            $idea->fill($row);
            $idea->display_id = 'ID-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $idea->created_at = $row['created_at'];
            $idea->updated_at = $row['created_at'];
            $idea->save();
        }
    }
}
