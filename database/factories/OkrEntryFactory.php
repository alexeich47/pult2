<?php

namespace Database\Factories;

use App\Models\OkrEntry;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OkrEntry>
 */
class OkrEntryFactory extends Factory
{
    public function definition(): array
    {
        $unitIds = Unit::pluck('id')->all();

        return [
            'unit_id' => fake()->randomElement($unitIds ?: [null]),
            'type' => fake()->randomElement(['objective', 'key_result']),
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'period' => fake()->randomElement(['Q1 2026', 'Q2 2026', 'Q3 2026', '2026']),
            'progress' => fake()->numberBetween(0, 100),
            'status' => fake()->randomElement(['active', 'completed', 'cancelled']),
            'parent_id' => null,
            'sort_order' => 0,
        ];
    }
}
