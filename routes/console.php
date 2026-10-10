<?php

use App\Services\BillingService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('bills:mark-overdue', function (BillingService $billing) {
    $count = $billing->markOverdue();

    $this->info("{$count} bill(s) marked overdue.");
})->purpose('Mark bills that are past their due date with money still owed as overdue');

// Run `php artisan schedule:run` every minute (cron / Windows Task Scheduler).
Schedule::command('bills:mark-overdue')->dailyAt('00:15');
