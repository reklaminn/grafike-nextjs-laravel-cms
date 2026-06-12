<?php

declare(strict_types=1);

namespace App\Observers;

use App\Services\TraefikDynamicConfig;
use Stancl\Tenancy\Database\Models\Domain;

/**
 * Keeps the Traefik dynamic-config file in sync with the `domains` table.
 *
 * Every CRUD on a tenant domain triggers a full regenerate of the file —
 * cheaper than diffing (we have dozens, not thousands of tenants) and
 * guarantees the file matches the DB exactly.  The regenerate call itself
 * is wrapped in try/catch inside the service, so a write failure never
 * breaks the Domain create/update/delete that triggered it.
 *
 * Registered in AppServiceProvider::boot() against the stancl Domain model.
 */
class DomainObserver
{
    public function __construct(private readonly TraefikDynamicConfig $traefik) {}

    public function created(Domain $domain): void
    {
        $this->traefik->regenerate();
    }

    public function updated(Domain $domain): void
    {
        $this->traefik->regenerate();
    }

    public function deleted(Domain $domain): void
    {
        $this->traefik->regenerate();
    }

    /**
     * Soft-delete restore (in case the central DB ever enables soft deletes
     * on the domains table — current schema doesn't, but the hook is safe
     * to wire up either way).
     */
    public function restored(Domain $domain): void
    {
        $this->traefik->regenerate();
    }
}
