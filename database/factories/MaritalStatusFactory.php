<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaritalStatus>
 */
class MaritalStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'marital_status' => fake()->randomElement([
                'solteiro', 'casado', 'divorciado', 'viuvo', 'união_estável', 'nao_declarado',
            ]),
        ];
    }
}
