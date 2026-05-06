<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Scheduled Tasks ───────────────────────────────────────────────────────────

// Daily tenant backup — runs at 02:00 every night
// Keeps the last 30 backups per tenant; older ones are pruned automatically.
Schedule::command('cms:backup-tenant --all')
    ->dailyAt('02:00')
    ->runInBackground()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup.log'));
