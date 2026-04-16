<?php

namespace Database\Seeders;

use App\Models\Instruction;
use Illuminate\Database\Seeder;

class InstructionSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'unit_id' => null,
                'title' => 'Онбординг нового сотрудника',
                'type' => 'instruction',
                'content' => "## Процесс онбординга\n\n1. Создать учётные записи: email, Slack, Git, Jira\n2. Выдать доступы к внутренним системам\n3. Назначить наставника из команды\n4. Провести вводную встречу с руководителем\n5. Показать кодекс и словарь холдинга\n6. Добавить в календарь регулярных встреч\n7. Через 2 недели — первый check-in",
                'checklist_items' => null,
            ],
            [
                'unit_id' => null,
                'title' => 'Процесс ревью кода',
                'type' => 'instruction',
                'content' => "## Code Review Protocol\n\n- Каждый PR должен получить минимум 1 approve\n- Автор PR обязан описать what/why/how в описании\n- Reviewer проверяет: корректность, читаемость, тесты, безопасность\n- Блокирующие замечания помечаются как **blocker**\n- Максимальное время ожидания ревью — 24 часа\n- После approve — автор мержит сам",
                'checklist_items' => null,
            ],
            [
                'unit_id' => 'swiftpunk',
                'title' => 'Чек-лист запуска проекта',
                'type' => 'checklist',
                'content' => null,
                'checklist_items' => [
                    ['text' => 'Бизнес-план утверждён CEO', 'checked' => true],
                    ['text' => 'Бюджет согласован с CFO', 'checked' => true],
                    ['text' => 'Команда сформирована', 'checked' => false],
                    ['text' => 'Инфраструктура развёрнута', 'checked' => false],
                    ['text' => 'Домен и SSL готовы', 'checked' => false],
                    ['text' => 'CI/CD настроен', 'checked' => false],
                    ['text' => 'Мониторинг подключён', 'checked' => false],
                ],
            ],
            [
                'unit_id' => null,
                'title' => 'Чек-лист оффбординга',
                'type' => 'checklist',
                'content' => null,
                'checklist_items' => [
                    ['text' => 'Передача дел коллеге', 'checked' => false],
                    ['text' => 'Отключение доступов к системам', 'checked' => false],
                    ['text' => 'Возврат оборудования', 'checked' => false],
                    ['text' => 'Exit-интервью проведено', 'checked' => false],
                    ['text' => 'Удаление из Slack и email', 'checked' => false],
                ],
            ],
        ];

        foreach ($rows as $row) {
            Instruction::create($row);
        }
    }
}
