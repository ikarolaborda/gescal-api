<?php

namespace App\Actions\Reports;

use App\Models\User;
use Illuminate\Support\Collection;

class MaskPIIFieldsAction
{
    private const MASK_VALUE = '***';

    private array $personFields = ['email', 'primary_phone', 'secondary_phone'];
    private array $addressFields = ['street', 'number', 'complement', 'neighborhood', 'zip_code', 'reference_point'];
    private array $documentFields = ['number', 'issuing_body'];

    public function __construct()
    {
        $this->personFields = config('reports.pii_masking.person_fields', $this->personFields);
        $this->addressFields = config('reports.pii_masking.address_fields', $this->addressFields);
        $this->documentFields = config('reports.pii_masking.document_fields', $this->documentFields);
    }

    public function execute(Collection $data, User $user, string $entityType): Collection
    {
        // Administrators see all data unmasked
        if ($user->isAdmin()) {
            return $data;
        }

        // Coordinators get PII masked
        if ($user->isCoordinator()) {
            return $data->map(function ($record) use ($entityType) {
                return $this->maskRecord($record, $entityType);
            });
        }

        // Default: mask everything for other roles
        return $data->map(function ($record) use ($entityType) {
            return $this->maskRecord($record, $entityType);
        });
    }

    private function maskRecord(array $record, string $entityType): array
    {
        $maskedRecord = $record;

        // Mask person fields
        foreach ($this->personFields as $field) {
            if (isset($maskedRecord[$field])) {
                $maskedRecord[$field] = self::MASK_VALUE;
            }
        }

        // Mask address fields if present in the record
        foreach ($this->addressFields as $field) {
            if (isset($maskedRecord[$field])) {
                $maskedRecord[$field] = self::MASK_VALUE;
            }
        }

        // Mask document fields if present in the record
        foreach ($this->documentFields as $field) {
            if (isset($maskedRecord[$field])) {
                $maskedRecord[$field] = self::MASK_VALUE;
            }
        }

        // Handle nested relationships
        if (isset($maskedRecord['addresses']) && is_array($maskedRecord['addresses'])) {
            $maskedRecord['addresses'] = array_map(function ($address) {
                return $this->maskAddressFields($address);
            }, $maskedRecord['addresses']);
        }

        if (isset($maskedRecord['documents']) && is_array($maskedRecord['documents'])) {
            $maskedRecord['documents'] = array_map(function ($document) {
                return $this->maskDocumentFields($document);
            }, $maskedRecord['documents']);
        }

        return $maskedRecord;
    }

    private function maskAddressFields(array $address): array
    {
        foreach ($this->addressFields as $field) {
            if (isset($address[$field])) {
                $address[$field] = self::MASK_VALUE;
            }
        }

        return $address;
    }

    private function maskDocumentFields(array $document): array
    {
        foreach ($this->documentFields as $field) {
            if (isset($document[$field])) {
                $document[$field] = self::MASK_VALUE;
            }
        }

        return $document;
    }
}
