<?php

it('declares every Brand Information field as a flat top-level key (AC-013)', function () {
    $config = require dirname(__DIR__, 2).'/config/kr-mail-theme.php';

    expect($config)->toHaveKeys([
        'service_name',
        'logo',
        'company_name',
        'customer_center',
        'business_registration_number',
        'address',
        'terms_url',
        'unsubscribe_path',
    ]);
});

it('defaults the Unsubscribe Path to unsubscribe', function () {
    putenv('KR_MAIL_UNSUBSCRIBE_PATH');
    unset($_ENV['KR_MAIL_UNSUBSCRIBE_PATH'], $_SERVER['KR_MAIL_UNSUBSCRIBE_PATH']);

    $config = require dirname(__DIR__, 2).'/config/kr-mail-theme.php';

    expect($config['unsubscribe_path'])->toBe('unsubscribe');
});

it('lets a Service config value win over an environment value and keeps other keys (FR-040)', function () {
    $env = [
        'KR_MAIL_SERVICE_NAME' => '환경 서비스',
        'KR_MAIL_COMPANY_NAME' => '환경 회사',
    ];

    foreach ($env as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    try {
        $packageDefaults = require dirname(__DIR__, 2).'/config/kr-mail-theme.php';
        $serviceConfig = ['service_name' => '서비스 설정'];

        // Laravel merges a Service config file over the Package defaults with a shallow array_merge.
        $merged = array_merge($packageDefaults, $serviceConfig);

        expect($merged['service_name'])->toBe('서비스 설정')
            ->and($merged['company_name'])->toBe('환경 회사')
            ->and($merged['unsubscribe_path'])->toBe('unsubscribe');
    } finally {
        foreach (array_keys($env) as $key) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }
    }
});
