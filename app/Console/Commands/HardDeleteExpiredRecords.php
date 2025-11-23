<?php

namespace App\Console\Commands;

use App\Models\Benefit;
use App\Models\CaseRecord;
use App\Models\Document;
use App\Models\Family;
use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HardDeleteExpiredRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lgpd:hard-delete-expired
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete soft-deleted records past their LGPD retention period';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $isForced = $this->option('force');

        $this->info('LGPD Retention Period Cleanup');
        $this->info('============================');
        $this->newLine();

        // Get retention periods from config
        $retentionPeriods = [
            'people' => config('lgpd.retention_periods.people', 3650), // 10 years default
            'families' => config('lgpd.retention_periods.families', 3650),
            'cases' => config('lgpd.retention_periods.cases', 3650),
            'benefits' => config('lgpd.retention_periods.benefits', 3650),
            'documents' => config('lgpd.retention_periods.documents', 3650),
        ];

        $totalDeleted = 0;
        $results = [];

        // Process each model type
        $results['people'] = $this->processModel(Person::class, $retentionPeriods['people'], $isDryRun);
        $results['families'] = $this->processModel(Family::class, $retentionPeriods['families'], $isDryRun);
        $results['cases'] = $this->processModel(CaseRecord::class, $retentionPeriods['cases'], $isDryRun);
        $results['benefits'] = $this->processModel(Benefit::class, $retentionPeriods['benefits'], $isDryRun);
        $results['documents'] = $this->processModel(Document::class, $retentionPeriods['documents'], $isDryRun);

        // Display results
        $this->newLine();
        $this->table(
            ['Resource', 'Retention (days)', 'Expired Records', 'Status'],
            collect($results)->map(fn ($result, $key) => [
                ucfirst($key),
                $retentionPeriods[$key],
                $result['count'],
                $isDryRun ? 'Dry Run' : ($result['deleted'] ? 'Deleted' : 'Skipped'),
            ])->toArray()
        );

        $totalDeleted = collect($results)->sum('count');

        if ($isDryRun) {
            $this->warn("DRY RUN: {$totalDeleted} records would be permanently deleted");
            $this->info('Run without --dry-run to actually delete records');
        } else {
            if ($totalDeleted > 0) {
                if (! $isForced && ! $this->confirm("Permanently delete {$totalDeleted} records?", false)) {
                    $this->warn('Operation cancelled');

                    return self::FAILURE;
                }

                $actualDeleted = collect($results)->sum(fn ($r) => $r['deleted'] ? $r['count'] : 0);
                $this->info("Successfully deleted {$actualDeleted} expired records");
            } else {
                $this->info('No expired records found');
            }
        }

        return self::SUCCESS;
    }

    /**
     * Process a single model type for hard deletion.
     *
     * @param  class-string  $modelClass
     * @return array{count: int, deleted: bool}
     */
    protected function processModel(string $modelClass, int $retentionDays, bool $isDryRun): array
    {
        $cutoffDate = now()->subDays($retentionDays);

        $query = $modelClass::onlyTrashed()
            ->where('deleted_at', '<=', $cutoffDate);

        $count = $query->count();

        if ($count === 0) {
            return ['count' => 0, 'deleted' => false];
        }

        if ($isDryRun) {
            return ['count' => $count, 'deleted' => false];
        }

        // Permanently delete expired records
        DB::transaction(function () use ($query) {
            $query->forceDelete();
        });

        return ['count' => $count, 'deleted' => true];
    }
}
