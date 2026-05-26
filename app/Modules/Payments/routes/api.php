<?php

declare(strict_types=1);

use App\Modules\Payments\Http\Controllers\Api\IyzicoWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Payments module — tenant API routes
|--------------------------------------------------------------------------
|
| Loaded by PaymentsModuleServiceProvider::bootTenant() — meaning these
| endpoints exist ONLY on tenants that have the `payments` module
| enabled.  Routes are CSRF-exempt because the requests originate
| from Iyzico (server-to-server) and from the user's browser after a
| cross-domain 3DS redirect.
|
| CSRF exemption is configured via the VerifyCsrfToken middleware's
| `$except` array — see bootstrap/app.php (Laravel 12 routeMiddleware
| pattern) if you need to update it.  For Phase 2 the module ships its
| routes as `api` group which is already csrf-exempt by default.
*/

Route::prefix('api/v1/payments/iyzico')
    ->middleware(['api'])
    ->name('payments.iyzico.')
    ->group(function (): void {

        Route::post('callback', [IyzicoWebhookController::class, 'callback'])
            ->name('callback');

        Route::post('webhook', [IyzicoWebhookController::class, 'webhook'])
            ->name('webhook');
    });
