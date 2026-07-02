<?php

declare(strict_types=1);

use App\Modules\Lodging\Http\Controllers\Api\ReservationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Lodging module — tenant API routes
|--------------------------------------------------------------------------
|
| Loaded by LodgingModuleServiceProvider::bootTenant(), so reachable only on
| tenants with the `lodging` module enabled.  Tenant resolution
| (?tenant={id} / X-Tenant-ID) + MeterTenantUsage run in the parent `api`
| group before we get here.
*/

Route::prefix('api/v1/lodging')
    ->middleware(['api'])
    ->name('lodging.')
    ->group(function (): void {

        // Active room types (price, capacity, images).
        Route::get('room-types', [ReservationController::class, 'roomTypes'])
            ->name('room-types');

        // Booked nights for a room type in a window → { booked: ["Y-m-d", …] }.
        Route::get('availability', [ReservationController::class, 'availability'])
            ->name('availability');

        // Create a reservation request (honeypot + Turnstile + throttle).
        Route::post('requests', [ReservationController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('requests.store');
    });
