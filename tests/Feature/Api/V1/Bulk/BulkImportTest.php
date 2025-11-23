<?php

namespace Tests\Feature\Api\V1\Bulk;

use App\Models\Address;
use App\Models\FederationUnit;
use App\Models\MaritalStatus;
use App\Models\Person;
use App\Models\RaceEthnicity;
use App\Models\Role;
use App\Models\SchoolingLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JsonApiTestHelpers;

class BulkImportTest extends TestCase
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

        // Create reference data
        FederationUnit::factory()->create(['federation_unit' => 'SP']);
        RaceEthnicity::factory()->create(['race_color' => 'branca']);
        MaritalStatus::factory()->create(['marital_status' => 'solteiro']);
        SchoolingLevel::factory()->create(['schooling_level' => 'medio_completo']);
    }

    public function test_coordinator_can_bulk_import_people(): void
    {
        // Arrange
        $importData = [
            'people' => [
                [
                    'full_name' => 'João Silva',
                    'sex' => 'Masculino',
                    'birth_date' => '1990-01-01',
                    'nationality' => 'brasileiro',
                    'natural_federation_unit_id' => FederationUnit::first()->id,
                    'race_ethnicity_id' => RaceEthnicity::first()->id,
                    'marital_status_id' => MaritalStatus::first()->id,
                    'schooling_level_id' => SchoolingLevel::first()->id,
                ],
                [
                    'full_name' => 'Maria Santos',
                    'sex' => 'Feminino',
                    'birth_date' => '1985-05-15',
                    'nationality' => 'brasileiro',
                    'natural_federation_unit_id' => FederationUnit::first()->id,
                    'race_ethnicity_id' => RaceEthnicity::first()->id,
                    'marital_status_id' => MaritalStatus::first()->id,
                    'schooling_level_id' => SchoolingLevel::first()->id,
                ],
            ],
        ];

        // Act
        $response = $this->post('/api/v1/bulk/import', $importData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'type' => 'bulk-import-results',
                    'attributes' => [
                        'success' => true,
                        'results' => [
                            'people' => [
                                'created' => 2,
                                'failed' => 0,
                                'errors' => [],
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseCount('persons', 2);
        $this->assertDatabaseHas('persons', ['full_name' => 'João Silva']);
        $this->assertDatabaseHas('persons', ['full_name' => 'Maria Santos']);
    }

    public function test_social_worker_cannot_bulk_import(): void
    {
        // Arrange
        $importData = [
            'people' => [
                [
                    'full_name' => 'Test Person',
                    'sex' => 'Masculino',
                    'birth_date' => '1990-01-01',
                    'nationality' => 'brasileiro',
                ],
            ],
        ];

        // Act
        $response = $this->post('/api/v1/bulk/import', $importData, [
            'Authorization' => 'Bearer ' . $this->socialWorkerToken,

        ]);

        // Assert
        $response->assertForbidden();
    }

    public function test_bulk_import_validates_max_records_per_type(): void
    {
        // Arrange - Create 1001 records to exceed limit
        $people = [];
        for ($i = 0; $i < 1001; $i++) {
            $people[] = [
                'full_name' => "Person {$i}",
                'sex' => 'Masculino',
                'birth_date' => '1990-01-01',
                'nationality' => 'brasileiro',
            ];
        }

        $importData = ['people' => $people];

        // Act
        $response = $this->post('/api/v1/bulk/import', $importData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('people');
    }

    public function test_bulk_import_rolls_back_on_validation_error(): void
    {
        // Arrange
        $importData = [
            'people' => [
                [
                    'full_name' => 'Valid Person',
                    'sex' => 'Masculino',
                    'birth_date' => '1990-01-01',
                    'nationality' => 'brasileiro',
                    'natural_federation_unit_id' => FederationUnit::first()->id,
                    'race_ethnicity_id' => RaceEthnicity::first()->id,
                    'marital_status_id' => MaritalStatus::first()->id,
                    'schooling_level_id' => SchoolingLevel::first()->id,
                ],
                [
                    'full_name' => 'Invalid Person',
                    'sex' => 'Invalid', // Invalid sex value
                    'birth_date' => '1990-01-01',
                    'nationality' => 'brasileiro',
                ],
            ],
        ];

        // Act
        $response = $this->post('/api/v1/bulk/import', $importData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert - Transaction should roll back all records
        $response->assertStatus(422);
        $this->assertDatabaseCount('persons', 0);
    }

    public function test_coordinator_can_bulk_import_families(): void
    {
        // Arrange
        $person1 = Person::factory()->create();
        $person2 = Person::factory()->create();
        $address1 = Address::factory()->create();
        $address2 = Address::factory()->create();

        $importData = [
            'families' => [
                [
                    'responsible_person_id' => $person1->id,
                    'address_id' => $address1->id,
                    'origin_city' => 'São Paulo',
                    'origin_federation_unit_id' => FederationUnit::first()->id,
                    'family_income_value' => 2500.00,
                ],
                [
                    'responsible_person_id' => $person2->id,
                    'address_id' => $address2->id,
                    'origin_city' => 'Rio de Janeiro',
                    'origin_federation_unit_id' => FederationUnit::first()->id,
                    'family_income_value' => 3000.00,
                ],
            ],
        ];

        // Act
        $response = $this->post('/api/v1/bulk/import', $importData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'attributes' => [
                        'success' => true,
                        'results' => [
                            'families' => [
                                'created' => 2,
                                'failed' => 0,
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseCount('families', 2);
    }

    public function test_bulk_import_returns_portuguese_error_messages(): void
    {
        // Arrange
        $importData = [
            'people' => array_fill(0, 1001, [
                'full_name' => 'Test',
                'sex' => 'Masculino',
                'birth_date' => '1990-01-01',
            ]),
        ];

        // Act
        $response = $this->post('/api/v1/bulk/import', $importData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert
        $response->assertStatus(422);
        $responseData = $response->json();
        $this->assertStringContainsString('Máximo', $responseData['errors']['people'][0]);
    }

    public function test_bulk_import_handles_mixed_resource_types(): void
    {
        // Arrange
        $person = Person::factory()->create();
        $address = Address::factory()->create();

        $importData = [
            'people' => [
                [
                    'full_name' => 'New Person',
                    'sex' => 'Masculino',
                    'birth_date' => '1990-01-01',
                    'nationality' => 'brasileiro',
                    'natural_federation_unit_id' => FederationUnit::first()->id,
                    'race_ethnicity_id' => RaceEthnicity::first()->id,
                    'marital_status_id' => MaritalStatus::first()->id,
                    'schooling_level_id' => SchoolingLevel::first()->id,
                ],
            ],
            'families' => [
                [
                    'responsible_person_id' => $person->id,
                    'address_id' => $address->id,
                    'origin_city' => 'Test City',
                    'origin_federation_unit_id' => FederationUnit::first()->id,
                    'family_income_value' => 1500.00,
                ],
            ],
        ];

        // Act
        $response = $this->post('/api/v1/bulk/import', $importData, [
            'Authorization' => 'Bearer ' . $this->coordinatorToken,

        ]);

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'attributes' => [
                        'success' => true,
                        'results' => [
                            'people' => ['created' => 1],
                            'families' => ['created' => 1],
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseCount('persons', 2); // 1 existing + 1 new
        $this->assertDatabaseCount('families', 1);
    }

    public function test_unauthenticated_user_cannot_bulk_import(): void
    {
        // Arrange
        $importData = [
            'people' => [
                [
                    'full_name' => 'Test Person',
                    'sex' => 'Masculino',
                    'birth_date' => '1990-01-01',
                ],
            ],
        ];

        // Act
        $response = $this->post('/api/v1/bulk/import', $importData, [

        ]);

        // Assert
        $response->assertUnauthorized();
    }
}
