<?php

namespace App\Actions\Families;

use App\Models\Family;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class CreateFamilyAction
{
    public function __construct(
        private readonly AuditLogService $auditLog
    ) {}

    /**
     * Create a new family with transactional logic.
     *
     * @param  array<string, mixed>  $data
     * @param  bool  $inTransaction  Whether to wrap in a transaction (disable when already in a transaction)
     */
    public function execute(array $data, bool $inTransaction = true): Family
    {
        $operation = function () use ($data) {
            $family = Family::create($data);

            $this->auditLog->log($family, 'created', additionalData: [
                'new_values' => $data,
            ]);

            return $family->fresh();
        };

        return $inTransaction
            ? DB::transaction($operation)
            : $operation();
    }
}
