<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Ship Facilities Master
|--------------------------------------------------------------------------
|
| Eski tur sisteminin "Tab 2 — İmkanlar" checkbox listesinden alındı.
| 15 standart cruise / ferry facility slug'ı.  Ship.facilities JSON
| column'unda bunlardan seçim tutulur.
|
| Yeni facility eklemek: buraya satır ekle + label translations dosyasına
| TR/EN ekle.  Migration gerekmez (JSON column şemaya bağlı değil).
|
| Frontend render: tour detay sayfasında ikon grid olarak gösterilir.
|
*/

return [
    'available' => [

        'betting'         => ['icon' => '🎲', 'sort_order' => 1],
        'bar'             => ['icon' => '🍹', 'sort_order' => 2],
        'bowling'         => ['icon' => '🎳', 'sort_order' => 3],
        'casino'          => ['icon' => '🎰', 'sort_order' => 4],
        'natural_scenery' => ['icon' => '🌄', 'sort_order' => 5],
        'entertainment'   => ['icon' => '🎭', 'sort_order' => 6],
        'pool'            => ['icon' => '🏊', 'sort_order' => 7],
        'air_conditioning' => ['icon' => '❄️', 'sort_order' => 8],
        'library'         => ['icon' => '📚', 'sort_order' => 9],
        'cinema'          => ['icon' => '🎬', 'sort_order' => 10],
        'sports'          => ['icon' => '⚽', 'sort_order' => 11],
        'wifi'            => ['icon' => '📶', 'sort_order' => 12],
        'dining_room'     => ['icon' => '🍽', 'sort_order' => 13],
        'dining_options'  => ['icon' => '🥘', 'sort_order' => 14],
        'other'           => ['icon' => '✨', 'sort_order' => 15],

    ],

    /*
    |--------------------------------------------------------------------------
    | Display Labels (per language)
    |--------------------------------------------------------------------------
    | Eski sistemden birebir gelen Turkish labels + English çeviri.
    */
    'labels' => [
        'tr' => [
            'betting'          => 'Bahis Tesisleri',
            'bar'              => 'Bar Özellikleri',
            'bowling'          => 'Bowling Tesisleri',
            'casino'           => 'Casino Tesisleri',
            'natural_scenery'  => 'Doğal Senaryo Olanakları',
            'entertainment'    => 'Eğlence Tesisleri',
            'pool'             => 'Havuz Olanakları',
            'air_conditioning' => 'Klima',
            'library'          => 'Kütüphane Olanakları',
            'cinema'           => 'Sinema Tesisleri',
            'sports'           => 'Spor Tesisleri',
            'wifi'             => 'WiFi Tesisatı',
            'dining_room'      => 'Yemek Odası',
            'dining_options'   => 'Yemek Olanakları',
            'other'            => 'Diğer İmkanlar',
        ],
        'en' => [
            'betting'          => 'Betting Facilities',
            'bar'              => 'Bar Features',
            'bowling'          => 'Bowling',
            'casino'           => 'Casino',
            'natural_scenery'  => 'Natural Scenery',
            'entertainment'    => 'Entertainment',
            'pool'             => 'Swimming Pool',
            'air_conditioning' => 'Air Conditioning',
            'library'          => 'Library',
            'cinema'           => 'Cinema',
            'sports'           => 'Sports Facilities',
            'wifi'             => 'WiFi',
            'dining_room'      => 'Dining Room',
            'dining_options'   => 'Dining Options',
            'other'            => 'Other Amenities',
        ],
    ],
];
