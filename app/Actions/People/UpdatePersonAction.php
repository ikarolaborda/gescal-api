<?php

namespace App\Actions\People;

use App\Models\Person;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class UpdatePersonAction
{
    public function __construct(
        private readonly AuditLogService $auditLog
    ) {}

    /**
     * Update an existing person with transactional logic.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(Person $person, array $data): Person
    {
        return DB::transaction(function () use ($person, $data) {
            $oldValues = $person->only(array_keys($data));
            $person->update($data);

            $this->auditLog->log($person, 'updated', additionalData: [
                'old_values' => $oldValues,
                'new_values' => $data,
            ]);

            return $person->fresh();
        });
    }
}
