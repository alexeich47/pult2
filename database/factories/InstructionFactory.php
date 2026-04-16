<?php

namespace Database\Factories;

use App\Models\Instruction;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Instruction>
 */
class InstructionFactory extends Factory
{
    public function definition(): array
    {
        $unitIds = Unit::pluck('id')->all();
        $type = fake()->randomElement(['instruction', 'checklist']);

        return [
            'unit_id' => fake()->randomElement($unitIds ?: [null]),
            'title' => fake()->sentence(4),
            'type' => $type,
            'content' => $type === 'instruction' ? fake()->paragraphs(2, true) : null,
            'checklist_items' => $type === 'checklist'
                ? array_map(fn () => ['text' => fake()->sentence(3), 'checked' => fake()->boolean()], range(1, 4))
                : null,
            'created_by' => null,
        ];
    }
}
