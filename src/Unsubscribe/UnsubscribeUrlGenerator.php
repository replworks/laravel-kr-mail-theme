<?php

namespace Replworks\KrMailTheme\Unsubscribe;

use Illuminate\Support\Facades\URL;

final class UnsubscribeUrlGenerator
{
    public const ROUTE_NAME = 'kr-mail-theme.unsubscribe';

    public static function generate(string $userIdentifier): string
    {
        return URL::signedRoute(self::ROUTE_NAME, ['user' => $userIdentifier]);
    }
}
