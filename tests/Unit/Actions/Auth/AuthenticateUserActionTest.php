<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthenticateUserActionTest extends TestCase
{
    use RefreshDatabase;

    private AuthenticateUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new AuthenticateUserAction;
    }

    public function test_execute_returns_token_for_valid_credentials(): void
    {
        // Arrange: Create a user
        $user = User::factory()->socialWorker()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Act: Authenticate
        $result = $this->action->execute('test@example.com', 'password123');

        // Assert: Check result structure
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('token_type', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals('bearer', $result['token_type']);
        $this->assertEquals($user->id, $result['user']->id);
        $this->assertNotEmpty($result['token']);
    }

    public function test_execute_throws_validation_exception_for_invalid_password(): void
    {
        // Arrange: Create a user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        // Assert: Expect validation exception
        $this->expectException(ValidationException::class);

        // Act: Authenticate with wrong password
        $this->action->execute('test@example.com', 'wrong-password');
    }

    public function test_execute_throws_validation_exception_for_nonexistent_user(): void
    {
        // Assert: Expect validation exception
        $this->expectException(ValidationException::class);

        // Act: Authenticate with non-existent email
        $this->action->execute('nonexistent@example.com', 'password123');
    }

    public function test_execute_includes_user_roles_in_result(): void
    {
        // Arrange: Ensure roles exist first
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        // Create a user with admin role
        $user = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Act: Authenticate
        $result = $this->action->execute('admin@example.com', 'password123');

        // Assert: Check roles are loaded
        $this->assertTrue($result['user']->relationLoaded('roles'));
        $this->assertNotEmpty($result['user']->roles);
    }
}
