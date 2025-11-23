<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear all rate limiters for registration endpoint
        RateLimiter::clear('registration:127.0.0.1');
        RateLimiter::clear('registration:localhost');

        // Also clear any rate limiter that might have been created
        foreach (['127.0.0.1', 'localhost', '::1'] as $ip) {
            RateLimiter::clear("registration:{$ip}");
        }
    }

    protected function tearDown(): void
    {
        // Clear rate limiters after each test
        RateLimiter::clear('registration:127.0.0.1');

        parent::tearDown();
    }

    /** @test */
    public function first_user_creates_organization_and_receives_super_admin_role(): void
    {
        Mail::fake();

        // Generate a valid CNPJ
        $cnpj = $this->generateValidCnpj();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'João Silva',
            'email' => 'joao@prefeitura.sp.gov.br',
            'password' => 'SecureP@ssw0rd!',
            'password_confirmation' => 'SecureP@ssw0rd!',
            'organization_cnpj' => $cnpj,
            'organization_name' => 'Prefeitura Municipal de São Paulo',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => ['name', 'email', 'status'],
                    'relationships',
                ],
                'meta' => ['token', 'message'],
            ])
            ->assertJson([
                'data' => [
                    'attributes' => [
                        'status' => 'active',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('organizations', [
            'cnpj' => $cnpj,
            'name' => 'Prefeitura Municipal de São Paulo',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'joao@prefeitura.sp.gov.br',
            'status' => 'active',
        ]);

        $user = User::where('email', 'joao@prefeitura.sp.gov.br')->first();
        $this->assertTrue($user->hasUserRole('ROLE_ORGANIZATION_SUPER_ADMIN'));
    }

    /** @test */
    public function first_user_receives_jwt_token_in_response(): void
    {
        Mail::fake();

        $cnpj = $this->generateValidCnpj();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Maria Santos',
            'email' => 'maria@prefeitura.rj.gov.br',
            'password' => 'SecureP@ssw0rd!',
            'password_confirmation' => 'SecureP@ssw0rd!',
            'organization_cnpj' => $cnpj,
            'organization_name' => 'Prefeitura Municipal do Rio de Janeiro',
        ]);

        $response->assertStatus(201);

        $responseData = $response->json();
        $this->assertArrayHasKey('token', $responseData['meta']);
        $this->assertNotEmpty($responseData['meta']['token']);
    }

    /** @test */
    public function organization_record_created_with_provided_name_and_cnpj(): void
    {
        Mail::fake();

        $cnpj = $this->generateValidCnpj();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Pedro Costa',
            'email' => 'pedro@prefeitura.mg.gov.br',
            'password' => 'SecureP@ssw0rd!',
            'password_confirmation' => 'SecureP@ssw0rd!',
            'organization_cnpj' => $cnpj,
            'organization_name' => 'Prefeitura Municipal de Belo Horizonte',
        ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Prefeitura Municipal de Belo Horizonte',
            'cnpj' => $cnpj,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function validation_fails_if_cnpj_invalid_format(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Ana Paula',
            'email' => 'ana@prefeitura.gov.br',
            'password' => 'SecureP@ssw0rd!',
            'password_confirmation' => 'SecureP@ssw0rd!',
            'organization_cnpj' => '12345678901234', // Invalid CNPJ checksum
            'organization_name' => 'Prefeitura Test',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['organization_cnpj']);
    }

    /** @test */
    public function validation_fails_if_organization_name_missing_for_first_user(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Carlos Lima',
            'email' => 'carlos@prefeitura.gov.br',
            'password' => 'SecureP@ssw0rd!',
            'password_confirmation' => 'SecureP@ssw0rd!',
            'organization_cnpj' => '12345678000199',
            // organization_name missing
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['organization_name']);
    }

    /** @test */
    public function subsequent_user_registration_creates_pending_user(): void
    {
        // Create organization first
        $organization = Organization::factory()->create();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Fernanda Oliveira',
            'email' => 'fernanda@prefeitura.gov.br',
            'password' => 'SecureP@ssw0rd!',
            'password_confirmation' => 'SecureP@ssw0rd!',
            'organization_cnpj' => $organization->cnpj,
            // organization_name not required for existing org
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'attributes' => [
                        'status' => 'pending',
                    ],
                ],
            ]);

        $user = User::where('email', 'fernanda@prefeitura.gov.br')->first();
        $this->assertEquals(UserStatus::Pending, $user->status);
        $this->assertNotNull($user->cancellation_token);
        $this->assertNotNull($user->cancellation_token_expires_at);
        $this->assertFalse($user->hasUserRole('ROLE_ORGANIZATION_SUPER_ADMIN'));
    }

    /** @test */
    public function concurrent_first_user_registrations_create_one_organization(): void
    {
        Mail::fake();

        $cnpj = $this->generateValidCnpj();
        $registrationData1 = [
            'name' => 'User One',
            'email' => 'user1@prefeitura.gov.br',
            'password' => 'SecureP@ssw0rd!',
            'password_confirmation' => 'SecureP@ssw0rd!',
            'organization_cnpj' => $cnpj,
            'organization_name' => 'Prefeitura Municipal Test',
        ];

        $registrationData2 = [
            'name' => 'User Two',
            'email' => 'user2@prefeitura.gov.br',
            'password' => 'SecureP@ssw0rd!',
            'password_confirmation' => 'SecureP@ssw0rd!',
            'organization_cnpj' => $cnpj,
            'organization_name' => 'Prefeitura Municipal Test',
        ];

        // Simulate concurrent requests (in reality, this would be async)
        $response1 = $this->postJson('/api/v1/auth/register', $registrationData1);
        $response2 = $this->postJson('/api/v1/auth/register', $registrationData2);

        // Both requests should succeed
        $this->assertTrue(in_array($response1->status(), [201, 201]));
        $this->assertTrue(in_array($response2->status(), [201, 201]));

        // Only one organization should exist
        $this->assertEquals(1, Organization::where('cnpj', $cnpj)->count());

        // Two users should exist
        $this->assertEquals(2, User::whereHas('organization', function ($query) use ($cnpj) {
            $query->where('cnpj', $cnpj);
        })->count());

        // One active super admin, one pending
        $this->assertEquals(
            1,
            User::where('status', 'active')
                ->whereHas('organization', function ($query) use ($cnpj) {
                    $query->where('cnpj', $cnpj);
                })->count()
        );

        $this->assertEquals(
            1,
            User::where('status', 'pending')
                ->whereHas('organization', function ($query) use ($cnpj) {
                    $query->where('cnpj', $cnpj);
                })->count()
        );
    }

    /** @test */
    public function password_must_meet_strength_requirements(): void
    {
        $cnpj = $this->generateValidCnpj();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@prefeitura.gov.br',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'organization_cnpj' => $cnpj,
            'organization_name' => 'Test Org',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'existing@prefeitura.gov.br']);

        $cnpj = $this->generateValidCnpj();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'existing@prefeitura.gov.br',
            'password' => 'SecureP@ssw0rd!',
            'password_confirmation' => 'SecureP@ssw0rd!',
            'organization_cnpj' => $cnpj,
            'organization_name' => 'Test Org',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function rate_limiting_prevents_excessive_registration_attempts(): void
    {
        // Clear rate limiters to ensure clean slate for this test
        RateLimiter::clear('registration:127.0.0.1');

        // Attempt to register 6 times (limit is 5 per hour)
        for ($i = 0; $i < 6; $i++) {
            $cnpj = $this->generateValidCnpj();

            $response = $this->postJson('/api/v1/auth/register', [
                'name' => "User $i",
                'email' => "user$i@prefeitura.gov.br",
                'password' => 'SecureP@ssw0rd!',
                'password_confirmation' => 'SecureP@ssw0rd!',
                'organization_cnpj' => $cnpj,
                'organization_name' => 'Test Org',
            ]);

            if ($i < 5) {
                $this->assertNotEquals(429, $response->status());
            } else {
                $response->assertStatus(429)
                    ->assertJsonFragment(['status' => '429']);
            }
        }
    }

    /**
     * Generate a valid Brazilian CNPJ for testing.
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
}
