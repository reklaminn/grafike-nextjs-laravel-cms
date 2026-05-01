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
            URL::forceScheme('https');
            URL::forceRootUrl('https://' . $request->getHost());

            // Redirect HTTP → HTTPS at PHP level (independent of Traefik config).
            if (! $request->secure()) {
                return redirect()->secure($request->getRequestUri(), 301);
            }
        }

        $response = $next($request);

        // DEBUG: prove this middleware actually ran. Remove once HTTPS issue is resolved.
        if (method_exists($response, 'headers')) {
            $response->headers->set('X-ForceHttps-Ran', $skip ? 'skipped' : 'yes');
            $response->headers->set('X-ForceHttps-Env', app()->environment());
            $response->headers->set('X-ForceHttps-Host', $request->getHost());
            $response->headers->set('X-ForceHttps-Secure', $request->secure() ? 'yes' : 'no');
        }

        return $response;
    }
}
