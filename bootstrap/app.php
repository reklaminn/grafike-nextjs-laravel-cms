<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust Traefik (and any Docker reverse proxy) so Laravel correctly
        // detects HTTPS from the X-Forwarded-Proto header. Without this,
        // $request->secure() always returns false behind a TLS-terminating proxy.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        // Register ForceHttps in EVERY way Laravel 12 supports, so it runs
        // regardless of which group the request is dispatched through.
        // - prepend(): adds to global middleware stack (runs before group middleware)
        // - prependToGroup('web'): adds to web group (runs on all web routes)
        // - prependToGroup('api'): adds to api group (runs on all api routes)
        $middleware->prepend(\App\Http\Middleware\UseSiteHostHeader::class);
        $middleware->prepend(\App\Http\Middleware\ForceHttps::class);
        $middleware->prependToGroup('web', \App\Http\Middleware\ForceHttps::class);
        $middleware->prependToGroup('api', \App\Http\Middleware\ForceHttps::class);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->alias([
            'admin.auth'      => \App\Http\Middleware\AdminAuthenticate::class,
            'member.auth'     => \App\Http\Middleware\MemberAuthenticate::class,
            'tenant.admin'    => \App\Http\Middleware\InitializeTenancyForAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);
    })->create();
