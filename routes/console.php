<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Process round open/close schedule every minute
Schedule::job(new \App\Jobs\ProcessRoundSchedule)->everyMinute();

// SMS Deposit reconciliation every 5 minutes
Schedule::command('sms-deposit:reconcile')->everyFiveMinutes();

// AI Risk auto-balance every 5 minutes
Schedule::call(function () {
    app(\App\Services\Risk\RiskEngineService::class)->runAutoBalance();
})->everyFiveMinutes();

// Generate Yeekee rounds daily at 23:50
Schedule::call(function () {
    app(\App\Services\LotteryService::class)->generateYeekeeRounds();
})->dailyAt('23:50');

// Daily stats reset at midnight
Schedule::call(function () {
    app(\App\Services\Risk\RiskEngineService::class)->resetDailyStats();
})->dailyAt('00:00');

// Profit snapshot every hour
Schedule::call(function () {
    app(\App\Services\Risk\RiskEngineService::class)->takeProfitSnapshot('hourly');
})->hourly();
