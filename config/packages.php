<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Packages (hosting/abonelik paketleri)
    |--------------------------------------------------------------------------
    |
    | Her tenant bir pakete sahiptir (tenants.data.package). Paket; izin verilen
    | yönetici kullanıcı sayısını (max_users), AI kullanım planını (ai_plan →
    | config/ai.php plans), depolama kotasını (max_storage_mb) ve günlük istek
    | limitini (max_requests_per_day) belirler.
    |
    | null → sınırsız.
    |
    | GÜVENLİK: max_requests_per_day varsayılan olarak null (kapalı) bırakıldı —
    | canlı trafiği bozmamak için. Metering (sayım) her zaman çalışır; HARD limit
    | yalnızca buraya bir değer yazıldığında devreye girer (fail-open).
    |
    */

    'default' => env('TENANT_DEFAULT_PACKAGE', 'basic'),

    'packages' => [
        'basic' => [
            'label'                => 'Temel',
            'ai_plan'              => 'free',
            'max_users'            => 1,
            'max_storage_mb'       => 500,
            'max_requests_per_day' => null,
            'modules'              => [],
        ],
        'standard' => [
            'label'                => 'Standart',
            'ai_plan'              => 'starter',
            'max_users'            => 3,
            'max_storage_mb'       => 2048,
            'max_requests_per_day' => null,
            'modules'              => [],
        ],
        'pro' => [
            'label'                => 'Profesyonel',
            'ai_plan'              => 'pro',
            'max_users'            => 10,
            'max_storage_mb'       => 10240,
            'max_requests_per_day' => null,
            'modules'              => ['tours', 'commerce'],
        ],
        'enterprise' => [
            'label'                => 'Kurumsal',
            'ai_plan'              => 'enterprise',
            'max_users'            => null,
            'max_storage_mb'       => null,
            'max_requests_per_day' => null,
            'modules'              => ['tours', 'commerce'],
        ],
    ],
];
