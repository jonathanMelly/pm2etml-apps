<?php

use App\Console\Commands\EvaluationReportCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule::command('inspire')->hourly();
Schedule::command(EvaluationReportCommand::class)->dailyAt('07:45')->weekdays()
    ->sentryMonitor('pm2etml-apps-cron-evaluation-report');
