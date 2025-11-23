<?php

namespace App\Actions\Benefits;

use App\Mail\BenefitGrantedNotification;
use App\Models\Benefit;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CreateBenefitAction
{
    public function __construct(
        private readonly AuditLogService $auditLog
    ) {}

    /**
     * Create a new benefit with transactional logic.
     *
     * @param  array<string, mixed>  $data
     * @param  bool  $inTransaction  Whether to wrap in a transaction (disable when already in a transaction)
     * @param  bool  $sendNotifications  Whether to send email notifications (disable for bulk operations)
     */
    public function execute(array $data, bool $inTransaction = true, bool $sendNotifications = true): Benefit
    {
        $operation = function () use ($data, $sendNotifications) {
            $data['is_active'] = $data['is_active'] ?? true;

            $benefit = Benefit::create($data);

            $this->auditLog->log($benefit, 'created', additionalData: [
                'new_values' => $data,
            ]);

            $benefit->load(['benefitProgram', 'person', 'family.responsiblePerson']);

            if ($sendNotifications) {
                $this->notifyCoordinators($benefit);
            }

            return $benefit->fresh();
        };

        return $inTransaction
            ? DB::transaction($operation)
            : $operation();
    }

    /**
     * Send benefit granted notification to all coordinators.
     */
    protected function notifyCoordinators(Benefit $benefit): void
    {
        $coordinators = User::whereHas('roles', function ($query) {
            $query->where('name', 'coordinator');
        })->get();

        $actionUrl = config('app.frontend_url') . '/benefits/' . $benefit->id;

        foreach ($coordinators as $coordinator) {
            Mail::to($coordinator->email)
                ->send(new BenefitGrantedNotification($benefit, $actionUrl));
        }
    }
}
