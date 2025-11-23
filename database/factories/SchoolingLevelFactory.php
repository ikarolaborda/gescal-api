<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchoolingLevel>
 */
class SchoolingLevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schooling_level' => fake()->randomElement([
                'fundamental_incompleto', 'fundamental_completo',
                'medio_incompleto', 'medio_completo',
                'superior_incompleto', 'superior_completo',
                'pos_graduacao_incompleto', 'pos_graduacao_completo',
                'nao_declarado',
            ]),
        ];
    }
}
