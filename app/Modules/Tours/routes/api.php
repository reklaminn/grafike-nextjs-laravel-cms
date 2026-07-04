<?php

declare(strict_types=1);

use App\Modules\Tours\Http\Controllers\Api\BookingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tours module — tenant API routes
|--------------------------------------------------------------------------
|
| Loaded UNCONDITIONALLY by ToursModuleServiceProvider::boot() (route:cache
| uyumu). Sarmalayan middleware yığını orada tanımlı:
|   UseSiteHostHeader → InitializeTenancyForPublicApi → MeterTenantUsage
|   → tenant.module.active:tours  (modülsüz tenant → 404)
| Controller'lar tenancy() initialise + doğru tenant DB'ye güvenebilir.
*/

Route::prefix('api/v1/tours')
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
