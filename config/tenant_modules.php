<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Tenant Vertical Modules Registry
|--------------------------------------------------------------------------
|
| iraspa-cms ships a "core" CMS (pages, sections, themes, SEO, members,
| forms) that every tenant always gets.  Verticals beyond that — turizm
| satış (tours), e-ticaret (commerce), shared ödeme altyapısı (payments) —
| live in `app/Modules/{Module}/` and are opt-in per tenant.
|
| NOTE: A separate `config/modules.php` exists for the LEGACY section
| widget renderer (Yan Menu, İçerik Bloğu, …) — that's a different concept
| (per-section component registry) and we do not touch it here.
|
| A tenant's enabled modules are stored under `Tenant.data['modules']` as
| an array of slugs (matching keys in this file).  Empty array (default
| for existing tenants) means "core only" — full backward compatibility.
|
| Consumed by:
|   - App\Services\Modules\ModuleRegistry  (lookups, dependency resolution)
|   - App\Services\Modules\ModuleManager   (install / uninstall orchestration)
|   - app/Console/Commands/Tenant/Module*  (artisan UX)
|   - App\Modules\*\Providers\*ServiceProvider  (boot-time module gating)
|   - App\Http\Controllers\Api\SiteController  (exposes modules to Next.js)
|   - resources/views/admin/tenants/_modules-panel.blade.php  (admin UI)
|
| Adding a new vertical (e.g. "rentals"):
|   1. Create app/Modules/Rentals/{Providers,Models,...} structure
|   2. Add an entry below with label, migrations_path, service_provider
|   3. Register the ServiceProvider in bootstrap/providers.php
|   4. Run `php artisan tenant:module:install rentals --tenant=<id>`
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Module Definitions
    |--------------------------------------------------------------------------
    |
    | Each key is the slug used in Tenant.data['modules'] and in the
    | tenant:module:* artisan commands.  Required fields:
    |
    |   label            — Turkish display name shown in admin UI
    |   description      — one-line explainer for the admin checkbox
    |   service_provider — fully-qualified provider class (also in providers.php)
    |   migrations_path  — relative path from base_path(); empty = no migrations
    |   requires         — slugs of modules that must be enabled first
    |   user_installable — false hides from admin UI (internal / dependency-only)
    |
    */

    'definitions' => [

        'payments' => [
            'label'            => 'Ödeme Altyapısı',
            'description'      => 'Paylaşılan ödeme gateway katmanı (Iyzico, ileride Stripe / PayTR). Tours ve Commerce tarafından otomatik kullanılır.',
            'service_provider' => \App\Modules\Payments\Providers\PaymentsModuleServiceProvider::class,
            'migrations_path'  => 'app/Modules/Payments/Database/migrations',
            'requires'         => [],
            'user_installable' => false,
        ],

        'tours' => [
            'label'            => 'Turlar (Cruise / Paket / Günlük)',
            'description'      => 'Tur kataloğu, departure takvimi, rezervasyon ve voucher yönetimi. Cruise gemisi, çoklu-günlük paket tur ve günlük tur tek modülde alt-tip olarak modellenir.',
            'service_provider' => \App\Modules\Tours\Providers\ToursModuleServiceProvider::class,
            'migrations_path'  => 'app/Modules/Tours/Database/migrations',
            'requires'         => ['payments'],
            'user_installable' => true,
        ],

        'commerce' => [
            'label'            => 'E-Ticaret',
            'description'      => 'Ürün kataloğu, sepet, sipariş, kargo entegrasyonu. (Phase 6 — bu modül henüz aktif değil.)',
            'service_provider' => \App\Modules\Commerce\Providers\CommerceModuleServiceProvider::class,
            'migrations_path'  => 'app/Modules/Commerce/Database/migrations',
            'requires'         => ['payments'],
            'user_installable' => false, // Phase 6'da true yapılacak
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Always-On Core
    |--------------------------------------------------------------------------
    |
    | `core` is implicit and cannot be disabled — it represents the
    | "kurumsal" CMS feature set (pages, sections, themes, SEO, members,
    | forms).  Listed here only for UI display purposes.
    |
    */

    'core' => [
        'label'       => 'Kurumsal CMS (Core)',
        'description' => 'Sayfa builder, section\'lar, tema, SEO, üyelik, formlar. Her tenant\'ta her zaman aktif.',
    ],

];
