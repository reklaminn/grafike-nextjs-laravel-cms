<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Two responsibilities:
 *
 * 1. Force the URL generator's scheme + root URL on EVERY request.
 *    AppServiceProvider::boot() also does this, but that has been observed
 *    to be insufficient in some Laravel 12 + multi-tenant setups where the
 *    URL generator state is reset somewhere between boot and view rendering.
 *    Doing it again here in middleware guarantees URLs are always https://.
 *
 * 2. Redirect any plain-HTTP request to HTTPS at PHP level (works regardless
 *    of whether Traefik's http-catchall router is configured).
 *
 * Skipped in local / testing environments so artisan serve still works.
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        // Force URL generator state on EVERY request, not just at boot.
        // We can't trust the service provider boot to persist through the
        // entire request lifecycle.
        $appUrl = config('app.url') ?: env('APP_URL', '');
        if (str_starts_with((string) $appUrl, 'https://')) {
            URL::forceScheme('https');
            URL::forceRootUrl($appUrl);
        }

        // Redirect HTTP → HTTPS in production.
        if (! $request->secure() && app()->isProduction()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
