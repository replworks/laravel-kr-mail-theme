<?php

use Illuminate\Support\Facades\Event;
use Replworks\KrMailTheme\Unsubscribe\Unsubscribed;

it('carries the Recipient User Identifier of the Unsubscribe request', function () {
    $event = new Unsubscribed('user-1');

    expect($event->userIdentifier)->toBe('user-1');
});

it('is dispatchable and reaches a listener with the same identifier', function () {
    Event::fake([Unsubscribed::class]);

    event(new Unsubscribed('user-1'));

    Event::assertDispatched(Unsubscribed::class, fn (Unsubscribed $event) => $event->userIdentifier === 'user-1');
});
