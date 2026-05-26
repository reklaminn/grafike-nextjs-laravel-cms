<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AiServiceProvider::class,
    App\Providers\TenancyServiceProvider::class,

    /*
    |--------------------------------------------------------------------------
    | Vertical modules (config/tenant_modules.php)
    |--------------------------------------------------------------------------
    |
    | Each provider class is autoloaded unconditionally so its event
    | listeners can wire in.  The provider itself is responsible for
    | gating runtime registration on `Tenant::hasModule($slug)` — central
    | / unrelated tenants pay zero cost beyond a few microsecond event hits.
    */
    App\Modules\Payments\Providers\PaymentsModuleServiceProvider::class,
    App\Modules\Tours\Providers\ToursModuleServiceProvider::class,
    App\Modules\Commerce\Providers\CommerceModuleServiceProvider::class,
];
