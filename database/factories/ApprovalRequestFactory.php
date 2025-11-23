<?php

namespace Database\Factories;

use App\Models\ApprovalRequest;
use App\Models\Benefit;
use App\Models\CaseRecord;
use App\Models\Family;
use App\Models\Person;
use App\Models\User;
use App\States\ApprovalRequest\ApprovedPrelimState;
use App\States\ApprovalRequest\ApprovedState;
use App\States\ApprovalRequest\CancelledState;
use App\States\ApprovalRequest\DraftState;
use App\States\ApprovalRequest\ExpiredState;
use App\States\ApprovalRequest\PendingDocumentsState;
use App\States\ApprovalRequest\RejectedState;
use App\States\ApprovalRequest\RevokedState;
use App\States\ApprovalRequest\SubmittedState;
use App\States\ApprovalRequest\UnderReviewState;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprovalRequestFactory extends Factory
{
    protected $model = ApprovalRequest::class;

    public function definition(): array
    {
        return [
            'case_id' => CaseRecord::factory(),
            'benefit_id' => fake()->boolean(70) ? Benefit::factory() : null,
            'family_id' => fake()->boolean(80) ? Family::factory() : null,
            'person_id' => fake()->boolean(60) ? Person::factory() : null,
            'status' => DraftState::class,
            'submitted_by_user_id' => null,
            'decided_by_user_id' => null,
            'decided_at' => null,
            'reason' => null,
            'metadata' => [],
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DraftState::class,
            'submitted_by_user_id' => null,
            'decided_by_user_id' => null,
            'decided_at' => null,
            'reason' => null,
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubmittedState::class,
            'submitted_by_user_id' => User::factory(),
            'decided_by_user_id' => null,
            'decided_at' => null,
        ]);
    }

    public function underReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UnderReviewState::class,
            'submitted_by_user_id' => User::factory(),
            'decided_by_user_id' => null,
            'decided_at' => null,
        ]);
    }

    public function pendingDocuments(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PendingDocumentsState::class,
            'submitted_by_user_id' => User::factory(),
            'decided_by_user_id' => null,
            'decided_at' => null,
            'metadata' => [
                'documents_requested' => [
                    'proof_of_income',
                    'proof_of_residence',
                ],
                'requested_at' => now()->toISOString(),
            ],
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApprovedState::class,
            'submitted_by_user_id' => User::factory(),
            'decided_by_user_id' => User::factory(),
            'decided_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function approvedPrelim(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApprovedPrelimState::class,
            'submitted_by_user_id' => User::factory(),
            'decided_by_user_id' => User::factory(),
            'decided_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'metadata' => [
                'emergency_approval' => true,
                'requires_confirmation' => true,
            ],
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RejectedState::class,
            'submitted_by_user_id' => User::factory(),
            'decided_by_user_id' => User::factory(),
            'decided_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'reason' => fake()->sentence(12),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CancelledState::class,
            'submitted_by_user_id' => User::factory(),
            'decided_by_user_id' => User::factory(),
            'decided_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'reason' => fake()->sentence(10),
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RevokedState::class,
            'submitted_by_user_id' => User::factory(),
            'decided_by_user_id' => User::factory(),
            'decided_at' => fake()->dateTimeBetween('-60 days', '-1 day'),
            'reason' => fake()->sentence(15),
            'metadata' => [
                'revoked_at' => now()->toISOString(),
                'original_approval_date' => fake()->dateTimeBetween('-90 days', '-60 days')->format('Y-m-d'),
            ],
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExpiredState::class,
            'submitted_by_user_id' => User::factory(),
            'decided_by_user_id' => null,
            'decided_at' => null,
            'metadata' => [
                'expired_at' => now()->toISOString(),
                'days_pending' => 30,
            ],
        ]);
    }
}
