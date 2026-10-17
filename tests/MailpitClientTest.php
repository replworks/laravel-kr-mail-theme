<?php

use Illuminate\Support\Facades\Route;
use Replworks\KrMailTheme\BrandInformation;
use Replworks\KrMailTheme\MailKind;
use Replworks\KrMailTheme\MailRenderer;
use Replworks\KrMailTheme\RenderedMail;
use Replworks\KrMailTheme\Tests\Support\MailpitClient;
use Replworks\KrMailTheme\Unsubscribe\UnsubscribeUrlGenerator;

function krMailpitClient(): MailpitClient
{
    return MailpitClient::fromEnv();
}

function krMailpitRenderedMail(): RenderedMail
{
    Route::get('/unsubscribe', fn () => 'ok')->name(UnsubscribeUrlGenerator::ROUTE_NAME);

    return (new MailRenderer(brand: BrandInformation::fromConfig([
        'service_name' => '위피노트',
        'company_name' => '위피컴퍼니',
    ])))->render(MailKind::Advertising, '이벤트 안내', '본문입니다.', 'user-1');
}

it('empties Mailpit, sends a Rendered Mail, and reads back both parts, headers, and the HTML check', function () {
    $client = krMailpitClient();

    if (! $client->reachable()) {
        $this->markTestSkipped('Mailpit is not reachable at '.$client->baseUrl());
    }

    $client->empty();

    expect($client->messages())->toBeEmpty();

    $id = $client->send(krMailpitRenderedMail());

    expect($client->html($id))->toContain('본문입니다.')
        ->and($client->text($id))->toContain('본문입니다.')
        ->and($client->text($id))->toContain('수신거부');

    $headers = $client->headers($id);

    expect($headers)->toHaveKey('List-Unsubscribe')
        ->and($headers['List-Unsubscribe'][0])->toStartWith('<http')
        ->and($headers['List-Unsubscribe-Post'][0])->toBe('List-Unsubscribe=One-Click');

    expect($client->htmlCheck($id))->toHaveKey('Warnings');
})->group('mailpit');
