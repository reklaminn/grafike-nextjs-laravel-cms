<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\InitializeTenancyForPublicApi;
use App\Http\Middleware\UseSiteHostHeader;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Public API routes are loaded here so the central preview URL can resolve
| tenants from X-Tenant-ID before falling back to normal domain resolution.
|
| Central/admin API endpoints that don't require tenant context can be added
| above this group with a more specific prefix.
|--------------------------------------------------------------------------
*/

Route::middleware([
    UseSiteHostHeader::class,
    InitializeTenancyForPublicApi::class,
])->group(base_path('routes/tenant_api.php'));
