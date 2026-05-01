<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect any plain-HTTP request to HTTPS.
 *
 * This runs at the PHP level so it works regardless of whether Traefik's
 * http-catchall router is configured. `$request->secure()` returns true when
 * the X-Forwarded-Proto: https header is present (trustProxies must be set).
 *
 * Skipped in local / testing environments so artisan serve still works.
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->secure() && app()->isProduction()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
