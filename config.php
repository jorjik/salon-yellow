<?php
declare(strict_types=1);

/**
 * Настройки лендинга салона. Меняйте только здесь.
 */
return [
    'site' => [
        /** Публичный URL папки сайта с https, со слэшем в конце */
        'url' => 'http://d12rszpvzfxptckzr22kbwge.157.173.111.175.sslip.io/',
        'title' => 'VELVET — Салон красоты',
        'description' => 'Салон красоты VELVET: стрижки, окрашивание, маникюр, уход и макияж. Запишитесь онлайн со скидкой 15% на первый визит.',
        'name_short' => 'VELVET',
        'name_legal' => 'VELVET Beauty Studio',
    ],

    'phone' => [
        /** Только цифры, код страны без + (WhatsApp / сборка tel:) */
        'digits' => '77001234567',
        'display' => '+7 (700) 123-45-67',
        /** Для schema.org telephone */
        'e164' => '+77001234567',
    ],

    'whatsapp' => [
        'message' => 'Здравствуйте! Хочу записаться в салон VELVET.',
    ],

    /** Абсолютный URL картинки 1200×630 для соцсетей */
    'og_image' => 'https://placehold.co/1200x630/ffb300/ffffff/png?text=VELVET',

    'salon' => [
        'schema_description' => 'Салон красоты: стрижки, окрашивание, маникюр, уход за лицом и макияж.',
        'address_line' => 'г. Алматы, ул. Абая, 150/230',
        'address_street' => 'ул. Абая, 150/230',
        'address_city' => 'Алматы',
        'address_country' => 'KZ',
        'opens' => '09:00',
        'closes' => '21:00',
        'hours_label' => 'ежедневно 09:00 - 21:00',
        'map_lat' => '43.238949',
        'map_lng' => '76.889709',
        'map_zoom' => '14',
        /** Кнопка «Открыть в Google Maps» в отзывах */
        'google_maps_url' => 'https://maps.google.com',
    ],

    'mail' => [
        'to' => 'you@example.com',
        'subject' => 'VELVET: заявка с сайта',
        'from_label' => 'VELVET Site',
    ],

    'footer' => [
        'year' => 2026,
    ],
];
