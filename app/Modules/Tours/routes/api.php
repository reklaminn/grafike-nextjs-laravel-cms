<?php

declare(strict_types=1);

use App\Modules\Tours\Http\Controllers\Api\BookingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tours module — tenant API routes
|--------------------------------------------------------------------------
|
| Loaded by ToursModuleServiceProvider::bootTenant() — so these routes
| are reachable only on tenants that have the `tours` module enabled.
| Tenant resolution + module gating both happen before we get here, so
| every controller can trust tenancy() is initialised and the right
| tenant DB is selected.
*/

Route::prefix('api/v1/tours')
    ->middleware(['api'])
    ->name('tours.')
    ->group(function (): void {

        // Read-only quote preview — no booking is persisted.
        Route::post('quote', [BookingController::class, 'quote'])
            ->name('quote');

        // Create reservation (capacity held).
        Route::post('bookings', [BookingController::class, 'store'])
            ->name('bookings.store');

        // Resume payment by booking_ref.
        Route::get('bookings/{ref}', [BookingController::class, 'show'])
            ->where('ref', 'TUR-[0-9]{4}-[A-Z0-9]{6}')
            ->name('bookings.show');
    });
