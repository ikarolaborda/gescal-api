<?php

namespace App\Actions\People;

use App\Models\Person;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class CreatePersonAction
{
    public function __construct(
        private readonly AuditLogService $auditLog
    ) {}

    /**
     * Create a new person with transactional logic.
     *
     * @param  array<string, mixed>  $data
     * @param  bool  $inTransaction  Whether to wrap in a transaction (disable when already in a transaction)
     */
    public function execute(array $data, bool $inTransaction = true): Person
    {
        $operation = function () use ($data) {
            $person = Person::create($data);

            $this->auditLog->log($person, 'created', additionalData: [
                'new_values' => $data,
            ]);

            return $person->fresh();
        };

        return $inTransaction
            ? DB::transaction($operation)
            : $operation();
    }
}
