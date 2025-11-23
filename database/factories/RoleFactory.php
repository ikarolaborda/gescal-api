<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => str_replace(' ', '_', strtolower($name)),
            'description' => fake()->sentence(),
            'is_system' => false,
        ];
    }

    public function coordinator(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Coordinator',
            'slug' => 'coordinator',
            'description' => 'Reviews, approves, or rejects benefit requests and case decisions',
            'is_system' => true,
        ]);
    }

    public function socialWorker(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Social Worker',
            'slug' => 'social_worker',
            'description' => 'Creates and submits requests, responds to document requests, manages cases and benefits',
            'is_system' => true,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Full system access, can override decisions, manage users, and access all data including PII',
            'is_system' => true,
        ]);
    }
}
