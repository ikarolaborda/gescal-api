<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReportExecutionHistory>
 */
class ReportExecutionHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-1 month', 'now');
        $completedAt = fake()->dateTimeBetween($startedAt, 'now');

        return [
            'report_schedule_id' => \App\Models\ReportSchedule::factory(),
            'report_id' => \App\Models\Report::factory(),
            'status' => fake()->randomElement(['completed', 'failed']),
            'error_message' => null,
            'executed_at' => $completedAt,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
        ];
    }

    /**
     * Indicate that the execution is still processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
            'started_at' => now(),
            'completed_at' => null,
            'report_id' => null,
        ]);
    }

    /**
     * Indicate that the execution completed successfully.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'error_message' => null,
        ]);
    }

    /**
     * Indicate that the execution failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => fake()->sentence(),
            'report_id' => null,
        ]);
    }
}
