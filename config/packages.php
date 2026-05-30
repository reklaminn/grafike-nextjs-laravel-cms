<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Packages (hosting/abonelik paketleri)
    |--------------------------------------------------------------------------
    |
    | Her tenant bir pakete sahiptir (tenants.data.package). Paket; izin verilen
    | yönetici kullanıcı sayısını (max_users), AI kullanım planını (ai_plan →
    | config/ai.php plans) ve ileride storage/modül yetkilerini belirler.
    |
    | max_users / max_storage_mb = null → sınırsız.
    |
    */

    'default' => env('TENANT_DEFAULT_PACKAGE', 'basic'),

    'packages' => [
        'basic' => [
            'label'          => 'Temel',
            'ai_plan'        => 'free',
            'max_users'      => 1,
            'max_storage_mb' => 500,
            'modules'        => [],
        ],
        'standard' => [
            'label'          => 'Standart',
            'ai_plan'        => 'starter',
            'max_users'      => 3,
            'max_storage_mb' => 2048,
            'modules'        => [],
        ],
        'pro' => [
            'label'          => 'Profesyonel',
            'ai_plan'        => 'pro',
            'max_users'      => 10,
            'max_storage_mb' => 10240,
            'modules'        => ['tours', 'commerce'],
        ],
        'enterprise' => [
            'label'          => 'Kurumsal',
            'ai_plan'        => 'enterprise',
            'max_users'      => null, // sınırsız
            'max_storage_mb' => null,
            'modules'        => ['tours', 'commerce'],
        ],
    ],
];
