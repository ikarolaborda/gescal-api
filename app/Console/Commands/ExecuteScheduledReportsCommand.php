<?php

namespace App\Console\Commands;

use App\Jobs\ExecuteScheduledReportJob;
use App\Models\ReportSchedule;
use Illuminate\Console\Command;

class ExecuteScheduledReportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:execute-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find and queue all due report schedules for execution';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for due report schedules...');

        // Find all active schedules that are due
        $dueSchedules = ReportSchedule::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('next_execution_at')
                    ->orWhere('next_execution_at', '<=', now());
            })
            ->get();

        if ($dueSchedules->isEmpty()) {
            $this->info('No due schedules found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$dueSchedules->count()} due schedule(s).");

        foreach ($dueSchedules as $schedule) {
            $this->line("Queuing schedule: {$schedule->name} (ID: {$schedule->id})");

            ExecuteScheduledReportJob::dispatch($schedule);
        }

        $this->info('All due schedules have been queued.');

        return Command::SUCCESS;
    }
}
