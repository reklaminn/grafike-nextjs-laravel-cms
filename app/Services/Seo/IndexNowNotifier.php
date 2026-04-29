<?php

namespace App\Services\Seo;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends IndexNow notifications to Bing / Yandex when pages or articles
 * are published or updated.
 *
 * Protocol spec: https://www.indexnow.org/documentation
 *
 * Required setup:
 *   1. Admin → Settings → Site → IndexNow Key (services.indexnow_key)
 *   2. The key value is served at /{key}.txt via a dynamic route (see routes/web.php)
 *
 * If the key is empty the service is a no-op (safe for local dev).
 */
class IndexNowNotifier
{
    private const ENDPOINT = 'https://api.indexnow.org/indexnow';

    /** Notify IndexNow about a single URL. */
    public function ping(string $url, ?int $siteId = null): void
    {
        $this->pingBatch([$url], $siteId);
    }

    /** Notify IndexNow about multiple URLs (max 10 000 per call). */
    public function pingBatch(array $urls, ?int $siteId = null): void
    {
        $key = SiteSetting::get('services.indexnow_key', '', $siteId);

        if (empty($key) || empty($urls)) {
            return;
        }

        $siteUrl  = rtrim(config('app.url', ''), '/');
        $host     = parse_url($siteUrl, PHP_URL_HOST) ?: '';

        if (empty($host)) {
            Log::warning('IndexNow: app.url is not set — skipping notification');
            return;
        }

        $payload = [
            'host'        => $host,
            'key'         => $key,
            'keyLocation' => "{$siteUrl}/{$key}.txt",
            'urlList'     => array_values(array_unique(array_filter($urls))),
        ];

        try {
            $response = Http::timeout(8)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post(self::ENDPOINT, $payload);

            if (! in_array($response->status(), [200, 202], true)) {
                Log::warning('IndexNow: unexpected response', [
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                    'payload' => $payload,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('IndexNow: notification failed', [
                'error'   => $e->getMessage(),
                'payload' => $payload,
            ]);
        }
    }
}
