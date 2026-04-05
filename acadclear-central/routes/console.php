<?php

use Illuminate\Support\Facades\Schedule;

// Daily maintenance at midnight
Schedule::command('maintenance:daily')
    ->dailyAt('00:00')
    ->appendOutputTo(storage_path('logs/daily-maintenance.log'))
    ->onSuccess(function () {
        \Log::info('Daily maintenance completed successfully');
    })
    ->onFailure(function () {
        \Log::error('Daily maintenance failed');
    });

// Check expired subscriptions every hour (for safety)
Schedule::command('subscriptions:check-expiry')
    ->hourly()
    ->appendOutputTo(storage_path('logs/subscription-expiry.log'));

// Generate daily report at 6 AM
Schedule::command('report:daily --save')
    ->dailyAt('06:00')
    ->appendOutputTo(storage_path('logs/daily-report.log'));

// Weekly backup on Sundays at 1 AM
Schedule::command('tenant:backup --all')
    ->weekly()
    ->sundays()
    ->at('01:00')
    ->appendOutputTo(storage_path('logs/weekly-backup.log'));

// Monthly report on 1st of month at 8 AM
Schedule::command('report:daily --save')
    ->monthly()
    ->at('08:00')
    ->appendOutputTo(storage_path('logs/monthly-report.log'));