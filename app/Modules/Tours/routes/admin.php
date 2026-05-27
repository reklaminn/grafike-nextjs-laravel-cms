<?php

declare(strict_types=1);

use App\Modules\Tours\Http\Controllers\Admin\BookingController;
use App\Modules\Tours\Http\Controllers\Admin\CabinCategoryController;
use App\Modules\Tours\Http\Controllers\Admin\CabinController;
use App\Modules\Tours\Http\Controllers\Admin\CabinGroupController;
use App\Modules\Tours\Http\Controllers\Admin\DestinationController;
use App\Modules\Tours\Http\Controllers\Admin\PortController;
use App\Modules\Tours\Http\Controllers\Admin\ShipCompanyController;
use App\Modules\Tours\Http\Controllers\Admin\ShipController;
use App\Modules\Tours\Http\Controllers\Admin\TourCategoryController;
use App\Modules\Tours\Http\Controllers\Admin\TourController;
use App\Modules\Tours\Http\Controllers\Admin\TourDateController;
use App\Modules\Tours\Http\Controllers\Admin\TourTagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tours module — admin routes
|--------------------------------------------------------------------------
|
| Loaded unconditionally by ToursModuleServiceProvider::boot() so admin
| route resolution works in central context (no tenant request to gate
| against).  Per-request gating happens via:
|
|   1. admin.auth         — admin must be logged in
|   2. tenant.admin       — InitializeTenancyForAdmin runs (sets tenant
|                           DB connection from session)
|   3. tenant.module:tours — RequireTenantModule checks the active
|                           tenant has the 'tours' module enabled,
|                           returns 404 otherwise (no information leak)
|
| Route names live under `admin.*` to match the rest of the admin
| ecosystem (sidebar nav matches by `str_starts_with('admin.tours.…')`).
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['web', 'admin.auth', 'tenant.admin', 'tenant.module:tours'])
    ->group(function (): void {

        // Tour CRUD (resource-style)
        Route::resource('tours', TourController::class)->except(['show']);

        // Nested departures under a tour
        Route::resource('tours.dates', TourDateController::class)
            ->parameters(['dates' => 'date'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        // Categories (flat-ish under admin)
        Route::resource('tour-categories', TourCategoryController::class)->except(['show']);

        // Marketing tags (Phase 1.5.d — Tab 1'deki tour seçenekleri)
        Route::resource('tour-tags', TourTagController::class)->except(['show']);

        // ─── Cruise Yönetimi master CRUD'ları (Phase 1.5.d) ───────────────
        // Master entity'ler — Tours tarafından tüketilir, sidebar'da ayrı grup.

        Route::resource('cabin-categories', CabinCategoryController::class)->except(['show']);

        Route::resource('ship-companies', ShipCompanyController::class)->except(['show']);

        Route::resource('ships', ShipController::class)->except(['show']);
        Route::post('ships/{ship}/media/{mediaId}/delete',
                    [ShipController::class, 'deleteMedia'])->name('ships.media.delete');

        Route::resource('cabins', CabinController::class)->except(['show']);
        Route::post('cabins/{cabin}/media/{mediaId}/delete',
                    [CabinController::class, 'deleteMedia'])->name('cabins.media.delete');

        Route::resource('cabin-groups', CabinGroupController::class)->except(['show']);

        Route::resource('ports', PortController::class)->except(['show']);

        Route::resource('destinations', DestinationController::class)->except(['show']);

        // Bookings — readonly + cancel action + manifest export
        Route::get   ('tour-bookings',          [BookingController::class, 'index' ])->name('tour-bookings.index');
        Route::get   ('tour-bookings/{booking}',[BookingController::class, 'show'  ])->name('tour-bookings.show');
        Route::post  ('tour-bookings/{booking}/cancel',
                                                [BookingController::class, 'cancel'])->name('tour-bookings.cancel');

        // Manifest XLSX is scoped to a tour_date (not a single booking)
        Route::get   ('tour-dates/{date}/manifest',
                                                [BookingController::class, 'manifest'])->name('tour-dates.manifest');
    });
