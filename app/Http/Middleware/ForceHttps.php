<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Brute-force HTTPS enforcement middleware.
 *
 * Runs as a global middleware on every request. Three responsibilities:
 *
 * 1. Force URL::scheme('https') on every request, no conditions.
 * 2. Force URL::rootUrl('https://<host>') based on the actual request host
 *    (so multi-tenant subdomains and custom domains all get the right root).
 * 3. 301-redirect any plain-HTTP request to HTTPS at PHP level, regardless
 *    of whether Traefik did it.
 *
 * Skipped only when APP_ENV=local|testing so artisan serve still works.
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip in local/testing only — production, staging, etc. all force HTTPS.
        $skip = in_array(app()->environment(), ['local', 'testing'], true);

        if (! $skip) {
            // Force URL generator state on EVERY request, unconditionally.
            // Use the actual request host so tenant subdomains/custom domains
            // all generate correct HTTPS URLs against their own domain.
            URL::forceScheme('https');
            URL::forceRootUrl('https://' . $request->getHost());

            // Redirect HTTP → HTTPS at PHP level (independent of Traefik config).
            if (! $request->secure()) {
                return redirect()->secure($request->getRequestUri(), 301);
            }
        }

        return $next($request);
    }
}
