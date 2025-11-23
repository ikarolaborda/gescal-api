<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class ApiTestCase extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    protected User $user;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and reference data
        $this->seed(RoleSeeder::class);
        $this->seed(ReferenceDataSeeder::class);
    }

    protected function actingAsSocialWorker(): static
    {
        $this->user = User::factory()->create();
        $this->user->roles()->attach(Role::where('slug', 'social_worker')->first());
        $this->token = JWTAuth::fromUser($this->user);

        return $this;
    }

    protected function actingAsCoordinator(): static
    {
        $this->user = User::factory()->create();
        $this->user->roles()->attach(Role::where('slug', 'coordinator')->first());
        $this->token = JWTAuth::fromUser($this->user);

        return $this;
    }

    protected function actingAsAdmin(): static
    {
        $this->user = User::factory()->create();
        $this->user->roles()->attach(Role::where('slug', 'admin')->first());
        $this->token = JWTAuth::fromUser($this->user);

        return $this;
    }

    protected function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
        ];
    }
}
