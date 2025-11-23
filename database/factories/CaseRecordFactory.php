<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CaseRecord>
 */
class CaseRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'family_id' => \App\Models\Family::factory(),
            'occurrence_id' => null,
            'housing_unit_id' => null,
            'dc_number' => fake()->unique()->numerify('DC-####-####'),
            'dc_year' => fake()->year(),
            'service_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
