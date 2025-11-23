<?php

namespace Tests\Feature\Api\V1\Bulk;

use App\Models\Family;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class BulkExportTest extends TestCase
{
    use JsonApiTestHelpers, RefreshDatabase;

    protected User $coordinator;
    protected User $socialWorker;
    protected string $coordinatorToken;
    protected string $socialWorkerToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $coordinatorRole = Role::factory()->create(['name' => 'coordinator', 'slug' => 'coordinator']);
        $socialWorkerRole = Role::factory()->create(['name' => 'social_worker', 'slug' => 'social_worker']);

        // Create users
        $this->coordinator = User::factory()->create();
        $this->coordinator->roles()->attach($coordinatorRole);
        $this->coordinatorToken = auth('api')->login($this->coordinator);

        $this->socialWorker = User::factory()->create();
        $this->socialWorker->roles()->attach($socialWorkerRole);
        $this->socialWorkerToken = auth('api')->login($this->socialWorker);
    }

    public function test_coordinator_can_bulk_export_people(): void
    {
        // Arrange
        Person::factory()->count(3)->create();

        $exportData = [
            'types' => ['persons'],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/bulk/export', $exportData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes',
                    ],
                ],
                'included',
                'meta' => [
                    'total_records',
                    'export_timestamp',
                    'max_records_limit',
                    'limit_reached',
                ],
            ])
            ->assertJson([
                'meta' => [
                    'total_records' => 3,
                    'limit_reached' => false,
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_social_worker_cannot_bulk_export(): void
    {
        // Arrange
        Person::factory()->count(3)->create();

        $exportData = [
            'types' => ['persons'],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/bulk/export', $exportData, [
            'Authorization' => 'Bearer ' . $this->socialWorkerToken,

        ]);

        // Assert
        $response->assertForbidden();
    }

    public function test_bulk_export_supports_multiple_resource_types(): void
    {
        // Arrange
        Person::factory()->count(2)->create();
        Family::factory()->count(3)->create();

        $exportData = [
            'types' => ['persons', 'families'],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/bulk/export', $exportData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert
        $response->assertOk()
            ->assertJson([
                'meta' => [
                    'total_records' => 5,
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(5, $data);

        $peopleCount = collect($data)->where('type', 'persons')->count();
        $familiesCount = collect($data)->where('type', 'families')->count();

        $this->assertEquals(2, $peopleCount);
        $this->assertEquals(3, $familiesCount);
    }

    public function test_bulk_export_filters_by_created_since(): void
    {
        // Arrange
        $oldPerson = Person::factory()->create(['created_at' => now()->subDays(10)]);
        $newPerson = Person::factory()->create(['created_at' => now()->subDay()]);

        $exportData = [
            'types' => ['persons'],
            'filters' => [
                'created_since' => now()->subDays(2)->toDateString(),
            ],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/bulk/export', $exportData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert
        $response->assertOk()
            ->assertJson([
                'meta' => [
                    'total_records' => 1,
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals((string) $newPerson->id, $data[0]['id']);
    }

    public function test_bulk_export_respects_max_records_limit(): void
    {
        // Arrange - Create more than MAX_TOTAL_RECORDS
        // Note: MAX_TOTAL_RECORDS is 10000, so we'll test with a smaller number
        Person::factory()->count(15)->create();

        $exportData = [
            'types' => ['persons'],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/bulk/export', $exportData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert
        $response->assertOk();

        $meta = $response->json('meta');
        $this->assertLessThanOrEqual(10000, $meta['total_records']);
        $this->assertEquals(15, $meta['total_records']); // Should be all records since < 10000
        $this->assertFalse($meta['limit_reached']);
    }

    public function test_bulk_export_validates_required_types(): void
    {
        // Arrange
        $exportData = [
            // Missing 'types' field
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/bulk/export', $exportData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('types');
    }

    public function test_bulk_export_validates_invalid_resource_types(): void
    {
        // Arrange
        $exportData = [
            'types' => ['invalid_type'],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/bulk/export', $exportData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('types.0');
    }

    public function test_bulk_export_includes_export_timestamp(): void
    {
        // Arrange
        Person::factory()->create();

        $exportData = [
            'types' => ['persons'],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/bulk/export', $exportData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert
        $response->assertOk();
        $meta = $response->json('meta');

        $this->assertArrayHasKey('export_timestamp', $meta);
        $this->assertNotEmpty($meta['export_timestamp']);

        // Verify timestamp is in ISO 8601 format
        $timestamp = $meta['export_timestamp'];
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $timestamp);
    }

    public function test_bulk_export_returns_json_api_compliant_data(): void
    {
        // Arrange
        $person = Person::factory()->create(['full_name' => 'Test Person']);

        $exportData = [
            'types' => ['persons'],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/bulk/export', $exportData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert
        $response->assertOk();
        $data = $response->json('data.0');

        $this->assertEquals('persons', $data['type']);
        $this->assertEquals((string) $person->id, $data['id']);
        $this->assertArrayHasKey('attributes', $data);
        $this->assertEquals('Test Person', $data['attributes']['full_name']);
    }

    public function test_bulk_export_returns_portuguese_error_messages(): void
    {
        // Arrange
        $exportData = [
            'types' => [],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/bulk/export', $exportData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert
        $response->assertStatus(422);
        $responseData = $response->json();
        $this->assertStringContainsString('recurso', $responseData['errors']['types'][0]);
    }

    public function test_unauthenticated_user_cannot_bulk_export(): void
    {
        // Arrange
        $exportData = [
            'types' => ['persons'],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/bulk/export', $exportData, [

        ]);

        // Assert
        $response->assertUnauthorized();
    }
}
