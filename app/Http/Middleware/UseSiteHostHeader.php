<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseSiteHostHeader
{
    /**
     * Next.js SSR calls Laravel through the Docker service name (app1), but
     * tenant resolution must run against the original public site host.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $siteHost = $request->headers->get('X-Site-Host');

        if ($siteHost && $this->isInternalDockerHost($request->getHost())) {
            $siteHost = strtolower(preg_replace('#^https?://#', '', rtrim($siteHost, '/')));

            if ($siteHost !== '') {
                $request->headers->set('host', $siteHost);
                $request->server->set('HTTP_HOST', $siteHost);
                $request->server->set('SERVER_NAME', $siteHost);
            }
        }

        return $next($request);
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
