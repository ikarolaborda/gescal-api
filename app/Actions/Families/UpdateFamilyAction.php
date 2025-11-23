<?php

namespace App\Actions\Families;

use App\Models\Family;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class UpdateFamilyAction
{
    public function __construct(
        private readonly AuditLogService $auditLog
    ) {}

    /**
     * Update an existing family with transactional logic.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(Family $family, array $data): Family
    {
        return DB::transaction(function () use ($family, $data) {
            $oldValues = $family->only(array_keys($data));
            $family->update($data);

            $this->auditLog->log($family, 'updated', additionalData: [
                'old_values' => $oldValues,
                'new_values' => $data,
            ]);

            return $family->fresh();
        });
    }
}
