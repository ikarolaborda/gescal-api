<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Prefeitura Municipal de ' . fake()->city(),
            'cnpj' => $this->generateValidCnpj(),
            'status' => 'active',
        ];
    }

    /**
     * Generate a valid Brazilian CNPJ.
     */
    protected function generateValidCnpj(): string
    {
        // Generate first 12 digits
        $cnpj = '';
        for ($i = 0; $i < 12; $i++) {
            $cnpj .= random_int(0, 9);
        }

        // Calculate first check digit
        $sum = 0;
        $multiplier = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $multiplier[$i];
        }
        $remainder = $sum % 11;
        $cnpj .= $remainder < 2 ? 0 : 11 - $remainder;

        // Calculate second check digit
        $sum = 0;
        $multiplier = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $multiplier[$i];
        }
        $remainder = $sum % 11;
        $cnpj .= $remainder < 2 ? 0 : 11 - $remainder;

        return $cnpj;
    }

    /**
     * Indicate that the organization is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
