<?php

namespace Workbench\App\Support;

use Replworks\KrMailTheme\BodyViewRenderer;
use Replworks\KrMailTheme\MailKind;
use Replworks\KrMailTheme\MailRenderer;
use Replworks\KrMailTheme\RenderedMail;

final class Preview
{
    public const SUBJECT = '위피노트 안내';

    public static function render(?string $kind, ?string $body, ?string $scheme): RenderedMail
    {
        $samples = require __DIR__.'/../../samples.php';

        $kind = match ((string) $kind) {
            'advertising' => MailKind::Advertising,
            default => MailKind::Informational,
        };
        $body = array_key_exists((string) $body, $samples['bodies']) ? (string) $body : 'receipt';
        $scheme = $scheme === 'dark' ? 'dark' : 'light';

        $markdown = (new BodyViewRenderer)->render(
            $samples['view_namespace'].'::'.$samples['bodies'][$body],
            [],
            $samples['recipient_name'],
        );

        return (new MailRenderer)->render(
            $kind,
            self::SUBJECT,
            $markdown,
            $samples['user_identifier'],
            $scheme,
        );
    }
}
