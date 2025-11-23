<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule LGPD hard delete cleanup command to run daily at 2 AM
Schedule::command('lgpd:hard-delete-expired --force')->dailyAt('02:00');

// Schedule report execution check to run every minute
Schedule::command('reports:execute-scheduled')->everyMinute();
