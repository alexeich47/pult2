<?php

namespace Database\Seeders;

use App\Models\Meeting;
use Illuminate\Database\Seeder;

class MeetingSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'unit_id' => null,
                'title' => 'Еженедельный стендап холдинга',
                'type' => 'standup',
                'scheduled_at' => now()->addDays(1)->setTime(10, 0),
                'duration_minutes' => 15,
                'location' => 'Zoom',
                'agenda' => "1. Статус задач за прошлую неделю\n2. Планы на текущую неделю\n3. Блокеры и вопросы",
                'status' => 'scheduled',
            ],
            [
                'unit_id' => null,
                'title' => 'Ежемесячный обзор метрик',
                'type' => 'monthly',
                'scheduled_at' => now()->addDays(5)->setTime(14, 0),
                'duration_minutes' => 60,
                'location' => 'Google Meet',
                'agenda' => "1. Обзор MRR по юнитам\n2. Статус OKR\n3. Финансовые показатели\n4. Планы на следующий месяц",
                'status' => 'scheduled',
            ],
            [
                'unit_id' => 'affcatalog',
                'title' => 'Планирование спринта AffCatalog',
                'type' => 'planning',
                'scheduled_at' => now()->addDays(2)->setTime(11, 0),
                'duration_minutes' => 90,
                'location' => 'Office',
                'agenda' => "1. Ревью бэклога\n2. Оценка задач\n3. Формирование спринта",
                'status' => 'scheduled',
            ],
            [
                'unit_id' => 'playduck',
                'title' => 'Ретроспектива PlayDuck Q1',
                'type' => 'retro',
                'scheduled_at' => now()->addDays(3)->setTime(15, 0),
                'duration_minutes' => 45,
                'location' => 'Zoom',
                'agenda' => "1. Что пошло хорошо\n2. Что можно улучшить\n3. Action items",
                'status' => 'scheduled',
            ],
            [
                'unit_id' => 'swiftpunk',
                'title' => '1-on-1: CEO ↔ COO',
                'type' => 'one_on_one',
                'scheduled_at' => now()->addDays(1)->setTime(16, 0),
                'duration_minutes' => 30,
                'location' => 'Office',
                'agenda' => "1. Текущие приоритеты\n2. Обратная связь\n3. Планы развития",
                'status' => 'scheduled',
            ],
        ];

        foreach ($rows as $row) {
            Meeting::create($row);
        }
    }
}
