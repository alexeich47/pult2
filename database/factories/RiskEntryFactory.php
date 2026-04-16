<?php

namespace Database\Factories;

use App\Models\RiskEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiskEntry>
 */
class RiskEntryFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['risk', 'issue', 'fuckup', 'workaround']);
        $statuses = RiskEntry::STATUSES_BY_TYPE[$type];

        return [
            'type' => $type,
            'name' => fake()->sentence(4),
            'description' => fake()->paragraphs(2, true),
            'owner_name' => fake()->lastName().' '.mb_substr(fake()->firstName(), 0, 1).'.',
            'status' => fake()->randomElement($statuses),
            'entry_date' => fake()->dateTimeBetween('-3 months'),
        ];
    }
}
