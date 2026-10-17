<?php

use Replworks\KrMailTheme\Tests\Support\MailpitClient;

beforeEach(function () {
    $samples = require __DIR__.'/../../workbench/samples.php';

    foreach ($samples['brand'] as $key => $value) {
        config()->set("kr-mail-theme.{$key}", $value);
    }

    app('view')->addNamespace($samples['view_namespace'], $samples['views_path']);
    app('view')->addLocation($samples['views_path']);

    require __DIR__.'/../../workbench/routes/web.php';
});

it('delivers the rendered mail to Mailpit with an HTML part and a TEXT part (AC-014, FR-041, FR-042)', function () {
    $client = MailpitClient::fromEnv();

    if (! $client->reachable()) {
        $this->markTestSkipped('Mailpit is unreachable at '.$client->baseUrl());
    }

    $client->empty();

    $response = $this->postJson('/send', [
        'kind' => 'advertising',
        'body' => 'button',
        'recipient' => 'recipient@example.com',
    ]);

    $response->assertOk()->assertJson(['state' => 'delivered']);

    $messages = $client->messages();

    expect($messages)->toHaveCount(1);

    $id = (string) $messages[0]['ID'];

    expect($messages[0]['To'][0]['Address'])->toBe('recipient@example.com')
        ->and($messages[0]['Subject'])->toBe('(광고) 위피노트 안내');

    expect($client->text($id))->toContain('주문 내역 확인하기')
        ->and($client->text($id))->toContain('위피노트');

    expect($client->html($id))->toContain('@media (prefers-color-scheme: dark)')
        ->and($client->html($id))->toContain('위피노트')
        ->and($client->html($id))->not->toContain('color-scheme: light;');

    expect($client->headers($id))->toHaveKey('List-Unsubscribe');
});

it('delivers an Informational Mail without the advertising headers (FR-041)', function () {
    $client = MailpitClient::fromEnv();

    if (! $client->reachable()) {
        $this->markTestSkipped('Mailpit is unreachable at '.$client->baseUrl());
    }

    $client->empty();

    $this->postJson('/send', [
        'kind' => 'informational',
        'body' => 'long',
        'recipient' => 'recipient@example.com',
    ])->assertOk()->assertJson(['state' => 'delivered']);

    $messages = $client->messages();

    expect($messages[0]['Subject'])->toBe('위피노트 안내');

    $headers = $client->headers((string) $messages[0]['ID']);

    expect($headers)->not->toHaveKey('List-Unsubscribe')
        ->and($headers)->not->toHaveKey('List-Unsubscribe-Post');
});

it('shows failed with ERR-005 when Mailpit is unreachable and does not retry (AC-015, ERR-005)', function () {
    putenv('MAILPIT_SMTP=smtp://127.0.0.1:1');

    try {
        $response = $this->postJson('/send', [
            'kind' => 'informational',
            'body' => 'receipt',
            'recipient' => 'recipient@example.com',
        ]);
    } finally {
        putenv('MAILPIT_SMTP');
    }

    $response->assertOk()->assertJson(['state' => 'failed', 'error' => 'ERR-005']);
});

it('rejects an invalid EL-008 value with ERR-006 (ERR-006)', function () {
    $response = $this->postJson('/send', [
        'kind' => 'informational',
        'body' => 'receipt',
        'recipient' => 'not-an-address',
    ]);

    $response->assertOk()->assertJson(['state' => 'failed', 'error' => 'ERR-006']);
});

it('serves the SCR-001 page with a CSRF token for the delivery request (EL-009)', function () {
    $this->get('/')->assertOk()->assertSee('name="csrf-token"', false);
});

it('keeps EL-009 disabled for an empty or invalid address (ERR-006)', function () {
    expect(file_get_contents(__DIR__.'/../../workbench/resources/js/scr-001.js'))
        ->toContain('el.send.disabled = busy || !addressIsValid();')
        ->toContain("el.send.addEventListener('click', send);");
});
