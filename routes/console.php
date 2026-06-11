<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('leaves:process-daily')
    ->daily()
    ->at('23:59')
    ->withoutOverlapping()
    ->runInBackground()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/leaves_process_daily.log'));

Schedule::command('shift-roster:finalize')
    ->daily()
    ->at('23:59')
    ->withoutOverlapping()
    ->runInBackground()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/shift_roster_finalize.log'));

Schedule::command('compensatory:sync-quotas')
    ->daily()
    ->at('23:59')
    ->withoutOverlapping()
    ->runInBackground()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/compensatory_sync_quotas.log'));

Schedule::command('leave:rollover')
    ->yearlyOn(1, 1, '00:30')
    ->withoutOverlapping()
    ->runInBackground()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/leave_rollover.log'));
