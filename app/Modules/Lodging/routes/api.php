<?php

declare(strict_types=1);

use App\Modules\Lodging\Http\Controllers\Api\ReservationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Lodging module — tenant API routes
|--------------------------------------------------------------------------
|
| Loaded UNCONDITIONALLY by LodgingModuleServiceProvider::boot() (route:cache
| uyumu). Sarmalayan middleware yığını orada tanımlı:
|   UseSiteHostHeader → InitializeTenancyForPublicApi → MeterTenantUsage
|   → tenant.module.active:lodging  (modülsüz tenant → 404)
| Bu yüzden burada yalnızca prefix + isim + rota-özel throttle kalır.
*/

Route::prefix('api/v1/lodging')
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
