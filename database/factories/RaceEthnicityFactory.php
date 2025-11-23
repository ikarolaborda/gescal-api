<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RaceEthnicity>
 */
class RaceEthnicityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'race_color' => fake()->randomElement([
                'branca', 'preta', 'parda', 'amarela', 'indigena', 'nao_declarada',
            ]),
        ];
    }
}
