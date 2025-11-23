<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $resource = fake()->randomElement(['persons', 'families', 'cases', 'benefits', 'users']);
        $action = fake()->randomElement(['view', 'create', 'update', 'delete']);
        $name = ucfirst($action) . ' ' . ucfirst($resource);

        return [
            'name' => $name,
            'slug' => $action . '_' . $resource,
            'resource' => $resource,
            'action' => $action,
            'description' => fake()->sentence(),
            'is_system' => false,
        ];
    }
}
