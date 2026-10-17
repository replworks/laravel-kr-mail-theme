<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Replworks\KrMailTheme\Unsubscribe\ConsentRepository;
use Replworks\KrMailTheme\Unsubscribe\Unsubscribed;
use Replworks\KrMailTheme\Unsubscribe\UnsubscribeUrlGenerator;

uses(RefreshDatabase::class);

function krUnsubUrl(string $userIdentifier): string
{
    return UnsubscribeUrlGenerator::generate($userIdentifier);
}

function krUnsubTampered(string $url): string
{
    return str_replace('user=user-1', 'user=user-2', $url);
}

function krUnsubConsent(string $userIdentifier): mixed
{
    return DB::table(ConsentRepository::TABLE)->where('user_identifier', $userIdentifier)->value('marketing_consent');
}

function krUnsubSeedTrue(string $userIdentifier): void
{
    DB::table(ConsentRepository::TABLE)->insert([
        'user_identifier' => $userIdentifier,
        'marketing_consent' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('processes a valid GET immediately, without login, and shows SCR-002 Success (FR-043, FR-045, FR-046, FR-052, AC-016)', function () {
    $response = $this->get(krUnsubUrl('user-1'));

    $response->assertOk()
        ->assertSee('data-state="success"', false)
        ->assertSee('id="el-012"', false)
        ->assertDontSee('signature=', false);

    expect(krUnsubConsent('user-1'))->not->toBeNull()
        ->and((bool) krUnsubConsent('user-1'))->toBeFalse();
});

it('shows SCR-002 Success on GET even when the value is already false (FR-052, AC-016)', function () {
    DB::table(ConsentRepository::TABLE)->insert([
        'user_identifier' => 'user-1',
        'marketing_consent' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->get(krUnsubUrl('user-1'));

    $response->assertOk()->assertSee('data-state="success"', false);

    expect((bool) krUnsubConsent('user-1'))->toBeFalse();
});

it('processes a valid POST with the one-click body and returns a success status (FR-044, FR-053, AC-017)', function () {
    $response = $this->post(krUnsubUrl('user-1'), ['List-Unsubscribe' => 'One-Click']);

    $response->assertOk();

    expect((bool) krUnsubConsent('user-1'))->toBeFalse();
});

it('accepts a valid POST without the one-click body (FR-046)', function () {
    $response = $this->post(krUnsubUrl('user-1'));

    $response->assertOk();

    expect((bool) krUnsubConsent('user-1'))->toBeFalse();
});

it('fires the Unsubscribe Event once on the first valid request and not on a repeat (FR-050, FR-051, AC-018)', function () {
    Event::fake([Unsubscribed::class]);

    $url = krUnsubUrl('user-1');

    $this->get($url)->assertOk();
    $this->get($url)->assertOk();

    Event::assertDispatchedTimes(Unsubscribed::class, 1);
    Event::assertDispatched(Unsubscribed::class, fn (Unsubscribed $event) => $event->userIdentifier === 'user-1');
});

it('shows SCR-002 Error and changes nothing for an altered GET link (ERR-003, AC-019)', function () {
    Event::fake([Unsubscribed::class]);
    krUnsubSeedTrue('user-1');

    $response = $this->get(krUnsubTampered(krUnsubUrl('user-1')));

    $response->assertOk()->assertSee('data-state="error"', false);

    Event::assertNotDispatched(Unsubscribed::class);
    expect((bool) krUnsubConsent('user-1'))->toBeTrue()
        ->and(krUnsubConsent('user-2'))->toBeNull();
});

it('shows SCR-002 Error for an unsigned GET link (ERR-003, AC-019)', function () {
    Event::fake([Unsubscribed::class]);
    krUnsubSeedTrue('user-1');

    $response = $this->get('/unsubscribe?user=user-1');

    $response->assertOk()->assertSee('data-state="error"', false);

    Event::assertNotDispatched(Unsubscribed::class);
    expect((bool) krUnsubConsent('user-1'))->toBeTrue();
});

it('returns a non-success status and changes nothing for an altered POST link (ERR-003, AC-019)', function () {
    Event::fake([Unsubscribed::class]);
    krUnsubSeedTrue('user-1');

    $response = $this->post(krUnsubTampered(krUnsubUrl('user-1')), ['List-Unsubscribe' => 'One-Click']);

    $response->assertForbidden();

    Event::assertNotDispatched(Unsubscribed::class);
    expect((bool) krUnsubConsent('user-1'))->toBeTrue();
});

it('returns a non-success status for an unsigned POST link (ERR-003, AC-019)', function () {
    Event::fake([Unsubscribed::class]);

    $response = $this->post('/unsubscribe?user=user-1', ['List-Unsubscribe' => 'One-Click']);

    $response->assertForbidden();

    Event::assertNotDispatched(Unsubscribed::class);
});
