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

// Defensive Traefik dynamic-config sync.  The DomainObserver already
// regenerates the file on every domain CRUD, so this hourly tick is purely
// a drift-detection safety net for scenarios where the observer doesn't
// fire (raw SQL inserts, DB restores, manual edits, container rebuilds).
// The command itself is idempotent — same content → file mtime updates but
// Traefik file provider detects no diff and skips the reload.
Schedule::command('traefik:sync')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();
