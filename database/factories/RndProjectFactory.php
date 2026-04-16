<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\RndProject;
use App\Models\Unit;
use App\Support\PultEnums;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RndProject>
 */
class RndProjectFactory extends Factory
{
    public function definition(): array
    {
        $unitIds = Unit::pluck('id')->all();
        $ownerIds = Employee::where('status', 'active')->pluck('id')->all();

        return [
            'unit_id' => fake()->randomElement($unitIds ?: ['swiftpunk']),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'owner_id' => fake()->randomElement($ownerIds ?: [1]),
            'priority' => fake()->randomElement(PultEnums::rndPriorities()),
            'status' => fake()->randomElement(PultEnums::rndStatuses()),
            'budget' => fake()->randomFloat(2, 1000, 50000),
            'currency' => fake()->randomElement(['USD', 'EUR', 'UAH', 'RUB']),
            'deadline' => fake()->optional()->dateTimeBetween('now', '+6 months'),
            'success_criteria' => fake()->paragraph(),
            'kill_criteria' => fake()->paragraph(),
            'started_at' => fake()->optional()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
