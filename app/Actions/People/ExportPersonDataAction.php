<?php

namespace App\Actions\People;

use App\Models\Person;

class ExportPersonDataAction
{
    /**
     * Export all personal data for LGPD Article 18 compliance (data portability).
     *
     * Returns a comprehensive JSON structure with all person data and related records.
     */
    public function execute(Person $person): array
    {
        $person->load([
            'naturalFederationUnit',
            'raceEthnicity',
            'maritalStatus',
            'schoolingLevel',
            'documents',
            'families.address.stateId',
            'families.originFederationUnit',
            'benefits.benefitProgram',
        ]);

        return [
            'data' => [
                'type' => 'person-data-export',
                'id' => (string) $person->id,
                'attributes' => [
                    'personal_information' => [
                        'full_name' => $person->full_name,
                        'sex' => $person->sex,
                        'birth_date' => $person->birth_date?->format('Y-m-d'),
                        'filiation_text' => $person->filiation_text,
                        'nationality' => $person->nationality,
                        'natural_city' => $person->natural_city,
                        'natural_federation_unit' => $person->naturalFederationUnit?->federation_unit,
                        'race_ethnicity' => $person->raceEthnicity?->race_color,
                        'marital_status' => $person->maritalStatus?->marital_status,
                        'schooling_level' => $person->schoolingLevel?->schooling_level,
                    ],

                    'contact_information' => [
                        'primary_phone' => $person->primary_phone,
                        'secondary_phone' => $person->secondary_phone,
                        'email' => $person->email,
                    ],

                    'documents' => $person->documents->map(fn ($doc) => [
                        'type' => $doc->documentType?->document_type,
                        'number' => $doc->document_number,
                        'issuing_body' => $doc->issuing_body,
                        'issue_date' => $doc->issue_date?->format('Y-m-d'),
                        'created_at' => $doc->created_at?->toIso8601String(),
                    ])->toArray(),

                    'families' => $person->families->map(fn ($family) => [
                        'id' => $family->id,
                        'origin_city' => $family->origin_city,
                        'origin_federation_unit' => $family->originFederationUnit?->federation_unit,
                        'family_income_bracket' => $family->family_income_bracket,
                        'family_income_value' => $family->family_income_value,
                        'address' => $family->address ? [
                            'street' => $family->address->street,
                            'number' => $family->address->number,
                            'complement' => $family->address->complement,
                            'neighborhood' => $family->address->neighborhood,
                            'city' => $family->address->city,
                            'state' => $family->address->stateId?->federation_unit,
                            'zip_code' => $family->address->zip_code,
                        ] : null,
                        'created_at' => $family->created_at?->toIso8601String(),
                    ])->toArray(),

                    'benefits' => $person->benefits->map(fn ($benefit) => [
                        'program' => $benefit->benefitProgram->name,
                        'value' => $benefit->value,
                        'is_active' => $benefit->is_active,
                        'started_at' => $benefit->started_at?->format('Y-m-d'),
                        'ended_at' => $benefit->ended_at?->format('Y-m-d'),
                        'created_at' => $benefit->created_at?->toIso8601String(),
                    ])->toArray(),

                    'metadata' => [
                        'created_at' => $person->created_at?->toIso8601String(),
                        'updated_at' => $person->updated_at?->toIso8601String(),
                        'export_date' => now()->toIso8601String(),
                        'export_format_version' => '1.0',
                    ],
                ],
            ],
            'meta' => [
                'lgpd_compliance' => [
                    'article' => 'Article 18, Brazilian General Data Protection Law (LGPD)',
                    'right' => 'Right to data portability',
                    'description' => 'Complete export of all personal data held by the system',
                ],
            ],
        ];
    }
}
