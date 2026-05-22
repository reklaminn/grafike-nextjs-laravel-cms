<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Database\Models\Domain;
use Symfony\Component\Yaml\Yaml;
use Throwable;

/**
 * Generates a Traefik file-provider config from the tenant domains table.
 *
 * Architecture:
 *   - Each tenant domain (cms.dentistkusadasi.com, nuhcicek.com.tr, …) needs its
 *     own `Host(`…`)` router so Traefik can request a per-domain ACME HTTP-01
 *     cert.  HostRegexp wildcard routers don't trigger ACME automatically
 *     because Traefik can't enumerate matching domains from a regex.
 *   - The file lives on a host volume shared between the Laravel container
 *     (write) and the Traefik container (read).  Traefik's file provider
 *     watches the directory and hot-reloads within ~2s of a change.
 *   - Writes are atomic (tmp file + rename) so Traefik never reads a partial
 *     document mid-write.
 *
 * Triggered by:
 *   - DomainObserver (on tenant domain create/update/delete)
 *   - `php artisan traefik:sync` (bootstrap + defensive hourly cron)
 */
class TraefikDynamicConfig
{
    /**
     * Filesystem destination for the generated config.  Defaults to the
     * standard mount path inside the app container; override via env in tests
     * or alternate deployments.
     */
    private string $outputDir;

    /**
     * Reference to the Docker-provider service that hosts Next.js.  Using
     * `@docker` qualifier so we re-use the existing LB pool defined by the
     * frontend container's labels (no duplicate server definitions).
     */
    private string $frontendService;

    /**
     * Cert resolver name from the Traefik static config.  Must match the
     * resolver that uses HTTP-01 challenge (per-domain certs).  For the
     * Hostinger VPS this is `mytlschallenge`.
     */
    private string $certResolver;

    public function __construct()
    {
        $this->outputDir       = (string) env('TRAEFIK_DYNAMIC_PATH', '/var/traefik-dynamic');
        $this->frontendService = (string) env('TRAEFIK_FRONTEND_SERVICE', 'grafike-frontend@docker');
        $this->certResolver    = (string) env('TRAEFIK_CERT_RESOLVER', 'mytlschallenge');
    }

    /**
     * Regenerate the tenant routers file from the current state of the
     * `domains` table in the central DB.  Safe to call from any context:
     * resolves to a no-op (with a warning log) if the output directory is
     * unwritable, so a local dev install without the mount doesn't error.
     */
    public function regenerate(): void
    {
        if (env('TRAEFIK_DYNAMIC_DISABLED', false)) {
            return;
        }

        if (! is_dir($this->outputDir)) {
            Log::debug('TraefikDynamicConfig: output dir missing, skipping', [
                'dir' => $this->outputDir,
            ]);
            return;
        }

        if (! is_writable($this->outputDir)) {
            Log::warning('TraefikDynamicConfig: output dir not writable', [
                'dir' => $this->outputDir,
            ]);
            return;
        }

        try {
            $config = $this->buildConfig();

            // Traefik v3 file provider reads YAML / TOML only — JSON support
            // was dropped from v2.  Symfony Yaml dumps with `inline=8` so the
            // nested router structures stay multi-line readable rather than
            // collapsing to one ugly line at depth 4.
            $payload = Yaml::dump($config, 8, 2, Yaml::DUMP_OBJECT_AS_MAP);

            $dest = $this->outputDir . '/tenants.yaml';
            $tmp  = $dest . '.tmp.' . bin2hex(random_bytes(4));

            if (file_put_contents($tmp, $payload, LOCK_EX) === false) {
                throw new \RuntimeException('Failed to write tmp file: ' . $tmp);
            }

            // Atomic rename so Traefik never reads a half-written file.
            if (! rename($tmp, $dest)) {
                @unlink($tmp);
                throw new \RuntimeException('Failed to rename tmp -> dest');
            }

            // Clean up any legacy tenants.json from earlier deploys — leaving
            // it lying around would confuse anyone debugging.
            $legacyJson = $this->outputDir . '/tenants.json';
            if (is_file($legacyJson)) {
                @unlink($legacyJson);
            }

            Log::info('TraefikDynamicConfig: regenerated', [
                'file'         => $dest,
                'domain_count' => count($config['http']['routers'] ?? []) / 2,
            ]);
        } catch (Throwable $e) {
            // Domain CRUD must not fail because of a dynamic-config write error.
            // Log loudly so ops can fix the mount; never throw upstream.
            Log::error('TraefikDynamicConfig: regenerate failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Build the full Traefik dynamic config payload.  Each tenant domain
     * gets two routers:
     *   - <key>           — HTTPS, port 443, TLS via cert resolver
     *   - <key>-http      — HTTP, port 80, redirected to HTTPS
     *
     * Both reference the same Next.js LB service so the frontend continues
     * to handle the actual request after routing.
     */
    private function buildConfig(): array
    {
        $routers     = [];
        $middlewares = [
            // Tenant-domain HTTP -> HTTPS redirect.  Permanent=false (307) so
            // POST bodies are preserved on first-load redirects (matches the
            // global https-redirect middleware on the catch-all route).
            'tenant-https-redirect' => [
                'redirectScheme' => [
                    'scheme'    => 'https',
                    'permanent' => false,
                ],
            ],
        ];

        // Pull every domain from the central tenants DB.  This includes
        // central domains too (e.g. graficms.grafike.site if it's in
        // `domains`), but those typically have their own static router from
        // docker-compose labels — and even if duplicated here, Traefik just
        // uses whichever has higher priority.  Filtering would require
        // hardcoding the central host list, which we intentionally avoid.
        $domains = Domain::query()->orderBy('domain')->get();

        foreach ($domains as $domain) {
            $host = (string) $domain->domain;

            if (! $this->isValidHost($host)) {
                Log::warning('TraefikDynamicConfig: skipping invalid host', ['host' => $host]);
                continue;
            }

            $key = 'tenant-' . preg_replace('/[^a-z0-9-]/', '-', strtolower($host));

            $routers[$key] = [
                'rule'        => sprintf('Host(`%s`)', $host),
                'entryPoints' => ['websecure'],
                'service'     => $this->frontendService,
                'priority'    => 100,
                'tls'         => [
                    'certResolver' => $this->certResolver,
                ],
            ];

            $routers[$key . '-http'] = [
                'rule'        => sprintf('Host(`%s`)', $host),
                'entryPoints' => ['web'],
                'service'     => $this->frontendService,
                'priority'    => 100,
                'middlewares' => ['tenant-https-redirect@file'],
            ];
        }

        return [
            'http' => [
                'routers'     => $routers,
                'middlewares' => $middlewares,
            ],
        ];
    }

    /**
     * Defensive host validation — rejects obviously malformed inputs that
     * could break the Traefik rule parser or, worse, inject extra rule
     * fragments (defence-in-depth; Eloquent already escapes input).
     */
    private function isValidHost(string $host): bool
    {
        if ($host === '' || strlen($host) > 253) {
            return false;
        }
        // Allow hostnames + dots; reject anything that could close the Host(`…`) backtick.
        return (bool) preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/', $host);
    }
}
