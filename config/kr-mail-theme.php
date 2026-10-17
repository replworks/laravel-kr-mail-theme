<?php

return [
    'service_name' => env('KR_MAIL_SERVICE_NAME'),
    'logo' => env('KR_MAIL_LOGO_URL'),
    'company_name' => env('KR_MAIL_COMPANY_NAME'),
    'customer_center' => env('KR_MAIL_CUSTOMER_CENTER'),
    'business_registration_number' => env('KR_MAIL_BUSINESS_REGISTRATION_NUMBER'),
    'address' => env('KR_MAIL_ADDRESS'),
    'terms_url' => env('KR_MAIL_TERMS_URL'),
    'unsubscribe_path' => env('KR_MAIL_UNSUBSCRIBE_PATH', 'unsubscribe'),
];
