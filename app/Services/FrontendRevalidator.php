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

    /** Purge Next.js Data Cache entries by tag (e.g. "page-home", "articles"). */
    public function tags(array $tags): void
    {
        if (empty($tags)) return;
        $this->send(compact('tags'));
    }

    /** Purge by both paths and tags in a single request. */
    public function flush(array $paths = [], array $tags = []): void
    {
        $payload = [];
        if ($paths) $payload['paths'] = $paths;
        if ($tags)  $payload['tags']  = $tags;
        if ($payload) $this->send($payload);
    }

    // ─── Internal ─────────────────────────────────────────────────────────────

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
