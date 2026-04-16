<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Idea;
use App\Models\Unit;
use App\Support\PultEnums;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Idea>
 */
class IdeaFactory extends Factory
{
    public function definition(): array
    {
        $unitIds = Unit::pluck('id')->all();
        $authorIds = Employee::where('status', 'active')->pluck('id')->all();

        return [
            'unit_id' => fake()->randomElement($unitIds ?: ['swiftpunk']),
            'author_id' => fake()->randomElement($authorIds ?: [1]),
            'priority' => fake()->randomElement(PultEnums::ideaPriorities()),
            'status' => fake()->randomElement(PultEnums::ideaStatuses()),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraphs(2, true),
            'impact' => fake()->paragraph(),
        ];
    }
}
