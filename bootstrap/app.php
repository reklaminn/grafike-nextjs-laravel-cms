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

        // ForceHttps as a GLOBAL middleware (not just web group) so it runs
        // before any web/api group middleware. Using prepend() here instead of
        // web(prepend:) because the named-parameter form of web() is not
        // available in all Laravel 11 patch versions.
        $middleware->prepend(\App\Http\Middleware\ForceHttps::class);

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
