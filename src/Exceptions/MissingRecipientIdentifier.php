<?php

namespace Replworks\KrMailTheme\Exceptions;

use RuntimeException;

final class MissingRecipientIdentifier extends RuntimeException
{
    public static function forAdvertisingMail(): self
    {
        return new self('Advertising Mail requires a Recipient User Identifier.');
    }
}
