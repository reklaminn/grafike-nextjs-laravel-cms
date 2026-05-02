<?php

use App\Http\Controllers\TenantAssetController;
use App\Http\Middleware\InitializeTenancyForPublicApi;
use App\Http\Middleware\UseSiteHostHeader;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Web Routes
|--------------------------------------------------------------------------
| Routes here are served from the CENTRAL domain only (admin panel, etc.).
| Frontend and public API routes live in routes/tenant_web.php and
| routes/tenant_api.php — they are loaded by TenancyServiceProvider
| with the InitializeTenancyByDomain middleware attached.
|--------------------------------------------------------------------------
*/

// Health check (already defined via withRouting health: '/up')
// Admin routes are in routes/admin.php (loaded via withRouting->then callback)

Route::middleware([
    UseSiteHostHeader::class,
    InitializeTenancyForPublicApi::class,
])
    ->get('/tenant-assets/{path}', [TenantAssetController::class, 'show'])
    ->where('path', '.*')
    ->name('tenant.assets.show');

// Root redirect — central domain has no public-facing page; send visitors to the admin panel.
Route::redirect('/', '/admin', 301);
