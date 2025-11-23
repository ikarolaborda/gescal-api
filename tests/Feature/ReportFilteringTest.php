<?php

namespace Tests\Feature;

use App\Models\Benefit;
use App\Models\Family;
use App\Models\Person;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class ReportFilteringTest extends TestCase
{
    use JsonApiTestHelpers;
    use RefreshDatabase;

    protected User $coordinator;

    protected string $coordinatorToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create coordinator role and user
        $coordinatorRole = Role::factory()->create(['name' => 'coordinator', 'slug' => 'coordinator']);

        $this->coordinator = User::factory()->create();
        $this->coordinator->roles()->attach($coordinatorRole);
        $this->coordinatorToken = auth('api')->login($this->coordinator);

        Queue::fake();
    }

    public function test_can_filter_report_by_created_at_date_range(): void
    {
        // Arrange - Create persons with different creation dates
        Person::factory()->create(['created_at' => '2025-01-15']);
        Person::factory()->create(['created_at' => '2025-02-15']);
        Person::factory()->create(['created_at' => '2025-03-15']);
        Person::factory()->create(['created_at' => '2025-04-15']);

        $requestData = [
            'entity_type' => 'persons',
            'format' => 'pdf',
            'parameters' => [
                'filters' => [
                    'created_at' => [
                        'from' => '2025-02-01',
                        'to' => '2025-03-31',
                    ],
                ],
            ],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/reports', $requestData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,
        ]);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'entity_type',
                    'format',
                    'status',
                    'parameters',
                ],
            ]);

        $this->assertDatabaseHas('reports', [
            'entity_type' => 'persons',
            'format' => 'pdf',
            'user_id' => $this->coordinator->id,
        ]);
    }

    public function test_can_filter_report_by_updated_at_date_range(): void
    {
        // Arrange
        Person::factory()->create([
            'created_at' => '2025-01-01',
            'updated_at' => '2025-01-15',
        ]);
        Person::factory()->create([
            'created_at' => '2025-01-01',
            'updated_at' => '2025-02-15',
        ]);

        $requestData = [
            'entity_type' => 'persons',
            'format' => 'excel',
            'parameters' => [
                'filters' => [
                    'updated_at' => [
                        'from' => '2025-02-01',
                        'to' => '2025-02-28',
                    ],
                ],
            ],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/reports', $requestData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,
        ]);

        // Assert
        $response->assertStatus(201);

        $report = Report::latest()->first();
        $this->assertEquals('2025-02-01', $report->parameters['filters']['updated_at']['from']);
        $this->assertEquals('2025-02-28', $report->parameters['filters']['updated_at']['to']);
    }

    public function test_can_filter_benefits_by_is_active(): void
    {
        // Arrange
        Benefit::factory()->count(5)->create(['is_active' => true]);
        Benefit::factory()->count(3)->create(['is_active' => false]);

        $requestData = [
            'entity_type' => 'benefits',
            'format' => 'csv',
            'parameters' => [
                'filters' => [
                    'is_active' => true,
                ],
            ],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/reports', $requestData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,
        ]);

        // Assert
        $response->assertStatus(201);

        $report = Report::latest()->first();
        $this->assertTrue($report->parameters['filters']['is_active']);
    }

    public function test_can_filter_persons_by_family_id(): void
    {
        // Arrange
        $family = Family::factory()->create();
        Person::factory()->count(3)->create(['family_id' => $family->id]);
        Person::factory()->count(2)->create(['family_id' => null]);

        $requestData = [
            'entity_type' => 'persons',
            'format' => 'json',
            'parameters' => [
                'filters' => [
                    'family_id' => $family->id,
                ],
            ],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/reports', $requestData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,
        ]);

        // Assert
        $response->assertStatus(201);

        $report = Report::latest()->first();
        $this->assertEquals($family->id, $report->parameters['filters']['family_id']);
    }

    public function test_can_filter_with_search_query(): void
    {
        // Arrange
        $requestData = [
            'entity_type' => 'persons',
            'format' => 'pdf',
            'parameters' => [
                'filters' => [
                    'search' => 'João Silva',
                ],
            ],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/reports', $requestData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,
        ]);

        // Assert
        $response->assertStatus(201);

        $report = Report::latest()->first();
        $this->assertEquals('João Silva', $report->parameters['filters']['search']);
    }

    public function test_can_combine_multiple_filters(): void
    {
        // Arrange
        $family = Family::factory()->create();

        $requestData = [
            'entity_type' => 'persons',
            'format' => 'excel',
            'parameters' => [
                'filters' => [
                    'created_at' => [
                        'from' => '2025-01-01',
                        'to' => '2025-12-31',
                    ],
                    'family_id' => $family->id,
                    'search' => 'Silva',
                ],
            ],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/reports', $requestData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,
        ]);

        // Assert
        $response->assertStatus(201);

        $report = Report::latest()->first();
        $filters = $report->parameters['filters'];

        $this->assertArrayHasKey('created_at', $filters);
        $this->assertArrayHasKey('family_id', $filters);
        $this->assertArrayHasKey('search', $filters);
        $this->assertEquals($family->id, $filters['family_id']);
        $this->assertEquals('Silva', $filters['search']);
    }

    public function test_validates_date_range_end_after_start(): void
    {
        // Arrange
        $requestData = [
            'entity_type' => 'persons',
            'format' => 'pdf',
            'parameters' => [
                'filters' => [
                    'created_at' => [
                        'from' => '2025-12-31',
                        'to' => '2025-01-01',
                    ],
                ],
            ],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/reports', $requestData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parameters.filters.created_at.to']);
    }

    public function test_validates_family_id_exists(): void
    {
        // Arrange
        $requestData = [
            'entity_type' => 'persons',
            'format' => 'pdf',
            'parameters' => [
                'filters' => [
                    'family_id' => 99999,
                ],
            ],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/reports', $requestData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parameters.filters.family_id']);
    }

    public function test_validates_is_active_is_boolean(): void
    {
        // Arrange
        $requestData = [
            'entity_type' => 'benefits',
            'format' => 'pdf',
            'parameters' => [
                'filters' => [
                    'is_active' => 'not-a-boolean',
                ],
            ],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/reports', $requestData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parameters.filters.is_active']);
    }

    public function test_validates_search_max_length(): void
    {
        // Arrange
        $requestData = [
            'entity_type' => 'persons',
            'format' => 'pdf',
            'parameters' => [
                'filters' => [
                    'search' => str_repeat('a', 256),
                ],
            ],
        ];

        // Act
        $response = $this->postJsonApi('/api/v1/reports', $requestData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parameters.filters.search']);
    }
}
