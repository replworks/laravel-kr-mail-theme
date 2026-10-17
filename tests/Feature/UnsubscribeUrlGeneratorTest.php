<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Replworks\KrMailTheme\Unsubscribe\UnsubscribeUrlGenerator;

function krSetUnsubscribeRouteUri(string $path): void
{
    Route::getRoutes()
        ->getByName(UnsubscribeUrlGenerator::ROUTE_NAME)
        ->setUri($path);
}

it('generates a signed, absolute Unsubscribe Link from the Recipient User Identifier (FR-016, FR-017)', function () {
    $url = UnsubscribeUrlGenerator::generate('user-123');

    expect($url)->toStartWith('http')
        ->toContain('user-123')
        ->toContain('signature=');
});

it('uses the configured Unsubscribe Path (FR-018, AC-007)', function () {
    krSetUnsubscribeRouteUri('opt-out');

    $url = UnsubscribeUrlGenerator::generate('user-123');

    expect(parse_url($url, PHP_URL_PATH))->toBe('/opt-out');
});

it('detects any alteration of the URL (FR-019, AC-007)', function () {
    $url = UnsubscribeUrlGenerator::generate('user-123');

    expect(URL::hasValidSignature(Request::create($url)))->toBeTrue();

    $alteredUser = str_replace('user-123', 'user-999', $url);
    expect(URL::hasValidSignature(Request::create($alteredUser)))->toBeFalse();

    $alteredSignature = preg_replace('/signature=[^&]+/', 'signature=deadbeef', $url);
    expect(URL::hasValidSignature(Request::create($alteredSignature)))->toBeFalse();
});

it('does not set an expiration (FR-020, AC-007)', function () {
    $url = UnsubscribeUrlGenerator::generate('user-123');

    expect($url)->not->toContain('expires=')
        ->and(URL::hasValidSignature(Request::create($url)))->toBeTrue();
});
