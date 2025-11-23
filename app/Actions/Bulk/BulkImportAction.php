<?php

namespace App\Actions\Bulk;

use App\Actions\Benefits\CreateBenefitAction;
use App\Actions\Cases\CreateCaseAction;
use App\Actions\Families\CreateFamilyAction;
use App\Actions\People\CreatePersonAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BulkImportAction
{
    public const MAX_RECORDS_PER_TYPE = 1000;

    public function __construct(
        private readonly CreatePersonAction $createPerson,
        private readonly CreateFamilyAction $createFamily,
        private readonly CreateCaseAction $createCase,
        private readonly CreateBenefitAction $createBenefit
    ) {}

    /**
     * Import multiple resources in batch with transactional processing.
     *
     * @param  array<string, array>  $data  Array keyed by resource type (people, families, cases, benefits)
     * @return array<string, array> Results summary for each resource type
     *
     * @throws ValidationException
     */
    public function execute(array $data): array
    {
        $this->validate($data);

        $results = [];

        // Process each resource type in a separate transaction
        foreach ($data as $resourceType => $records) {
            $results[$resourceType] = $this->importResourceType($resourceType, $records);
        }

        return $results;
    }

    /**
     * Validate the bulk import data structure and limits.
     *
     * @throws ValidationException
     */
    protected function validate(array $data): void
    {
        $validator = Validator::make($data, [
            'people' => 'sometimes|array|max:' . self::MAX_RECORDS_PER_TYPE,
            'people.*' => 'array',
            'families' => 'sometimes|array|max:' . self::MAX_RECORDS_PER_TYPE,
            'families.*' => 'array',
            'cases' => 'sometimes|array|max:' . self::MAX_RECORDS_PER_TYPE,
            'cases.*' => 'array',
            'benefits' => 'sometimes|array|max:' . self::MAX_RECORDS_PER_TYPE,
            'benefits.*' => 'array',
        ], [
            'people.max' => 'Maximum ' . self::MAX_RECORDS_PER_TYPE . ' records allowed for people',
            'families.max' => 'Maximum ' . self::MAX_RECORDS_PER_TYPE . ' records allowed for families',
            'cases.max' => 'Maximum ' . self::MAX_RECORDS_PER_TYPE . ' records allowed for cases',
            'benefits.max' => 'Maximum ' . self::MAX_RECORDS_PER_TYPE . ' records allowed for benefits',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Import a specific resource type with transactional safety.
     *
     * @return array{created: int, failed: int, errors: array}
     */
    protected function importResourceType(string $type, array $records): array
    {
        $created = 0;
        $failed = 0;
        $errors = [];

        try {
            DB::transaction(function () use ($type, $records, &$created, &$failed, &$errors) {
                foreach ($records as $index => $recordData) {
                    try {
                        $this->createRecord($type, $recordData);
                        $created++;
                    } catch (\Exception $e) {
                        $failed++;
                        $errors[] = [
                            'index' => $index,
                            'data' => $recordData,
                            'error' => $e->getMessage(),
                        ];
                    }
                }

                // If any record failed, rollback all for this resource type
                if ($failed > 0) {
                    throw new \Exception("Import failed for {$failed} {$type} records. Transaction rolled back.");
                }
            });
        } catch (\Exception $e) {
            // Transaction was rolled back
            return [
                'created' => 0,
                'failed' => count($records),
                'errors' => $errors,
                'message' => $e->getMessage(),
            ];
        }

        return [
            'created' => $created,
            'failed' => 0,
            'errors' => [],
        ];
    }

    /**
     * Create a single record based on resource type using individual create actions.
     *
     * Transactions are disabled since we're already in a transaction wrapper.
     * Notifications are disabled for bulk operations to avoid email flooding.
     */
    protected function createRecord(string $type, array $data): mixed
    {
        return match ($type) {
            'people' => $this->createPerson->execute(
                data: $data,
                inTransaction: false
            ),
            'families' => $this->createFamily->execute(
                data: $data,
                inTransaction: false
            ),
            'cases' => $this->createCase->execute(
                data: $data,
                inTransaction: false,
                sendNotifications: false
            ),
            'benefits' => $this->createBenefit->execute(
                data: $data,
                inTransaction: false,
                sendNotifications: false
            ),
            default => throw new \InvalidArgumentException("Unknown resource type: {$type}"),
        };
    }
}
