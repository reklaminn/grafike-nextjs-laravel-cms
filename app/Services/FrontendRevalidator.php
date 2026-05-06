<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends on-demand ISR revalidation requests to the Next.js frontend.
 *
 * Each observer calls this service after a model is saved/deleted so that
 * the Next.js Data Cache is immediately purged for the affected pages/tags.
 *
 * Required env vars:
 *   CMS_FRONTEND_URL      – base URL of Next.js app (e.g. http://frontend:3000)
 *   CMS_REVALIDATE_SECRET – shared secret; must match Next.js REVALIDATE_SECRET
 *
 * If CMS_REVALIDATE_SECRET is empty, the service is a no-op (safe for local dev).
 *
 * Tag scoping (multi-tenant isolation):
 *   The Next.js frontend serves all tenant domains from a single instance.
 *   Cache tags must be prefixed with the current tenant's primary domain so
 *   that revalidating tenant-A never busts tenant-B's cache.
 *
 *   e.g.  tag "page-home"  →  "nuhcicek.com.tr:page-home"
 *
 *   When called outside a tenant context (central-domain requests, console
 *   commands) the tags are sent unscoped, which is intentional — it purges
 *   the cache for the central domain only.
 */
class FrontendRevalidator
{
    private readonly string $frontendUrl;
    private readonly string $secret;

    public function __construct()
    {
        $this->frontendUrl = rtrim(config('cms.frontend_url', 'http://127.0.0.1:3000'), '/');
        $this->secret      = config('cms.revalidate_secret', '');
    }

    // ─── Public API ───────────────────────────────────────────────────────────

    /** Purge Next.js Data Cache entries by URL path (e.g. "/tr/home"). */
    public function paths(array $paths): void
    {
        if (empty($paths)) return;
        $this->send(compact('paths'));
    }

    /**
     * Purge Next.js Data Cache entries by tag.
     *
     * Tags are automatically prefixed with the current tenant's primary domain
     * when running inside a tenant context, preventing cross-tenant cache busting.
     */
    public function tags(array $tags): void
    {
        if (empty($tags)) return;
        $this->send(['tags' => $this->scopeTags($tags)]);
    }

    /** Purge by both paths and tags in a single request. */
    public function flush(array $paths = [], array $tags = []): void
    {
        $payload = [];
        if ($paths) $payload['paths'] = $paths;
        if ($tags)  $payload['tags']  = $this->scopeTags($tags);
        if ($payload) $this->send($payload);
    }

    // ─── Tenant tag scoping ───────────────────────────────────────────────────

    /**
     * Prefix every tag with the current tenant's primary domain.
     *
     * This mirrors the prefixing done in the Next.js API client (client.ts),
     * so Laravel's revalidation calls always bust the right tenant's cache.
     *
     *   "page-home"  →  "nuhcicek.com.tr:page-home"
     *
     * Falls back to unscoped tags when not inside a tenant context (central
     * domain, artisan commands, etc.).
     */
    private function scopeTags(array $tags): array
    {
        $domain = $this->currentTenantDomain();

        if (! $domain) {
            return $tags;
        }

        return array_map(fn (string $tag) => "{$domain}:{$tag}", $tags);
    }

    /**
     * Resolve the primary domain of the currently-initialised tenant.
     * Returns null when called outside a tenant context.
     */
    private function currentTenantDomain(): ?string
    {
        try {
            if (! function_exists('tenancy') || ! tenancy()->initialized) {
                return null;
            }

            $tenant = tenancy()->tenant;

            if (! $tenant) {
                return null;
            }

            // Use the tenant's key (slug) as fallback when no domain relationship is loaded.
            return $tenant->domains?->first()?->domain
                ?? (string) $tenant->getTenantKey();
        } catch (\Throwable) {
            return null;
        }
    }

    // ─── HTTP ─────────────────────────────────────────────────────────────────

    private function send(array $payload): void
    {
        // No-op when secret is not configured (local dev without Next.js running)
        if (empty($this->secret)) {
            return;
        }

        try {
            Http::timeout(5)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->secret}",
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])
                ->post("{$this->frontendUrl}/api/revalidate", $payload);
        } catch (\Throwable $e) {
            // Non-fatal — log and continue. A failed revalidation just means
            // the page stays cached until the next TTL expiry (60 s default).
            Log::warning('Frontend ISR revalidation failed', [
                'payload' => $payload,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
