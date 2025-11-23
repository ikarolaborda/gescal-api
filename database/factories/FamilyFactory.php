<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Family>
 */
class FamilyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Family>
     */
    protected $model = \App\Models\Family::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'responsible_person_id' => \App\Models\Person::factory(),
            'address_id' => \App\Models\Address::factory(),
            'origin_city' => fake()->city(),
            'origin_federation_unit_id' => \App\Models\FederationUnit::inRandomOrder()->first()?->id ?? 1,
            'family_income_bracket' => fake()->randomElement([
                'Até 1 salário mínimo',
                '1 a 2 salários mínimos',
                '2 a 3 salários mínimos',
                '3 a 5 salários mínimos',
                'Acima de 5 salários mínimos',
            ]),
            'family_income_value' => fake()->randomFloat(2, 500, 10000),
        ];
    }
}
