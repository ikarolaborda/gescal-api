<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'sex' => fake()->randomElement(['Masculino', 'Feminino']),
            'birth_date' => fake()->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
            'filiation_text' => fake()->optional()->name() . ' e ' . fake()->name(),
            'nationality' => fake()->optional(0.9)->randomElement(['brasileiro', 'brasileira']),
            'natural_city' => fake()->city(),
            'natural_federation_unit_id' => \App\Models\FederationUnit::factory(),
            'race_ethnicity_id' => \App\Models\RaceEthnicity::factory(),
            'marital_status_id' => \App\Models\MaritalStatus::factory(),
            'schooling_level_id' => \App\Models\SchoolingLevel::factory(),
            'primary_phone' => fake()->optional()->phoneNumber(),
            'secondary_phone' => fake()->optional(0.3)->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
        ];
    }
}
