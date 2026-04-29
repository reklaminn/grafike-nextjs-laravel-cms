<?php

return [
    'name' => env('CMS_NAME', 'Grafike CMS'),
    'frontend_url'      => env('CMS_FRONTEND_URL', 'http://127.0.0.1:3000'),
    'revalidate_secret' => env('CMS_REVALIDATE_SECRET', ''),
    'version' => '2.0.0',
    'default_language' => env('CMS_DEFAULT_LANGUAGE', 'tr'),
    'default_language_id' => env('CMS_DEFAULT_LANGUAGE_ID', 1),
    'homepage_id' => env('CMS_HOMEPAGE_ID', 835),
    'cdn_url' => env('CMS_CDN_URL', ''),
    'admin_prefix' => env('CMS_ADMIN_PREFIX', 'admin'),
    'uploads_path' => env('CMS_UPLOADS_PATH', 'uploads'),

    'cache' => [
        'enabled' => env('CMS_CACHE_ENABLED', true),
        'ttl' => env('CMS_CACHE_TTL', 600),
        'prefix' => 'cms_',
    ],

    'seo' => [
        'default_title_suffix' => env('CMS_TITLE_SUFFIX', ''),
        'default_description' => '',
        'enable_sitemap' => true,
        'enable_hreflang' => true,
    ],

    'recaptcha' => [
        'enabled' => env('RECAPTCHA_ENABLED', false),
        'site_key' => env('RECAPTCHA_SITE_KEY', ''),
        'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
    ],

    'social' => [
        'facebook_app_id' => env('FACEBOOK_APP_ID', ''),
        'twitter_username' => env('TWITTER_USERNAME', ''),
        'instagram_username' => env('INSTAGRAM_USERNAME', ''),
        'whatsapp_number' => env('WHATSAPP_NUMBER', ''),
    ],

    'analytics' => [
        'google_analytics_id' => env('GOOGLE_ANALYTICS_ID', ''),
        'google_tag_manager_id' => env('GOOGLE_TAG_MANAGER_ID', ''),
    ],

    'media' => [
        'max_upload_size' => 10240,
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'],
        'image_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'thumbnail_sizes' => [
            'small' => [150, 150],
            'medium' => [400, 300],
            'large' => [800, 600],
        ],
    ],
];
