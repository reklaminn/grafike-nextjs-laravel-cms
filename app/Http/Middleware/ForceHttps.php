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
        // DEBUG: write to a file every time this middleware runs.
        // We'll grep this file to prove the middleware actually executed.
        @file_put_contents(
            '/var/www/html/storage/logs/force-https.log',
            date('c').' '.$request->getMethod().' '.$request->fullUrl().' env='.app()->environment().PHP_EOL,
            FILE_APPEND
        );

        $skip = in_array(app()->environment(), ['local', 'testing'], true)
            || $this->isInternalDockerHost($request->getHost());

        if (! $skip) {
            URL::forceScheme('https');
            URL::forceRootUrl('https://'.$request->getHost());

            if (! $request->secure()) {
                return redirect()->secure($request->getRequestUri(), 301);
            }
        }

        $response = $next($request);

        // FIX: $response->headers is a PROPERTY, not a method. Use direct
        // property access wrapped in a try/catch to handle any response type.
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
        return in_array(strtolower($host), [
            'app1',
            'app2',
            'app3',
            'grafike_cms_app1',
            'grafike_cms_app2',
            'grafike_cms_app3',
        ], true);
    }
}
