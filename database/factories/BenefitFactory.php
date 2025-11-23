<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Benefit>
 */
class BenefitFactory extends Factory
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
            'person_id' => null,
            'benefit_program_id' => \App\Models\BenefitProgram::factory(),
            'value' => fake()->randomFloat(2, 100, 5000),
            'is_active' => fake()->boolean(80),
            'started_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'ended_at' => fake()->optional(0.3)->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
        ];
    }
}
