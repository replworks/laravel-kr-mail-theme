<?php

namespace Replworks\KrMailTheme;

final class SubjectFormatter
{
    public const ADVERTISING_PREFIX = '(광고) ';

    public static function format(MailKind $kind, string $subject): string
    {
        if ($kind !== MailKind::Advertising) {
            return $subject;
        }

        if (str_starts_with($subject, '(광고)')) {
            return $subject;
        }

        return self::ADVERTISING_PREFIX.$subject;
    }
}
