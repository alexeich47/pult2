<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Unit;
use App\Support\PultEnums;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        $unitIds = Unit::pluck('id')->all();
        $status = fake()->randomElement(PultEnums::employeeStatuses());
        $isVacancy = $status === 'vacancy';

        return [
            'unit_id' => fake()->randomElement($unitIds ?: ['holding']),
            'name' => $isVacancy ? null : fake()->name(),
            'position' => fake()->jobTitle(),
            'department' => fake()->randomElement(PultEnums::departments()),
            'email' => $isVacancy ? null : fake()->companyEmail(),
            'telegram' => $isVacancy ? null : '@'.fake()->userName(),
            'status' => $status,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => 'active',
            'name' => fake()->name(),
            'email' => fake()->companyEmail(),
            'telegram' => '@'.fake()->userName(),
        ]);
    }

    public function vacancy(): static
    {
        return $this->state(fn () => [
            'status' => 'vacancy',
            'name' => null,
            'email' => null,
            'telegram' => null,
        ]);
    }
}
