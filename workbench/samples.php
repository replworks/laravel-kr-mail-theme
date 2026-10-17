<?php

return [
    'brand' => [
        'service_name' => '위피노트',
        'logo' => 'https://example.com/brand/logo.png',
        'company_name' => '(주)위피컴퍼니',
        'customer_center' => '1588-0000',
        'business_registration_number' => '123-45-67890',
        'address' => '서울특별시 강남구 테헤란로 1',
        'terms_url' => 'https://example.com/terms',
    ],

    'user_identifier' => 'sample-user-1',

    'recipient_name' => '홍길동',

    'view_namespace' => 'workbench',

    'views_path' => __DIR__.'/resources/views',

    'bodies' => [
        'receipt' => 'bodies.receipt',
        'button' => 'bodies.button',
        'long' => 'bodies.long',
        'quote' => 'bodies.quote',
    ],
];
