<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'street' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'complement' => fake()->optional()->secondaryAddress(),
            'neighborhood' => fake()->optional()->citySuffix(),
            'city' => fake()->city(),
            'state_id' => \App\Models\FederationUnit::inRandomOrder()->first()?->id ?? 1,
            'zip_code' => fake()->optional()->postcode(),
            'reference_point' => fake()->optional()->sentence(),
        ];
    }
}
