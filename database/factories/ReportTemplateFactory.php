<?php

namespace Database\Factories;

use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportTemplateFactory extends Factory
{
    protected $model = ReportTemplate::class;

    public function definition(): array
    {
        $entityType = fake()->randomElement(['persons', 'families', 'cases', 'benefits']);

        $fieldsMap = [
            'persons' => ['id', 'full_name', 'birth_date', 'email', 'primary_phone'],
            'families' => ['id', 'responsible_person_name', 'address', 'member_count'],
            'cases' => ['id', 'case_number', 'status', 'created_at'],
            'benefits' => ['id', 'benefit_name', 'is_active', 'amount'],
        ];

        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true) . ' Report',
            'description' => fake()->sentence(),
            'entity_type' => $entityType,
            'configuration' => [
                'fields' => $fieldsMap[$entityType],
                'calculations' => fake()->randomElement([[], ['count'], ['count', 'sum'], ['count', 'average']]),
                'grouping' => fake()->optional()->randomElement(['origin_federation_unit', 'status', 'type']),
            ],
            'is_shared' => fake()->boolean(30),
            'organization_id' => fake()->optional()->numberBetween(1, 10),
        ];
    }

    public function shared(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_shared' => true,
            'organization_id' => 1,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_shared' => false,
            'organization_id' => null,
        ]);
    }
}
