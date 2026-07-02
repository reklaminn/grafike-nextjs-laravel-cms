<?php

declare(strict_types=1);

use App\Modules\Lodging\Http\Controllers\Admin\AvailabilityController;
use App\Modules\Lodging\Http\Controllers\Admin\ReservationController;
use App\Modules\Lodging\Http\Controllers\Admin\RoomTypeController;
use App\Modules\Lodging\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Lodging module — admin routes
|--------------------------------------------------------------------------
|
| Loaded unconditionally by LodgingModuleServiceProvider::boot().  Per-request
| gating:
|   1. admin.auth            — admin logged in
|   2. tenant.admin          — InitializeTenancyForAdmin (tenant DB from session)
|   3. tenant.module:lodging — RequireTenantModule → 404 if module disabled
|
| Route names live under `admin.lodging.*` (sidebar matches by prefix).
*/

Route::prefix('admin/lodging')
    ->name('admin.lodging.')
    ->middleware(['web', 'admin.auth', 'tenant.admin', 'tenant.module:lodging'])
    ->group(function (): void {

        // Room types (Oda Tipleri) — CRUD + image delete
        Route::resource('room-types', RoomTypeController::class)->except(['show']);
        Route::post('room-types/{room_type}/media/{mediaId}/delete',
                    [RoomTypeController::class, 'deleteMedia'])->name('room-types.media.delete');

        // Availability (Müsaitlik) — pick room type, add/remove blocked ranges
        Route::get   ('availability',               [AvailabilityController::class, 'index'  ])->name('availability.index');
        Route::post  ('availability',               [AvailabilityController::class, 'store'  ])->name('availability.store');
        Route::delete('availability/{availability}',[AvailabilityController::class, 'destroy'])->name('availability.destroy');

        // Reservations (Rezervasyonlar — gelen kutusu)
        Route::get ('reservations',                    [ReservationController::class, 'index'  ])->name('reservations.index');
        Route::get ('reservations/{reservation}',      [ReservationController::class, 'show'   ])->name('reservations.show');
        Route::post('reservations/{reservation}/confirm',[ReservationController::class, 'confirm'])->name('reservations.confirm');
        Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel' ])->name('reservations.cancel');
        Route::post('reservations/{reservation}/note',   [ReservationController::class, 'note'   ])->name('reservations.note');
        Route::get ('reservations-export',             [ReservationController::class, 'export' ])->name('reservations.export');

        // Settings (Ayarlar)
        Route::get('settings', [SettingController::class, 'edit'  ])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
