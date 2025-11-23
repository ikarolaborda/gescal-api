<?php

namespace App\Jobs;

use App\Actions\Reports\GenerateScheduledReportAction;
use App\Mail\ScheduledReportFailedNotification;
use App\Models\ReportSchedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ExecuteScheduledReportJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ReportSchedule $schedule
    ) {
        $this->onQueue(config('reports.queue.name', 'reports'));
    }

    /**
     * Execute the job.
     */
    public function handle(GenerateScheduledReportAction $action): void
    {
        Log::info('Executing scheduled report', [
            'schedule_id' => $this->schedule->id,
            'schedule_name' => $this->schedule->name,
        ]);

        try {
            $report = $action->execute($this->schedule);

            // Send report to recipients
            foreach ($this->schedule->recipients as $recipient) {
                Mail::to($recipient)->send(
                    new \App\Mail\ReportCompletedNotification($report)
                );
            }

            Log::info('Scheduled report executed successfully', [
                'schedule_id' => $this->schedule->id,
                'report_id' => $report->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Scheduled report execution failed', [
                'schedule_id' => $this->schedule->id,
                'error' => $e->getMessage(),
            ]);

            // Notify admin if schedule is disabled due to failures
            $maxFailures = config('reports.schedule_max_failures', 5);
            if ($this->schedule->failure_count >= $maxFailures) {
                Mail::to($this->schedule->user)->send(
                    new ScheduledReportFailedNotification($this->schedule)
                );
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Scheduled report job failed permanently', [
            'schedule_id' => $this->schedule->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
