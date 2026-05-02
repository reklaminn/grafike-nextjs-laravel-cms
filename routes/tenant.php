<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\UseSiteHostHeader;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
| All public-facing routes (frontend + public API) run inside this group.
| stancl resolves the tenant from the incoming domain, switches the DB
| connection, and every Eloquent call reads from that tenant's database.
|
| Admin routes remain on the central domain (routes/admin.php) and use
| tenancy()->initialize($slug) explicitly after admin login + site selection.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'web',
    UseSiteHostHeader::class,
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(base_path('routes/tenant_web.php'));
