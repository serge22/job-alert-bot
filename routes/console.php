<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

$fetchSchedule = config('app.fetch_jobs_schedule', 'everyTwoMinutes');
// 10 minutes lock timeout, so if something goes wrong we don't lose updates for 24 hours
Schedule::command('app:fetch-jobs')->{$fetchSchedule}()->withoutOverlapping(10);
Schedule::command('app:cleanup-old-jobs')->daily();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
