<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Process;
use App\Models\Unit;
use App\Support\PultEnums;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Process>
 */
class ProcessFactory extends Factory
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
            'category' => fake()->randomElement(PultEnums::processCategories()),
            'maturity' => fake()->randomElement(PultEnums::processMaturityLevels()),
            'document_url' => fake()->optional()->url(),
            'tool' => fake()->optional()->randomElement(['Jira', '1C', 'Notion', 'GitHub Actions', 'Trello']),
            'sort_order' => 0,
        ];
    }
}
