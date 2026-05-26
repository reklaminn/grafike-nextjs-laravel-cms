<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Payments Module Configuration
|--------------------------------------------------------------------------
|
| BYOK model — each tenant supplies their own API keys via the admin
| panel.  Keys are encrypted with Laravel's Crypt facade (APP_KEY) and
| persisted under Tenant.data.iyzico_keys.  See IyzicoBYOKResolver.
|
| Platform DOES NOT see plain keys at any point; they are pulled from
| Tenant.data, decrypted in-memory, and passed to the gateway per
| request.  Memory is cleared after the call (no caching).
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Default gateway
    |--------------------------------------------------------------------------
    | Used when a booking flow does not specify a gateway explicitly.
    | Currently we ship only Iyzico; multi-gateway routing per tenant
    | (e.g. "use Stripe for EU bookings, Iyzico for TR") is a Phase 5
    | concern.
    */
    'default_gateway' => env('PAYMENTS_DEFAULT_GATEWAY', 'iyzico'),

    /*
    |--------------------------------------------------------------------------
    | Gateway-specific config
    |--------------------------------------------------------------------------
    | Per-gateway settings.  `class` is bound by PaymentsModuleServiceProvider.
    | `keys_path` is the dot-path inside Tenant.data where this tenant's
    | encrypted credentials live (e.g. data.iyzico_keys.api_key + secret_key).
    */
    'gateways' => [

        'iyzico' => [
            'class'     => \App\Modules\Payments\Gateways\IyzicoGateway::class,

            // Tenant.data path holding the BYOK credentials.  Each
            // gateway picks its own structure; for Iyzico we store:
            //   data.iyzico_keys: { api_key: <encrypted>, secret_key: <encrypted>, sandbox: bool }
            'keys_path' => 'iyzico_keys',

            // Default sandbox flag if the tenant has not set one.
            // Production tenants explicitly opt-in by setting
            // data.iyzico_keys.sandbox = false in the admin panel.
            'default_sandbox' => env('IYZICO_DEFAULT_SANDBOX', true),

            // API base URLs.  Iyzico uses different hosts for sandbox
            // and production; we never hit production by accident
            // because BYOK explicitly carries the sandbox flag.
            'base_url_production' => 'https://api.iyzipay.com',
            'base_url_sandbox'    => 'https://sandbox-api.iyzipay.com',

            // Request timeout (seconds) — keep conservative because
            // 3DS calls hit downstream banks and can be slow.
            'timeout' => 30,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Reservation hold window
    |--------------------------------------------------------------------------
    | How long a "reserved" booking holds its tour_date capacity before
    | the ExpireUnpaidReservationJob releases it.  Tuned to match the
    | typical 3DS challenge → user-decision-makes-coffee timeline.
    */
    'reservation_hold_minutes' => env('PAYMENTS_RESERVATION_HOLD_MINUTES', 20),

    /*
    |--------------------------------------------------------------------------
    | Audit log table connection
    |--------------------------------------------------------------------------
    | Central DB connection used by PaymentAuditLog — webhook records
    | survive even when a tenant DB is destroyed (compliance + dispute
    | reconciliation).
    */
    'audit_connection' => 'central',

];
