<?php

namespace Replworks\KrMailTheme\Unsubscribe;

use Illuminate\Foundation\Events\Dispatchable;

final class Unsubscribed
{
    use Dispatchable;

    public function __construct(public readonly string $userIdentifier) {}
}
