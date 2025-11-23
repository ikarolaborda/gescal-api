<?php

namespace App\Actions\Cases;

use App\Mail\CaseCreatedNotification;
use App\Models\CaseRecord;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CreateCaseAction
{
    public function __construct(
        private readonly AuditLogService $auditLog
    ) {}

    /**
     * Create a new case record with transactional logic.
     *
     * @param  array<string, mixed>  $data
     * @param  bool  $inTransaction  Whether to wrap in a transaction (disable when already in a transaction)
     * @param  bool  $sendNotifications  Whether to send email notifications (disable for bulk operations)
     */
    public function execute(array $data, bool $inTransaction = true, bool $sendNotifications = true): CaseRecord
    {
        $operation = function () use ($data, $sendNotifications) {
            $case = CaseRecord::create($data);

            $this->auditLog->log($case, 'created', additionalData: [
                'new_values' => $data,
            ]);

            $case->load(['family.responsiblePerson', 'occurrence']);

            if ($sendNotifications) {
                $this->notifyCoordinators($case);
            }

            return $case->fresh();
        };

        return $inTransaction
            ? DB::transaction($operation)
            : $operation();
    }

    /**
     * Send case created notification to all coordinators.
     */
    protected function notifyCoordinators(CaseRecord $case): void
    {
        $coordinators = User::whereHas('roles', function ($query) {
            $query->where('name', 'coordinator');
        })->get();

        $actionUrl = config('app.frontend_url') . '/cases/' . $case->id;

        foreach ($coordinators as $coordinator) {
            Mail::to($coordinator->email)
                ->send(new CaseCreatedNotification($case, $actionUrl));
        }
    }
}
