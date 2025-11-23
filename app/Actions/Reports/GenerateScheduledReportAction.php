<?php

namespace App\Actions\Reports;

use App\Models\Report;
use App\Models\ReportExecutionHistory;
use App\Models\ReportSchedule;

class GenerateScheduledReportAction
{
    public function __construct(
        private readonly GenerateReportAction $generateReportAction
    ) {}

    public function execute(ReportSchedule $schedule): Report
    {
        // Create execution history entry
        $execution = ReportExecutionHistory::create([
            'report_schedule_id' => $schedule->id,
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            // Create the report
            $report = Report::create([
                'user_id' => $schedule->user_id,
                'entity_type' => $schedule->parameters['entity_type'] ?? 'persons',
                'format' => $schedule->parameters['format'] ?? 'pdf',
                'status' => \App\Enums\ReportStatus::Pending,
                'parameters' => $schedule->parameters,
                'file_available' => false,
            ]);

            // Generate the report
            $this->generateReportAction->execute($report);

            // Update execution history
            $execution->update([
                'report_id' => $report->id,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Reset failure count on success
            $schedule->update([
                'last_execution_at' => now(),
                'next_execution_at' => $schedule->calculateNextExecution(),
                'failure_count' => 0,
            ]);

            return $report;
        } catch (\Exception $e) {
            // Update execution history with error
            $execution->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            // Increment failure count
            $schedule->incrementFailureCount();

            // Disable schedule if too many failures
            $maxFailures = config('reports.schedule_max_failures', 5);
            if ($schedule->failure_count >= $maxFailures) {
                $schedule->update(['is_active' => false]);
            }

            throw $e;
        }
    }
}
