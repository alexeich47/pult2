<?php

namespace Database\Seeders;

use App\Models\RiskEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RiskEntrySeeder extends Seeder
{
    /**
     * Ported 1:1 from Pult 1.2 legacy `js/pages/RisksPage.js` RISKS_DATA.
     * Legacy `date` DD.MM.YYYY is promoted to a real date column.
     */
    public function run(): void
    {
        $rows = [
            // Risk log
            ['type' => 'risk', 'name' => 'Потеря ключевого сотрудника', 'entry_date' => '2025-04-10', 'owner_name' => 'Иванов И.', 'status' => 'open', 'description' => 'Риск ухода CTO до завершения проекта. Нет дублёра на критических компетенциях. Необходима кросс-тренировка команды.'],
            ['type' => 'risk', 'name' => 'Недостаток финансирования Q3', 'entry_date' => '2025-04-12', 'owner_name' => 'Петров С.', 'status' => 'mitigated', 'description' => 'Риск кассового разрыва в третьем квартале. Проработан резервный кредитный лимит с банком-партнёром.'],
            ['type' => 'risk', 'name' => 'Зависимость от одного поставщика', 'entry_date' => '2025-04-14', 'owner_name' => 'Козлов Д.', 'status' => 'open', 'description' => 'Критический компонент поставляет единственный вендор. Поиск альтернативных поставщиков в процессе.'],

            // Issue log
            ['type' => 'issue', 'name' => 'Сбой интеграции с банком', 'entry_date' => '2025-04-08', 'owner_name' => 'Сидоров А.', 'status' => 'in_progress', 'description' => 'Эквайринг не принимает платежи по картам МИР с 08.04. Техподдержка банка подключена, решение ожидается.'],
            ['type' => 'issue', 'name' => 'Задержка поставки оборудования', 'entry_date' => '2025-04-11', 'owner_name' => 'Козлов Д.', 'status' => 'closed', 'description' => 'Сервер для нового офиса задержан на таможне. Закрыто — оборудование получено и установлено.'],

            // Fuckup log
            ['type' => 'fuckup', 'name' => 'Письмо не тому клиенту', 'entry_date' => '2025-04-05', 'owner_name' => 'Новикова М.', 'status' => 'closed', 'description' => 'Коммерческое предложение с внутренними ценами случайно ушло стороннему адресату. Клиент уведомлён об ошибке, дополнительное соглашение подписано.'],
            ['type' => 'fuckup', 'name' => 'Удаление тестовой БД', 'entry_date' => '2025-04-13', 'owner_name' => 'Волков Е.', 'status' => 'open', 'description' => 'Тестовая база данных удалена без актуального резервного копирования. Восстановление невозможно. Введены обязательные правила бэкапа.'],

            // Workaround log
            ['type' => 'workaround', 'name' => 'Ручная выгрузка отчётов', 'entry_date' => '2025-04-01', 'owner_name' => 'Смирнова Т.', 'status' => 'active', 'description' => 'До запуска автоматической отчётности — ручная выгрузка каждый понедельник. Ответственная: Смирнова Т. Костыль будет снят после запуска модуля отчётов.'],
            ['type' => 'workaround', 'name' => 'Дубли контактов в CRM', 'entry_date' => '2025-04-09', 'owner_name' => 'Фёдоров Н.', 'status' => 'active', 'description' => 'Слияние компаний создаёт дублирующиеся контакты. Временное решение — ручная проверка перед созданием сделки. Дедупликация запланирована.'],
        ];

        $counters = ['risk' => 0, 'issue' => 0, 'fuckup' => 0, 'workaround' => 0];

        foreach ($rows as $row) {
            $counters[$row['type']]++;
            $prefix = RiskEntry::TYPE_PREFIXES[$row['type']];

            $entry = new RiskEntry;
            $entry->fill($row);
            $entry->display_id = $prefix.'-'.str_pad((string) $counters[$row['type']], 3, '0', STR_PAD_LEFT);
            $entry->entry_date = Carbon::parse($row['entry_date']);
            $entry->save();
        }
    }
}
