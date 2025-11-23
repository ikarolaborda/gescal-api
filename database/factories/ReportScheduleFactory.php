<?php

namespace Database\Factories;

use App\Enums\ReportFrequency;
use App\Models\ReportSchedule;
use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportScheduleFactory extends Factory
{
    protected $model = ReportSchedule::class;

    public function definition(): array
    {
        $frequency = fake()->randomElement(ReportFrequency::cases());
        $executionTime = fake()->time('H:i:s');

        return [
            'user_id' => User::factory(),
            'template_id' => fake()->optional()->randomElement([null, ReportTemplate::factory()]),
            'name' => fake()->words(3, true) . ' Schedule',
            'frequency' => $frequency,
            'execution_time' => $executionTime,
            'day_of_week' => $frequency === ReportFrequency::Weekly
                ? fake()->numberBetween(1, 7)
                : null,
            'day_of_month' => $frequency === ReportFrequency::Monthly
                ? fake()->numberBetween(1, 28)
                : null,
            'recipients' => [
                fake()->safeEmail(),
                fake()->safeEmail(),
            ],
            'parameters' => [
                'entity_type' => fake()->randomElement(['persons', 'families', 'cases', 'benefits']),
                'format' => fake()->randomElement(['pdf', 'excel']),
                'filters' => [],
            ],
            'last_execution_at' => fake()->optional()->dateTimeBetween('-7 days', 'now'),
            'next_execution_at' => fake()->dateTimeBetween('now', '+7 days'),
            'failure_count' => 0,
            'is_active' => true,
        ];
    }

    public function daily(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => ReportFrequency::Daily,
            'day_of_week' => null,
            'day_of_month' => null,
        ]);
    }

    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => ReportFrequency::Weekly,
            'day_of_week' => fake()->numberBetween(1, 7),
            'day_of_month' => null,
        ]);
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => ReportFrequency::Monthly,
            'day_of_week' => null,
            'day_of_month' => fake()->numberBetween(1, 28),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'failure_count' => 0,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withFailures(int $count = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'failure_count' => $count,
            'is_active' => $count < 5,
        ]);
    }
}
