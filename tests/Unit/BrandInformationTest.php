<?php

use Replworks\KrMailTheme\BrandInformation;

it('treats unset values as not supplied (FR-003, FR-004, FR-008, ERR-002)', function () {
    $brand = BrandInformation::fromConfig([]);

    expect($brand->serviceName())->toBe('[서비스명]')
        ->and($brand->logo())->toBeNull()
        ->and($brand->hasLogo())->toBeFalse()
        ->and($brand->companyName())->toBeNull()
        ->and($brand->customerCenter())->toBeNull()
        ->and($brand->businessRegistrationNumber())->toBeNull()
        ->and($brand->address())->toBeNull()
        ->and($brand->termsUrl())->toBeNull();
});

it('counts empty strings as not supplied (FR-003, FR-004, ERR-002)', function () {
    $brand = BrandInformation::fromConfig([
        'service_name' => '',
        'logo' => '',
        'company_name' => '',
        'customer_center' => '',
        'business_registration_number' => '',
        'address' => '',
        'terms_url' => '',
    ]);

    expect($brand->serviceName())->toBe(BrandInformation::SERVICE_NAME_PLACEHOLDER)
        ->and($brand->logo())->toBeNull()
        ->and($brand->hasLogo())->toBeFalse()
        ->and($brand->companyName())->toBeNull()
        ->and($brand->customerCenter())->toBeNull()
        ->and($brand->businessRegistrationNumber())->toBeNull()
        ->and($brand->address())->toBeNull()
        ->and($brand->termsUrl())->toBeNull();
});

it('gives the Service Name and Logo placeholders when not supplied (FR-003, FR-004)', function () {
    expect(BrandInformation::SERVICE_NAME_PLACEHOLDER)->toBe('[서비스명]')
        ->and(BrandInformation::LOGO_PLACEHOLDER)->toBe('[로고]');

    $brand = BrandInformation::fromConfig([]);

    expect($brand->serviceName())->toBe('[서비스명]')
        ->and($brand->hasLogo())->toBeFalse();
});

it('exposes every supplied value (AC-002, AC-003)', function () {
    $brand = BrandInformation::fromConfig([
        'service_name' => '위피노트',
        'logo' => 'https://cdn.example.com/logo.png',
        'company_name' => '(주)위피',
        'customer_center' => '1588-0000',
        'business_registration_number' => '123-45-67890',
        'address' => '서울특별시 강남구 테헤란로 1',
        'terms_url' => 'https://example.com/terms',
    ]);

    expect($brand->serviceName())->toBe('위피노트')
        ->and($brand->logo())->toBe('https://cdn.example.com/logo.png')
        ->and($brand->hasLogo())->toBeTrue()
        ->and($brand->companyName())->toBe('(주)위피')
        ->and($brand->customerCenter())->toBe('1588-0000')
        ->and($brand->businessRegistrationNumber())->toBe('123-45-67890')
        ->and($brand->address())->toBe('서울특별시 강남구 테헤란로 1')
        ->and($brand->termsUrl())->toBe('https://example.com/terms');
});

it('shows values supplied through environment variables alone (FR-039)', function () {
    $env = [
        'KR_MAIL_SERVICE_NAME' => '환경 서비스',
        'KR_MAIL_LOGO_URL' => 'https://env.example.com/logo.png',
        'KR_MAIL_COMPANY_NAME' => '환경 회사',
    ];

    foreach ($env as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    try {
        $config = require dirname(__DIR__, 2).'/config/kr-mail-theme.php';
        $brand = BrandInformation::fromConfig($config);

        expect($brand->serviceName())->toBe('환경 서비스')
            ->and($brand->logo())->toBe('https://env.example.com/logo.png')
            ->and($brand->companyName())->toBe('환경 회사');
    } finally {
        foreach (array_keys($env) as $key) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }
    }
});

it('never fails when a Brand Information field is missing (FR-008, ERR-002)', function () {
    expect(fn () => BrandInformation::fromConfig([]))->not->toThrow(Throwable::class);
});
