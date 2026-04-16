<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    public function definition(): array
    {
        $unitIds = Unit::pluck('id')->all();

        return [
            'unit_id' => fake()->randomElement($unitIds ?: [null]),
            'title' => fake()->sentence(3),
            'type' => fake()->randomElement(['standup', 'weekly', 'monthly', 'one_on_one', 'retro', 'planning', 'other']),
            'scheduled_at' => fake()->dateTimeBetween('now', '+30 days'),
            'duration_minutes' => fake()->randomElement([15, 30, 45, 60, 90]),
            'location' => fake()->randomElement(['Zoom', 'Google Meet', 'Office', null]),
            'agenda' => fake()->paragraph(),
            'notes' => null,
            'status' => fake()->randomElement(['scheduled', 'completed', 'cancelled']),
            'created_by' => null,
        ];
    }
}
