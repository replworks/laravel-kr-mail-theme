<?php

use Illuminate\Support\Facades\Config;

it('keeps other flat keys when one service config key overrides', function () {
    Config::set('kr-mail-theme.service_name', 'Default Service');
    Config::set('kr-mail-theme.company_name', 'Default Company');

    $service = ['service_name' => 'My Service'];
    $merged = array_merge(config('kr-mail-theme'), $service);

    expect($merged['service_name'])->toBe('My Service')
        ->and($merged['company_name'])->toBe('Default Company');
});
