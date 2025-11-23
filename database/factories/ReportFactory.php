<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        $entityType = fake()->randomElement(['persons', 'families', 'cases', 'benefits']);
        $format = fake()->randomElement(['pdf', 'excel', 'csv', 'json']);
        $status = fake()->randomElement(ReportStatus::cases());

        return [
            'user_id' => User::factory(),
            'entity_type' => $entityType,
            'format' => $format,
            'status' => $status,
            'file_path' => $status === ReportStatus::Completed
                ? "reports/2025/11/report-{$this->faker->uuid}.{$format}"
                : null,
            'file_available' => $status === ReportStatus::Completed,
            'parameters' => [
                'filters' => [
                    'created_at' => [
                        'from' => '2025-01-01',
                        'to' => '2025-12-31',
                    ],
                ],
            ],
            'metadata' => $status === ReportStatus::Completed ? [
                'record_count' => fake()->numberBetween(1, 1000),
                'generation_duration_seconds' => fake()->numberBetween(1, 30),
            ] : null,
            'error_message' => $status === ReportStatus::Failed
                ? fake()->sentence()
                : null,
            'generated_at' => $status === ReportStatus::Completed
                ? fake()->dateTimeBetween('-30 days', 'now')
                : null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportStatus::Pending,
            'file_path' => null,
            'file_available' => false,
            'metadata' => null,
            'generated_at' => null,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportStatus::Processing,
            'file_path' => null,
            'file_available' => false,
            'metadata' => null,
            'generated_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportStatus::Completed,
            'file_path' => "reports/2025/11/report-{$this->faker->uuid}.{$attributes['format']}",
            'file_available' => true,
            'metadata' => [
                'record_count' => fake()->numberBetween(1, 1000),
                'generation_duration_seconds' => fake()->numberBetween(1, 30),
            ],
            'generated_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportStatus::Failed,
            'file_path' => null,
            'file_available' => false,
            'error_message' => fake()->sentence(),
            'metadata' => null,
            'generated_at' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportStatus::Expired,
            'file_path' => null,
            'file_available' => false,
            'metadata' => [
                'record_count' => fake()->numberBetween(1, 1000),
                'generation_duration_seconds' => fake()->numberBetween(1, 30),
            ],
            'generated_at' => fake()->dateTimeBetween('-120 days', '-91 days'),
        ]);
    }
}
