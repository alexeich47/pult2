<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Unit;
use App\Support\PultEnums;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $unitIds = Unit::pluck('id')->all();

        return [
            'name' => fake()->company().' '.fake()->randomElement(['Cloud', 'Pro', 'Suite', 'Hub', 'API']),
            'url' => fake()->domainName(),
            'category' => fake()->randomElement(PultEnums::serviceCategories()),
            'unit_id' => fake()->randomElement($unitIds ?: ['holding']),
            'cost' => fake()->randomFloat(2, 0, 500),
            'currency' => fake()->randomElement(PultEnums::serviceCurrencies()),
            'billing' => fake()->randomElement(PultEnums::serviceBillingCycles()),
            'next_payment' => fake()->dateTimeBetween('now', '+6 months'),
            'status' => fake()->randomElement(PultEnums::serviceStatuses()),
        ];
    }
}
