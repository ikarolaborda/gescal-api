<?php

namespace App\Jobs;

use App\Actions\Reports\GenerateReportAction;
use App\Mail\ReportCompletedNotification;
use App\Models\Report;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateReportJob implements ShouldQueue
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
        public Report $report
    ) {
        $this->onQueue(config('reports.queue.name', 'reports'));
    }

    /**
     * Execute the job.
     */
    public function handle(GenerateReportAction $generateAction): void
    {
        Log::info('Starting report generation job', [
            'report_id' => $this->report->id,
            'format' => $this->report->format,
            'entity_type' => $this->report->entity_type,
        ]);

        try {
            $generateAction->execute($this->report);

            // Send completion notification
            if ($this->report->user->email) {
                Mail::to($this->report->user)->send(
                    new ReportCompletedNotification($this->report)
                );
            }

            Log::info('Report generation job completed successfully', [
                'report_id' => $this->report->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Report generation job failed', [
                'report_id' => $this->report->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // The GenerateReportAction already updates the report status to failed
            // so we just need to re-throw for queue retry logic
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Report generation job failed permanently', [
            'report_id' => $this->report->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
