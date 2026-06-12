<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Brute-force HTTPS enforcement middleware.
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        $skip = in_array(app()->environment(), ['local', 'testing'], true)
            || $this->isInternalDockerHost($request->getHost());

        if (! $skip) {
            URL::forceScheme('https');
            URL::forceRootUrl('https://'.$request->getHost());

            if (! $request->secure()) {
                // Use 308 (Permanent Redirect) instead of 301 so that POST/PUT/PATCH
                // requests preserve their HTTP method through the redirect.
                // 301 silently converts POST → GET, discarding the request body
                // (sections_json, form tokens, file uploads, etc.).
                return redirect()->secure($request->getRequestUri(), 308);
            }
        }

        $response = $next($request);

        try {
            $response->headers->set('X-ForceHttps-Ran', $skip ? 'skipped' : 'yes');
            $response->headers->set('X-ForceHttps-Env', app()->environment());
            $response->headers->set('X-ForceHttps-Host', $request->getHost());
            $response->headers->set('X-ForceHttps-Secure', $request->secure() ? 'yes' : 'no');
        } catch (\Throwable $e) {
            // Some response types may not support headers — ignore.
        }

        return $response;
    }

    private function isInternalDockerHost(string $host): bool
    {
        $host = strtolower(explode(':', $host)[0]);

        return in_array($host, [
            'app1',
            'app2',
            'app3',
            'grafike_cms_app1',
            'grafike_cms_app2',
            'grafike_cms_app3',
        ], true);
    }
}
