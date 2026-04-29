<?php

namespace App\Http\Controllers\Api\Concerns;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Adds HTTP caching headers (ETag + Last-Modified) to API resource responses.
 *
 * - ETag: MD5 of "{model_id}-{updated_at_timestamp}"
 * - Last-Modified: RFC7231 format of the model's updated_at timestamp
 * - Returns HTTP 304 Not Modified when the client's cached version is current.
 */
trait AddsHttpCacheHeaders
{
    /**
     * Return a JsonResource response decorated with ETag and Last-Modified headers.
     * Automatically responds with 304 if the client sends a matching If-None-Match
     * or an If-Modified-Since that is >= the resource's last modification time.
     *
     * @param  JsonResource  $resource
     * @param  Carbon|null   $lastModified  Falls back to now() when null.
     * @param  string        $etagSeed      Any string to derive the ETag from.
     */
    protected function cachedResponse(
        JsonResource $resource,
        ?Carbon $lastModified,
        string $etagSeed,
    ): \Illuminate\Http\JsonResponse|\Illuminate\Http\Response {
        $lastModified ??= now();
        $etag = '"' . md5($etagSeed . $lastModified->timestamp) . '"';
        $lastModifiedStr = $lastModified->toRfc7231String();

        /** @var Request $request */
        $request = request();

        // ── 304 checks ────────────────────────────────────────────────────────

        $ifNoneMatch    = $request->header('If-None-Match');
        $ifModifiedSince = $request->header('If-Modified-Since');

        $etagMatches = $ifNoneMatch && ($ifNoneMatch === $etag || $ifNoneMatch === '*');

        $notModifiedSince = false;
        if ($ifModifiedSince) {
            try {
                $since = Carbon::parse($ifModifiedSince);
                $notModifiedSince = $lastModified->lte($since);
            } catch (\Throwable) {
                // Ignore malformed header
            }
        }

        if ($etagMatches || $notModifiedSince) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Last-Modified', $lastModifiedStr)
                ->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
        }

        // ── Full response ─────────────────────────────────────────────────────

        return $resource->response()
            ->header('ETag', $etag)
            ->header('Last-Modified', $lastModifiedStr)
            ->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
    }
}
