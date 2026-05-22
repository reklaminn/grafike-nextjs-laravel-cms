<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TraefikDynamicConfig;
use Illuminate\Console\Command;

/**
 * Regenerate the Traefik dynamic-config file from the current `domains` table.
 *
 * Use cases:
 *   - First-time deploy: file doesn't exist yet, run once to create it.
 *   - Defensive cron: scheduled hourly to catch any drift if an observer
 *     was bypassed (e.g. raw SQL insert, manual DB edit, or DB restore).
 *   - Disaster recovery: after restoring from backup or container rebuild.
 *
 * The command is idempotent — running it on an already-correct file is
 * a no-op from Traefik's perspective (file mtime updates but content matches,
 * so the file provider doesn't reload routers).
 */
class SyncTraefikDomains extends Command
{
    protected $signature = 'traefik:sync';

    protected $description = 'Regenerate the Traefik dynamic-config file from the tenant domains table';

    public function handle(TraefikDynamicConfig $traefik): int
    {
        $this->info('Regenerating Traefik dynamic config...');

        $traefik->regenerate();

        $this->info('Done. Check /var/traefik-dynamic/tenants.json (or your configured TRAEFIK_DYNAMIC_PATH).');
        $this->line('Traefik file provider will hot-reload within ~2 seconds; no restart required.');

        return self::SUCCESS;
    }
}
