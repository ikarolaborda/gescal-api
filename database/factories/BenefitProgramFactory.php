<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BenefitProgram>
 */
class BenefitProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Bolsa Família',
                'Auxílio Emergencial',
                'Cesta Básica',
                'Vale-Gás',
                'Tarifa Social de Energia',
                'Benefício de Prestação Continuada (BPC)',
            ]),
            'code' => fake()->unique()->regexify('[A-Z]{2}[0-9]{4}'),
        ];
    }
}
